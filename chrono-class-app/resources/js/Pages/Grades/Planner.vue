<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    grades: Array,
    ano: Number,
    bimestre: Number,
    anosDisponiveis: Array,
    bimestresDisponiveis: Array,
});

// --- Filtros de período ---
const selectedAno      = ref(props.ano      || '');
const selectedBimestre = ref(props.bimestre || '');

const aplicarFiltro = () => {
    if (!selectedAno.value || !selectedBimestre.value) return;
    router.get(route('grades.planner'), {
        ano: selectedAno.value,
        bimestre: selectedBimestre.value,
    }, { preserveScroll: true });
};

const onAnoChange = () => {
    selectedBimestre.value = '';
};

// --- Dados locais mutáveis para o drag and drop ---
const gradesLocais = ref(
    (props.grades || []).map(g => ({
        ...g,
        horarios: [...(g.horarios || [])],
    }))
);

const diasDaSemana   = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA'];
const horariosBlocos = ['19:00-20:30', '20:45-22:15'];
const diasExtras     = ['SABADO', 'ATIVIDADE_DIGITAL'];

// --- Alterações pendentes ---
const alteracoesPendentes = ref([]);
const hasPendentes = computed(() => alteracoesPendentes.value.length > 0);

// --- Modo de visualização ---
const modoGrade = ref(false); // false = compacto, true = grade completa

// --- Drag and drop ---
const dragInfo   = ref(null);  // { horarioId, fromGradeId }
const dropTarget = ref(null);  // { gradeId, dia?, bloco? } — slot específico no modo grade

const onDragStart = (horario, gradeId) => {
    dragInfo.value = { horarioId: horario.id, fromGradeId: gradeId };
};

const onDragOver = (gradeId, event, dia = null, bloco = null) => {
    event.preventDefault();
    if (!dragInfo.value) return;

    const mesmaGrade = dragInfo.value.fromGradeId === gradeId;

    // Mesma grade só faz sentido no modo grade (slot específico diferente do original)
    if (mesmaGrade && modoGrade.value) {
        const horario = gradesLocais.value
            .find(g => g.id === gradeId)?.horarios
            .find(h => h.id === dragInfo.value.horarioId);
        const slotDiferente = horario && (horario.dia_semana !== dia || horario.horario_bloco !== bloco);
        if (slotDiferente) dropTarget.value = { gradeId, dia, bloco };
    } else if (!mesmaGrade) {
        dropTarget.value = { gradeId, dia, bloco };
    }
};

const onDragLeave = () => {
    dropTarget.value = null;
};

const isDropTarget = (gradeId, dia = null, bloco = null) => {
    if (!dropTarget.value) return false;
    if (dropTarget.value.gradeId !== gradeId) return false;
    if (dia && bloco) return dropTarget.value.dia === dia && dropTarget.value.bloco === bloco;
    return !dropTarget.value.dia; // modo compacto: highlight coluna inteira
};

const onDrop = (toGradeId, targetDia = null, targetBloco = null) => {
    dropTarget.value = null;
    if (!dragInfo.value) return;

    const { horarioId, fromGradeId } = dragInfo.value;
    dragInfo.value = null;

    const fromGrade = gradesLocais.value.find(g => g.id === fromGradeId);
    const toGrade   = gradesLocais.value.find(g => g.id === toGradeId);
    if (!fromGrade || !toGrade) return;

    const horarioIdx = fromGrade.horarios.findIndex(h => h.id === horarioId);
    if (horarioIdx === -1) return;
    const horario = fromGrade.horarios[horarioIdx];

    const diaDest   = targetDia   || horario.dia_semana;
    const blocoDest = targetBloco || horario.horario_bloco;

    // Mesma grade + mesmo slot = noop
    if (fromGradeId === toGradeId && diaDest === horario.dia_semana && blocoDest === horario.horario_bloco) return;

    // Horários que ocupam o slot de destino
    const slotDestino = toGrade.horarios.filter(h =>
        h.dia_semana    === diaDest &&
        h.horario_bloco === blocoDest
    );

    if (slotDestino.length > 0) {
        // ── SWAP ──
        const conflitoEntrada = toGrade.horarios.find(h =>
            h.dia_semana    === diaDest &&
            h.horario_bloco === blocoDest &&
            h.professor_id  === horario.professor_id &&
            !slotDestino.find(s => s.id === h.id)
        );
        if (conflitoEntrada) {
            alert(`Conflito! ${horario.professor?.nome} já está nesse slot em "${toGrade.nome}".`);
            return;
        }

        for (const h of slotDestino) {
            const conflitoOrigem = fromGrade.horarios.find(hf =>
                hf.dia_semana    === horario.dia_semana &&
                hf.horario_bloco === horario.horario_bloco &&
                hf.professor_id  === h.professor_id &&
                hf.id !== horarioId
            );
            if (conflitoOrigem) {
                alert(`Conflito! ${h.professor?.nome} já está nesse slot em "${fromGrade.nome}".`);
                return;
            }
        }

        // Swap: os do destino vão para o dia/bloco original do arrastado
        fromGrade.horarios.splice(horarioIdx, 1);
        slotDestino.forEach(h => {
            const idx = toGrade.horarios.findIndex(hh => hh.id === h.id);
            if (idx !== -1) toGrade.horarios.splice(idx, 1);
            fromGrade.horarios.push({ ...h, dia_semana: horario.dia_semana, horario_bloco: horario.horario_bloco });
        });
        toGrade.horarios.push({ ...horario, dia_semana: diaDest, horario_bloco: blocoDest });

        registrarPendente({ ...horario, dia_semana: diaDest, horario_bloco: blocoDest }, fromGradeId, fromGrade.nome, toGradeId, toGrade.nome);
        slotDestino.forEach(h =>
            registrarPendente({ ...h, dia_semana: horario.dia_semana, horario_bloco: horario.horario_bloco }, toGradeId, toGrade.nome, fromGradeId, fromGrade.nome)
        );

    } else {
        // ── MOVE SIMPLES para slot vazio ──
        const conflitoProf = toGrade.horarios.find(h =>
            h.dia_semana    === diaDest &&
            h.horario_bloco === blocoDest &&
            h.professor_id  === horario.professor_id
        );
        if (conflitoProf) {
            alert(`Conflito! ${horario.professor?.nome} já está alocado nesse horário em "${toGrade.nome}".`);
            return;
        }

        fromGrade.horarios.splice(horarioIdx, 1);
        toGrade.horarios.push({ ...horario, dia_semana: diaDest, horario_bloco: blocoDest });
        registrarPendente({ ...horario, dia_semana: diaDest, horario_bloco: blocoDest }, fromGradeId, fromGrade.nome, toGradeId, toGrade.nome);
    }
};

// Registra ou atualiza uma movimentação pendente
const registrarPendente = (horario, deGradeId, deGradeNome, paraGradeId, paraGradeNome) => {
    const entrada = {
        horarioId:    horario.id,
        nomeHorario:  `${horario.dia_semana} ${horario.horario_bloco} — ${horario.materia?.nome || '?'}`,
        diaSemana:    horario.dia_semana,
        horarioBloco: horario.horario_bloco,
        deGradeId,
        deGradeNome,
        paraGradeId,
        paraGradeNome,
    };
    const jaIdx = alteracoesPendentes.value.findIndex(a => a.horarioId === horario.id);
    if (jaIdx !== -1) {
        alteracoesPendentes.value[jaIdx] = entrada;
    } else {
        alteracoesPendentes.value.push(entrada);
    }
};

// Desfaz uma alteração revertendo o movimento local
const desfazerAlteracao = (index) => {
    const alt = alteracoesPendentes.value[index];

    const fromGrade = gradesLocais.value.find(g => g.id === alt.paraGradeId);
    const toGrade   = gradesLocais.value.find(g => g.id === alt.deGradeId);

    if (fromGrade && toGrade) {
        const hi = fromGrade.horarios.findIndex(h => h.id === alt.horarioId);
        if (hi !== -1) {
            const h = fromGrade.horarios.splice(hi, 1)[0];
            toGrade.horarios.push(h);
        }
    }

    alteracoesPendentes.value.splice(index, 1);
};

// --- Salvar via POST ---
const salvando = ref(false);

const salvarTudo = () => {
    if (!hasPendentes.value) return;
    salvando.value = true;

    router.post(route('grades.planner.salvar'), {
        alteracoes: alteracoesPendentes.value.map(a => ({
            horario_id:    a.horarioId,
            para_grade_id: a.paraGradeId,
            dia_semana:    a.diaSemana    || null,
            horario_bloco: a.horarioBloco || null,
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            alteracoesPendentes.value = [];
            salvando.value = false;
        },
        onError: () => {
            salvando.value = false;
            alert('Erro ao salvar. Tente novamente.');
        },
    });
};

// --- Helpers ---
const getHorariosSlot   = (grade, dia, bloco) =>
    grade.horarios.filter(h => h.dia_semana === dia && h.horario_bloco === bloco);

const getHorariosExtras = (grade) =>
    grade.horarios.filter(h => diasExtras.includes(h.dia_semana));

const diaLabel = (dia) => ({
    SEGUNDA: 'Seg', 'TERÇA': 'Ter', QUARTA: 'Qua', QUINTA: 'Qui',
    SEXTA: 'Sex', SABADO: 'Sáb', ATIVIDADE_DIGITAL: 'UCD',
}[dia] || dia);

const blocoLabel = (b) => b.replace('-', '–');

const cardCor = (horario) => {
    const t = horario.tipo_aula || '';
    if (t.includes('Engenharia')) return 'border-l-blue-500 bg-blue-50 dark:bg-blue-500/10';
    if (t.includes('Ciências'))   return 'border-l-green-500 bg-green-50 dark:bg-green-500/10';
    if (t.includes('Flex'))       return 'border-l-yellow-500 bg-yellow-50 dark:bg-yellow-500/10';
    return 'border-l-orange-400 bg-orange-50 dark:bg-orange-500/10';
};
</script>

<template>
    <Head title="Planner de Grades" />
    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Cabeçalho + seletor de período -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow ring-1 ring-gray-200 dark:ring-orange-500/20 p-5 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4 justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Planner de Grades</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Arraste horários entre turmas do mesmo período. As alterações só são salvas ao clicar em <strong>Salvar tudo</strong>.
                            </p>
                        </div>

                        <div class="flex items-end gap-3 flex-wrap">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ano</label>
                                <select
                                    v-model="selectedAno"
                                    @change="onAnoChange"
                                    class="rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 text-sm shadow-sm focus:ring-orange-500 focus:border-orange-500 py-1.5 pl-3 pr-8"
                                >
                                    <option value="" disabled>Ano</option>
                                    <option v-for="a in props.anosDisponiveis" :key="a" :value="a">{{ a }}</option>
                                </select>
                            </div>

                            <span class="text-gray-300 dark:text-gray-600 pb-2 select-none">›</span>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bimestre</label>
                                <select
                                    v-model="selectedBimestre"
                                    :disabled="!selectedAno"
                                    class="rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 text-sm shadow-sm focus:ring-orange-500 focus:border-orange-500 py-1.5 pl-3 pr-8 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <option value="" disabled>Bimestre</option>
                                    <option v-for="b in props.bimestresDisponiveis" :key="b" :value="b">{{ b }}º Bimestre</option>
                                </select>
                            </div>

                            <button
                                @click="aplicarFiltro"
                                :disabled="!selectedAno || !selectedBimestre"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white text-sm font-semibold rounded-md transition-colors"
                            >
                                Carregar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sem período selecionado -->
                <div v-if="!props.grades" class="text-center py-20 text-gray-400 dark:text-gray-500">
                    <p class="text-4xl mb-3">📅</p>
                    <p class="text-lg">Selecione um ano e bimestre para começar.</p>
                </div>

                <!-- Período sem grades -->
                <div v-else-if="gradesLocais.length === 0" class="text-center py-20 text-gray-400 dark:text-gray-500">
                    <p class="text-4xl mb-3">🗂️</p>
                    <p class="text-lg">Nenhuma grade encontrada para este período.</p>
                </div>

                <template v-else>
                    <!-- Painel de alterações pendentes -->
                    <div
                        v-if="hasPendentes"
                        class="mb-5 bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/40 rounded-lg p-4"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
                                    {{ alteracoesPendentes.length }} alteração{{ alteracoesPendentes.length > 1 ? 'ões' : '' }} pendente{{ alteracoesPendentes.length > 1 ? 's' : '' }}
                                </p>
                                <ul class="space-y-1.5">
                                    <li
                                        v-for="(alt, i) in alteracoesPendentes"
                                        :key="i"
                                        class="flex items-center gap-2 text-xs text-amber-900 dark:text-amber-200"
                                    >
                                        <span class="flex-1 truncate">
                                            <span class="font-semibold">{{ alt.nomeHorario }}</span>
                                            <span class="text-amber-600 dark:text-amber-400 mx-1">de</span>
                                            <span class="font-semibold">{{ alt.deGradeNome }}</span>
                                            <span class="text-amber-600 dark:text-amber-400 mx-1">→</span>
                                            <span class="font-semibold">{{ alt.paraGradeNome }}</span>
                                        </span>
                                        <button
                                            @click="desfazerAlteracao(i)"
                                            title="Desfazer"
                                            class="shrink-0 text-amber-600 dark:text-amber-400 hover:text-red-600 dark:hover:text-red-400 font-bold transition-colors px-1"
                                        >↩</button>
                                    </li>
                                </ul>
                            </div>
                            <button
                                @click="salvarTudo"
                                :disabled="salvando"
                                class="shrink-0 px-5 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white text-sm font-bold rounded-md transition-colors"
                            >
                                {{ salvando ? 'Salvando...' : '✓ Salvar tudo' }}
                            </button>
                        </div>
                    </div>

                    <!-- Toggle de modo de visualização -->
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Visualização</span>
                        <button
                            type="button"
                            @click="modoGrade = !modoGrade"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                            :class="modoGrade ? 'bg-orange-500' : 'bg-gray-300 dark:bg-neutral-600'"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                                :class="modoGrade ? 'translate-x-6' : 'translate-x-1'"
                            />
                        </button>
                        <span class="text-sm font-medium" :class="modoGrade ? 'text-orange-500 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400'">
                            {{ modoGrade ? 'Grade completa' : 'Somente preenchidos' }}
                        </span>
                    </div>

                    <!-- ══════════════════════════════════════════ -->
                    <!-- MODO COMPACTO (padrão) -->
                    <!-- ══════════════════════════════════════════ -->
                    <div v-if="!modoGrade" class="flex gap-4 overflow-x-auto pb-6 items-start">
                        <div
                            v-for="grade in gradesLocais"
                            :key="grade.id"
                            class="shrink-0 w-60 rounded-lg border transition-all duration-150"
                            :class="isDropTarget(grade.id)
                                ? 'border-orange-400 dark:border-orange-500 shadow-lg shadow-orange-200 dark:shadow-orange-500/20 ring-2 ring-orange-300 dark:ring-orange-500/40'
                                : 'border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900'"
                            @dragover="onDragOver(grade.id, $event)"
                            @dragleave="onDragLeave"
                            @drop="onDrop(grade.id)"
                        >
                            <!-- Cabeçalho -->
                            <div class="p-3 border-b border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 rounded-t-lg">
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" :title="grade.nome">{{ grade.nome }}</p>
                                <div class="flex gap-1.5 mt-1.5 flex-wrap">
                                    <span v-if="grade.turma" class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 font-medium">{{ grade.turma.nome }}</span>
                                    <span v-if="grade.curso?.length" class="text-[10px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 font-medium">
                                        {{ grade.curso.length === 2 ? 'Mista' : (grade.curso[0]?.includes('Engenharia') ? 'Eng. SW' : 'C. Comp') }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ grade.horarios.length }} horário{{ grade.horarios.length !== 1 ? 's' : '' }}</p>
                            </div>
                            <!-- Cards -->
                            <div class="p-2 space-y-1.5 min-h-[120px]">
                                <template v-for="dia in diasDaSemana" :key="dia">
                                    <template v-for="bloco in horariosBlocos" :key="bloco">
                                        <div
                                            v-for="horario in getHorariosSlot(grade, dia, bloco)"
                                            :key="horario.id"
                                            draggable="true"
                                            @dragstart="onDragStart(horario, grade.id)"
                                            class="border-l-4 rounded-md px-2 py-1.5 cursor-grab active:cursor-grabbing select-none transition-shadow hover:shadow-md dark:hover:shadow-black/30"
                                            :class="cardCor(horario)"
                                        >
                                            <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ diaLabel(dia) }} · {{ blocoLabel(bloco) }}</p>
                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 leading-snug mt-0.5">{{ horario.materia?.nome || '—' }}</p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ horario.professor?.nome || '—' }}</p>
                                            <p v-if="horario.sala" class="text-[10px] text-gray-400 dark:text-gray-500">🚪 {{ horario.sala }}</p>
                                        </div>
                                    </template>
                                </template>
                                <template v-if="getHorariosExtras(grade).length > 0">
                                    <div class="border-t border-dashed border-gray-200 dark:border-neutral-700 pt-2 mt-2">
                                        <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">Extras</p>
                                        <div
                                            v-for="horario in getHorariosExtras(grade)"
                                            :key="horario.id"
                                            draggable="true"
                                            @dragstart="onDragStart(horario, grade.id)"
                                            class="border-l-4 border-l-gray-400 bg-gray-50 dark:bg-neutral-800 rounded-md px-2 py-1.5 cursor-grab active:cursor-grabbing select-none mb-1 hover:shadow-md transition-shadow"
                                        >
                                            <p class="text-[10px] font-bold text-gray-500 uppercase">{{ diaLabel(horario.dia_semana) }}</p>
                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ horario.materia?.nome || '—' }}</p>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ horario.professor?.nome || '—' }}</p>
                                        </div>
                                    </div>
                                </template>
                                <div v-if="grade.horarios.length === 0" class="text-center py-8 text-gray-300 dark:text-gray-600 text-xs select-none">Solte aqui</div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════════════ -->
                    <!-- MODO GRADE COMPLETA -->
                    <!-- ══════════════════════════════════════════ -->
                    <div v-else class="overflow-x-auto pb-6">
                        <table class="border-separate border-spacing-0 min-w-max">
                            <thead>
                                <tr>
                                    <!-- Coluna de rótulos -->
                                    <th class="w-28 sticky left-0 z-10 bg-white dark:bg-zinc-800"></th>
                                    <th
                                        v-for="grade in gradesLocais"
                                        :key="grade.id"
                                        class="w-56 px-3 py-2 bg-gray-50 dark:bg-neutral-800 border-b border-l border-gray-200 dark:border-neutral-700 text-left"
                                    >
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate">{{ grade.nome }}</p>
                                        <div class="flex gap-1 mt-1 flex-wrap">
                                            <span v-if="grade.turma" class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 font-medium">{{ grade.turma.nome }}</span>
                                            <span v-if="grade.curso?.length" class="text-[10px] px-1.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 font-medium">
                                                {{ grade.curso.length === 2 ? 'Mista' : (grade.curso[0]?.includes('Engenharia') ? 'Eng. SW' : 'C. Comp') }}
                                            </span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="dia in diasDaSemana" :key="dia">
                                    <tr v-for="(bloco, bIdx) in horariosBlocos" :key="bloco">
                                        <!-- Rótulo dia + bloco -->
                                        <td
                                            class="sticky left-0 z-10 bg-white dark:bg-zinc-800 border-b border-r border-gray-200 dark:border-neutral-700 px-2 py-1.5 text-right"
                                            :class="bIdx === 0 ? 'pt-3' : ''"
                                        >
                                            <p v-if="bIdx === 0" class="text-[11px] font-bold text-orange-500 dark:text-orange-400 uppercase">{{ diaLabel(dia) }}</p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ blocoLabel(bloco) }}</p>
                                        </td>

                                        <!-- Célula por grade -->
                                        <td
                                            v-for="grade in gradesLocais"
                                            :key="grade.id"
                                            class="border-b border-l border-gray-200 dark:border-neutral-700 p-1.5 align-top transition-colors"
                                            :class="[
                                                bIdx === 0 ? 'pt-2' : '',
                                                isDropTarget(grade.id, dia, bloco)
                                                    ? 'bg-orange-50 dark:bg-orange-500/10 ring-2 ring-inset ring-orange-400 dark:ring-orange-500'
                                                    : 'bg-white dark:bg-neutral-900'
                                            ]"
                                            @dragover="onDragOver(grade.id, $event, dia, bloco)"
                                            @dragleave="onDragLeave"
                                            @drop="onDrop(grade.id, dia, bloco)"
                                        >
                                            <!-- Cards no slot -->
                                            <div
                                                v-for="horario in getHorariosSlot(grade, dia, bloco)"
                                                :key="horario.id"
                                                draggable="true"
                                                @dragstart="onDragStart(horario, grade.id)"
                                                class="border-l-4 rounded px-2 py-1.5 cursor-grab active:cursor-grabbing select-none hover:shadow-md dark:hover:shadow-black/30 transition-shadow"
                                                :class="cardCor(horario)"
                                            >
                                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 leading-snug">{{ horario.materia?.nome || '—' }}</p>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ horario.professor?.nome || '—' }}</p>
                                                <p v-if="horario.sala" class="text-[10px] text-gray-400 dark:text-gray-500">🚪 {{ horario.sala }}</p>
                                            </div>
                                            <!-- Slot vazio -->
                                            <div
                                                v-if="getHorariosSlot(grade, dia, bloco).length === 0"
                                                class="h-14 rounded border-2 border-dashed flex items-center justify-center transition-colors"
                                                :class="isDropTarget(grade.id, dia, bloco)
                                                    ? 'border-orange-400 dark:border-orange-500 text-orange-400'
                                                    : 'border-gray-200 dark:border-neutral-700 text-gray-300 dark:text-neutral-600'"
                                            >
                                                <span class="text-[10px] select-none">soltar aqui</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>