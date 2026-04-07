<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    grade: Object,
    existingHorarios: Array,
    existingHorariosOutrasGrades: Array,
    materias_presenciais: Array,
    materias_ucd: Array,
    professores: Array,
    salas: Array,
    turmas: Array,
    anoAtual: Number,
});

// --- Estado do Componente ---
const form = useForm({
    turma_id: props.grade?.turma_id || '',
    curso:    props.grade?.curso    || [],
    bimestre: props.grade?.bimestre || '',
    ano:      props.grade?.ano      || '',
    horarios: [],
});

const cursoOptions = ['Engenharia de Software', 'Ciências da Computação'];

// Preview do nome gerado
const selectedTurmaLabel = computed(() => {
    if (!form.turma_id) return null;
    const t = props.turmas.find(t => t.id == form.turma_id);
    return t ? t.nome : null;
});

const showSabado = ref(false);
const showUcd = ref(false);
const conflictWarnings = ref({});
const filteredProfessores = ref({});
const loadingProfessores = ref({});
const editingSlots = ref(new Set());
const materiaNames = ref({});

// Busca inline de matéria por slot
const buscaMateria = ref({});
const getBuscaMateria = (slotId) => buscaMateria.value[slotId] || '';
const setBuscaMateria = (slotId, val) => { buscaMateria.value[slotId] = val; };
const getMateriasFiltradas = (slotId, lista) => {
    const q = (buscaMateria.value[slotId] || '').toLowerCase().trim();
    if (!q) return [];
    return lista.filter(m => m.nome.toLowerCase().includes(q));
};
const selecionarMateria = (slot, materia, onChangeFn) => {
    slot.aula.materia_id = materia.id;
    buscaMateria.value[slot.id] = '';
    if (onChangeFn) onChangeFn(materia.id, slot.id);
};
const getMateriaLabel = (materiaId, lista) =>
    lista.find(m => m.id == materiaId)?.nome || '';
const professorNames = ref({});
const salaInfo = ref({});
const slotWarnings = ref({});

const diasDaSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA'];
const horariosBlocos = ['19:00-20:30', '20:45-22:15'];
const horarioSabado = '08:00-12:00';

// --- Estrutura de Dados Visual ---
const gradeVisual = ref(diasDaSemana.reduce((acc, dia) => {
    acc[dia] = horariosBlocos.map(bloco => ({
        dia_semana: dia,
        horario_bloco: bloco,
        slots: [{
            id: Date.now() + Math.random(),
            type: null,
            confirmed: false,
            aula: { professor_id: '', materia_id: '', sala: '', classroom_code: '' },
            flex_aulas: [],
        }],
    }));
    return acc;
}, {}));

const gradeSabado = ref({ tipo: '', materia_id: '' });
const gradeUcd = ref([]);

const materiasSabadoFiltradas = computed(() => {
    if (gradeSabado.value.tipo === 'estagio') return props.materias_presenciais.filter(m => m.nome.toLowerCase().includes('estágio'));
    if (gradeSabado.value.tipo === 'tcc') return props.materias_presenciais.filter(m => m.nome.toLowerCase().includes('tcc'));
    return [];
});

// --- Carrega dados existentes ---
const loadExistingGrade = () => {
    if (!props.existingHorarios || props.existingHorarios.length === 0) return;

    const horariosGrouped = {};
    props.existingHorarios.forEach(horario => {
        const key = `${horario.dia_semana}-${horario.horario_bloco}`;
        if (!horariosGrouped[key]) horariosGrouped[key] = [];
        horariosGrouped[key].push(horario);
    });

    Object.values(gradeVisual.value).forEach(diaDeAulas => {
        diaDeAulas.forEach((celula) => {
            const key = `${celula.dia_semana}-${celula.horario_bloco}`;
            const horariosCelula = horariosGrouped[key] || [];

            if (horariosCelula.length > 0) {
                celula.slots = horariosCelula.map(horario => {
                    const materia   = props.materias_presenciais?.find(m => m.id === horario.materia_id);
                    const professor = props.professores?.find(p => p.id === horario.professor_id);
                    const sala      = props.salas?.find(s => s.nome === horario.sala);

                    const slot = {
                        id: horario.id,
                        type: horario.tipo_slot || 'Ambos (Core)',
                        confirmed: true,
                        aula: {
                            professor_id:    horario.professor_id,
                            materia_id:      horario.materia_id,
                            sala:            horario.sala,
                            classroom_code:  horario.classroom_code || '',
                        },
                        flex_aulas: [],
                        original_id: horario.id,
                    };

                    materiaNames.value[slot.id]   = materia?.nome || '';
                    professorNames.value[slot.id]  = professor?.nome || '';
                    salaInfo.value[slot.id]        = sala || { nome: horario.sala };

                    return slot;
                });
            }
        });
    });

    // Carrega atividades ao Sábado
    const sabadoHorarios = props.existingHorarios.filter(h => h.dia_semana === 'SABADO');
    if (sabadoHorarios.length > 0) {
        showSabado.value = true;
        const h = sabadoHorarios[0];
        const materia = props.materias_presenciais?.find(m => m.id === h.materia_id);
        if (materia?.nome?.toLowerCase().includes('estágio')) gradeSabado.value.tipo = 'estagio';
        else if (materia?.nome?.toLowerCase().includes('tcc')) gradeSabado.value.tipo = 'tcc';
        gradeSabado.value.materia_id = h.materia_id;
    }
};

// --- Validação de Disponibilidade do Professor ---
const diaParaDisponibilidade = {
    'SEGUNDA': 'segunda', 'TERÇA': 'terça', 'QUARTA': 'quarta',
    'QUINTA': 'quinta', 'SEXTA': 'sexta', 'SABADO': 'sábado',
};

const professorDisponivelNoSlot = (professorId, dia, horario) => {
    const prof = props.professores.find(p => p.id == professorId);
    if (!prof?.disponibilidade) return true;
    const diaKey = diaParaDisponibilidade[dia?.toUpperCase()] ?? dia?.toLowerCase();
    return (prof.disponibilidade[diaKey] ?? []).includes(horario);
};

const professorLecionaMateria = (professorId, materiaId) => {
    const prof = props.professores.find(p => p.id == professorId);
    return prof?.materias?.some(m => m.id == materiaId) ?? false;
};

const getProfessoresDisponiveisNoSlot = (materiaId, dia, horario) => {
    if (!materiaId) return [];
    return props.professores.filter(p =>
        professorLecionaMateria(p.id, materiaId) &&
        professorDisponivelNoSlot(p.id, dia, horario)
    );
};

const getProfessoresIndisponiveisNoSlot = (materiaId, dia, horario) => {
    if (!materiaId) return [];
    return props.professores.filter(p =>
        !professorLecionaMateria(p.id, materiaId) ||
        !professorDisponivelNoSlot(p.id, dia, horario)
    );
};

// --- Lógica de Filtros ---
const materiasCore = computed(() => props.materias_presenciais);
const materiasFlex = computed(() => props.materias_presenciais);

const canSubmit = computed(() => form.turma_id && form.curso.length > 0 && form.bimestre && form.ano);

const getProfessoresParaMateria = async (materiaId, slotId) => {
    // Todos os professores vêm de props — não precisa de API
};

const checkForConflict = (aula, celula, slotId) => {
    const key = `${celula.dia_semana}-${celula.horario_bloco}-${slotId}`;
    if (!aula.professor_id) {
        delete conflictWarnings.value[key];
        return;
    }
    // Usa os horários de OUTRAS grades do mesmo bimestre/ano (já filtrados no backend)
    const fonte = props.existingHorariosOutrasGrades || [];
    const conflicts = fonte.filter(h =>
        h.dia_semana === celula.dia_semana &&
        h.horario_bloco === celula.horario_bloco &&
        h.professor_id == aula.professor_id
    );
    if (conflicts.length > 0) {
        const sharedClass = conflicts.find(c => c.materia_id == aula.materia_id);
        if (sharedClass) {
            conflictWarnings.value[key] = `Possível turma compartilhada com "${sharedClass.grade?.nome}" — confirme com mesmo professor e sala.`;
        } else {
            conflictWarnings.value[key] = `Conflito: Prof. já alocado na grade "${conflicts[0].grade?.nome}".`;
        }
    } else {
        delete conflictWarnings.value[key];
    }
};

// --- Manipulação da Grade ---
const addSlot = (celula) => {
    if (celula.slots.length < 2) {
        const newId = Date.now() + Math.random();
        celula.slots.push({
            id: newId,
            type: null,
            confirmed: false,
            aula: { professor_id: '', materia_id: '', sala: '', classroom_code: '' },
            flex_aulas: [],
        });
        editingSlots.value.add(newId);
    }
};

const removeSlot = (celula, slotIndex) => celula.slots.splice(slotIndex, 1);
const setSlotType = (slot, type) => slot.type = type;

const confirmSlot = (slot, dia, horario) => {
    if (slot.aula.materia_id && slot.aula.professor_id && slot.aula.sala) {
        const warnings = [];

        // 1. Disponibilidade do professor (aviso, não bloqueia)
        if (dia && horario && !professorDisponivelNoSlot(slot.aula.professor_id, dia, horario)) {
            const profNome = props.professores.find(p => p.id == slot.aula.professor_id)?.nome || 'Professor';
            warnings.push(`${profNome} não possui disponibilidade em ${dia} ${horario}.`);
        }

        // Horários de OUTRAS grades no mesmo dia/horário (já filtrados por bimestre/ano no backend)
        const horariosNoSlot = (props.existingHorariosOutrasGrades || []).filter(h =>
            h.dia_semana === dia &&
            h.horario_bloco === horario
        );

        // 2. Conflito de sala (exceto aula compartilhada: mesma UC + mesmo professor)
        const salaConflitos = horariosNoSlot.filter(h =>
            h.sala === slot.aula.sala &&
            !(h.materia_id == slot.aula.materia_id && h.professor_id == slot.aula.professor_id)
        );
        if (salaConflitos.length > 0) {
            const nomes = [...new Set(salaConflitos.map(c => c.grade?.nome))].join(', ');
            warnings.push(`Sala "${slot.aula.sala}" já em uso na(s) grade(s): ${nomes}.`);
        }

        // 3. Conflito de professor (exceto aula compartilhada: mesma UC + mesma sala)
        const profConflitos = horariosNoSlot.filter(h =>
            h.professor_id == slot.aula.professor_id &&
            !(h.materia_id == slot.aula.materia_id && h.sala === slot.aula.sala)
        );
        if (profConflitos.length > 0) {
            const profNome = props.professores.find(p => p.id == slot.aula.professor_id)?.nome || 'Professor';
            const nomes = [...new Set(profConflitos.map(c => c.grade?.nome))].join(', ');
            warnings.push(`${profNome} já alocado na(s) grade(s): ${nomes}.`);
        }

        // 4. UC compartilhada: mesma matéria neste slot em outra grade
        const mesmaUcOutraGrade = horariosNoSlot.filter(h =>
            h.materia_id == slot.aula.materia_id
        );
        if (mesmaUcOutraGrade.length > 0) {
            mesmaUcOutraGrade.forEach(h => {
                const outroProf = props.professores.find(p => p.id == h.professor_id)?.nome || 'Professor';
                const outraSala = h.sala || '(sem sala)';
                const gradeName = h.grade?.nome || 'outra grade';
                const profDiferente = h.professor_id != slot.aula.professor_id;
                const salaDiferente = h.sala !== slot.aula.sala;
                if (profDiferente && salaDiferente) {
                    warnings.push(`Turma compartilhada com "${gradeName}": professor deveria ser "${outroProf}" e sala "${outraSala}".`);
                } else if (profDiferente) {
                    warnings.push(`Turma compartilhada com "${gradeName}": professor deveria ser "${outroProf}".`);
                } else if (salaDiferente) {
                    warnings.push(`Turma compartilhada com "${gradeName}": sala deveria ser "${outraSala}".`);
                }
            });
        }

        slotWarnings.value[slot.id] = warnings;

        slot.confirmed = true;
        editingSlots.value.delete(slot.id);
        materiaNames.value[slot.id]  = props.materias_presenciais.find(m => m.id == slot.aula.materia_id)?.nome || '';
        professorNames.value[slot.id] = props.professores.find(p => p.id == slot.aula.professor_id)?.nome || '';
        salaInfo.value[slot.id]      = props.salas.find(s => s.nome === slot.aula.sala) || { nome: slot.aula.sala };
    }
};

const editSlot = (slot) => {
    slot.confirmed = false;
    editingSlots.value.add(slot.id);
    delete slotWarnings.value[slot.id];
};

const addFlexAula = (slot) => slot.flex_aulas.push({ id: Date.now(), professor_id: '', materia_id: '', sala: '', classroom_code: '', confirmed: false, materiaName: '', professorName: '' });
const removeFlexAula = (slot, flexIndex) => {
    slot.flex_aulas.splice(flexIndex, 1);
    if (!slot.flex_aulas.some(fa => fa.confirmed)) slot.confirmed = false;
};
const confirmFlexAula = (slot, fIndex) => {
    const fa = slot.flex_aulas[fIndex];
    if (fa.materia_id && fa.professor_id && fa.sala) {
        fa.confirmed = true;
        fa.materiaName = props.materias_presenciais.find(m => m.id == fa.materia_id)?.nome || '';
        fa.professorName = props.professores.find(p => p.id == fa.professor_id)?.nome || '';
        slot.confirmed = true;
    }
};
const editFlexAula = (slot, fa) => {
    fa.confirmed = false;
    if (!slot.flex_aulas.some(f => f.confirmed)) slot.confirmed = false;
};

const addUcd = () => gradeUcd.value.push({ dia_semana: 'ATIVIDADE_DIGITAL', horario_bloco: 'N/A', materia_id: '', professor_id: '', sala: '', classroom_code: '' });
const removeUcd = (index) => gradeUcd.value.splice(index, 1);

// --- Submissão ---
const submit = () => {
    form.clearErrors();
    let hasErrors = false;

    if (!form.turma_id) {
        form.setError('turma_id', 'Selecione a turma da grade para enviar.');
        hasErrors = true;
    }
    if (form.curso.length === 0) {
        form.setError('curso', 'Selecione pelo menos um curso da grade para enviar.');
        hasErrors = true;
    }
    if (!form.bimestre) {
        form.setError('bimestre', 'Selecione o bimestre da grade para enviar.');
        hasErrors = true;
    }
    if (!form.ano) {
        form.setError('ano', 'Preencha o ano da grade para enviar.');
        hasErrors = true;
    }

    if (hasErrors) return;

    const horariosPreenchidos = [];
    Object.values(gradeVisual.value).forEach(diaDeAulas => {
        diaDeAulas.forEach(celula => {
            const { slots, ...celulaBase } = celula;
            celula.slots.forEach(slot => {
                if (!slot.confirmed) return;
                const aulaBase = {
                    dia_semana:    celulaBase.dia_semana,
                    horario_bloco: celulaBase.horario_bloco,
                };
                if (slot.type === 'Flex') {
                    slot.flex_aulas.forEach(flexAula => {
                        if (flexAula.materia_id && flexAula.professor_id) horariosPreenchidos.push({ ...aulaBase, ...flexAula, tipo_slot: 'Flex' });
                    });
                } else if (slot.type && slot.aula.materia_id && slot.aula.professor_id) {
                    horariosPreenchidos.push({ ...aulaBase, ...slot.aula, tipo_slot: slot.type });
                }
            });
        });
    });

    if (showSabado.value && gradeSabado.value.materia_id) {
        const primeiroProfessorId = props.professores[0]?.id || null;
        horariosPreenchidos.push({ dia_semana: 'SABADO', horario_bloco: horarioSabado, materia_id: gradeSabado.value.materia_id, professor_id: primeiroProfessorId, sala: null, classroom_code: null, tipo_slot: 'Atividade Sábado' });
    }

    if (showUcd.value) {
        gradeUcd.value.forEach(aula => {
            if (aula.materia_id && aula.professor_id) horariosPreenchidos.push(aula);
        });
    }

    if (horariosPreenchidos.length === 0) {
        form.setError('horarios', 'Confirme pelo menos 1 aula antes de salvar.');
        return;
    }

    form.horarios = horariosPreenchidos;

    form.post(route('grades.update-post', props.grade.id), {
        onError: (errors) => {
            console.error('Erros ao atualizar grade:', errors);
            if (errors.error) alert('Erro: ' + errors.error);
        },
    });
};

onMounted(() => {
    loadExistingGrade();
});
</script>

<template>
    <Head :title="`Editar Grade: ${grade.nome}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Editar Grade: {{ grade.nome }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Cabeçalho da Grade: Turma + Curso -->
                            <div class="p-6 border rounded-lg mb-8 bg-gray-50 dark:bg-neutral-900 border-gray-200 dark:border-neutral-700">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

                                    <!-- Seleção de Turma -->
                                    <div>
                                        <InputLabel for="turma" class="text-gray-700 dark:text-gray-300 font-semibold mb-1">
                                            Turma <span class="text-red-500">*</span>
                                        </InputLabel>
                                        <select
                                            id="turma"
                                            v-model="form.turma_id"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                        >
                                            <option value="" disabled>Selecione uma Turma</option>
                                            <option v-for="turma in props.turmas" :key="turma.id" :value="turma.id">
                                                {{ turma.nome }}
                                            </option>
                                        </select>
                                        <InputError class="mt-1" :message="form.errors.turma_id" />

                                        <p v-if="selectedTurmaLabel" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Nome da grade: <span class="font-semibold text-orange-600 dark:text-orange-400">{{ selectedTurmaLabel }}</span>
                                        </p>
                                    </div>

                                    <!-- Seleção de Curso(s) -->
                                    <div>
                                        <InputLabel class="text-gray-700 dark:text-gray-300 font-semibold mb-1">
                                            Curso(s) <span class="text-red-500">*</span>
                                        </InputLabel>
                                        <div class="mt-2 space-y-2">
                                            <label
                                                v-for="option in cursoOptions"
                                                :key="option"
                                                class="flex items-center gap-3 cursor-pointer"
                                            >
                                                <input
                                                    type="checkbox"
                                                    v-model="form.curso"
                                                    :value="option"
                                                    class="rounded border-gray-300 dark:border-gray-500 text-orange-600 shadow-sm focus:ring-orange-500 dark:bg-neutral-700 w-4 h-4"
                                                />
                                                <span class="text-sm text-gray-800 dark:text-gray-200">{{ option }}</span>
                                            </label>
                                        </div>
                                        <InputError class="mt-1" :message="form.errors.curso" />
                                        <div v-if="form.curso.length > 0" class="mt-3 flex flex-wrap gap-2">
                                            <span
                                                v-for="c in form.curso"
                                                :key="c"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-full"
                                                :class="c.includes('Engenharia') ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-300' : 'bg-green-100 dark:bg-green-500/20 text-green-800 dark:text-green-300'"
                                            >
                                                {{ c.includes('Engenharia') ? 'Eng. Software' : 'C. Computação' }}
                                            </span>
                                            <span v-if="form.curso.length === 2" class="px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 dark:bg-orange-500/20 text-orange-800 dark:text-orange-300">
                                                Turma Mista
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Segunda linha: Bimestre + Ano -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start mt-6 pt-6 border-t border-gray-200 dark:border-neutral-700">

                                    <!-- Seleção de Bimestre -->
                                    <div>
                                        <InputLabel for="bimestre" class="text-gray-700 dark:text-gray-300 font-semibold mb-1">
                                            Bimestre <span class="text-red-500">*</span>
                                        </InputLabel>
                                        <div class="mt-2 flex gap-3 flex-wrap">
                                            <label
                                                v-for="b in [1, 2, 3, 4]"
                                                :key="b"
                                                class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border transition-colors"
                                                :class="form.bimestre == b
                                                    ? 'border-orange-500 bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300'
                                                    : 'border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:border-orange-400'"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="form.bimestre"
                                                    :value="b"
                                                    class="text-orange-600 focus:ring-orange-500"
                                                />
                                                <span class="text-sm font-medium">{{ b }}º Bim.</span>
                                            </label>
                                        </div>
                                        <InputError class="mt-1" :message="form.errors.bimestre" />
                                    </div>

                                    <!-- Ano -->
                                    <div>
                                        <InputLabel for="ano" class="text-gray-700 dark:text-gray-300 font-semibold mb-1">
                                            Ano <span class="text-red-500">*</span>
                                        </InputLabel>
                                        <div class="mt-1 flex items-center gap-3 flex-wrap">
                                            <input
                                                id="ano"
                                                type="number"
                                                v-model="form.ano"
                                                :min="2020"
                                                :max="2100"
                                                class="w-32 rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                            />
                                            <div class="flex gap-2">
                                                <button
                                                    v-for="anoOpt in [props.anoAtual - 1, props.anoAtual, props.anoAtual + 1]"
                                                    :key="anoOpt"
                                                    type="button"
                                                    @click="form.ano = anoOpt"
                                                    class="px-3 py-1.5 text-xs rounded-md border transition-colors"
                                                    :class="form.ano == anoOpt
                                                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 font-semibold'
                                                        : 'border-gray-300 dark:border-neutral-600 text-gray-600 dark:text-gray-400 hover:border-orange-400'"
                                                >
                                                    {{ anoOpt }}
                                                </button>
                                            </div>
                                        </div>
                                        <InputError class="mt-1" :message="form.errors.ano" />
                                        <p v-if="form.bimestre && form.ano" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Período: <span class="font-semibold text-orange-600 dark:text-orange-400">{{ form.bimestre }}º Bimestre de {{ form.ano }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Grade de Horários -->
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed border-collapse text-xs">
                                    <thead class="bg-gray-100 dark:bg-neutral-800">
                                        <tr>
                                            <th class="border border-gray-300 dark:border-neutral-700 p-2 font-semibold text-gray-600 dark:text-gray-300 w-[10%]">Horário</th>
                                            <th v-for="dia in diasDaSemana" :key="dia" class="border border-gray-300 dark:border-neutral-700 p-2 font-semibold text-gray-600 dark:text-gray-300 uppercase w-[18%]">{{ dia }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900">
                                        <tr v-for="(bloco, hIndex) in horariosBlocos" :key="hIndex">
                                            <td class="border border-gray-300 dark:border-neutral-700 p-2 font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-neutral-800 text-center align-middle">{{ bloco.replace('-', ' - ') }}</td>
                                            <td v-for="dia in diasDaSemana" :key="dia + hIndex" class="border border-gray-300 dark:border-neutral-700 p-1 align-top overflow-hidden max-w-0">
                                                <div class="space-y-2 h-full flex flex-col min-w-0">
                                                    <div v-for="(slot, sIndex) in gradeVisual[dia][hIndex].slots" :key="slot.id" class="bg-gray-50 dark:bg-neutral-800 p-1.5 rounded-md border border-gray-200 dark:border-neutral-700 relative flex-1 flex flex-col min-w-0">
                                                        <button @click="removeSlot(gradeVisual[dia][hIndex], sIndex)" type="button" class="absolute -top-1.5 -right-1.5 bg-red-500 dark:bg-red-600 text-white rounded-full h-4 w-4 text-xs flex items-center justify-center font-bold">&times;</button>

                                                        <!-- MODO VISUALIZAÇÃO -->
                                                        <div v-if="slot.confirmed" class="space-y-2 h-full flex flex-col">
                                                            <div class="bg-white dark:bg-neutral-900 rounded-lg border border-gray-200 dark:border-neutral-700 p-3 flex-1 flex flex-col justify-between shadow-sm hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-black/20 transition-shadow">
                                                                <div class="space-y-2">
                                                                    <div class="flex justify-between items-start gap-2">
                                                                        <div class="flex-1">
                                                                            <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Matéria</p>
                                                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ materiaNames[slot.id] }}</p>
                                                                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full":class="{'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300': slot.type === 'Engenharia de Software','bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300': slot.type === 'Ciências da Computação','bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300': slot.type === 'Ambos (Core)','bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300': slot.type === 'Flex',}">
                                                                             {{ slot.type === 'Engenharia de Software' ? 'Eng. SW' : slot.type === 'Ciências da Computação' ? 'C. Comp' : slot.type === 'Ambos (Core)' ? 'Ambos' : 'Flex' }}
                                                                                     </span>
                                                                        </div>
                                                                        <button @click="editSlot(slot)" type="button" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors" title="Editar">
                                                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Professor</p>
                                                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ professorNames[slot.id] }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Sala</p>
                                                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ salaInfo[slot.id]?.nome }}</p>
                                                                    </div>
                                                                    <div v-if="slot.aula.classroom_code" class="pt-2 border-t border-gray-200 dark:border-neutral-700">
                                                                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Classroom</p>
                                                                        <p class="text-sm font-mono text-blue-600 dark:text-blue-400">{{ slot.aula.classroom_code }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div v-if="slotWarnings[slot.id]?.length" class="space-y-1">
                                                                <div v-for="(warn, wIdx) in slotWarnings[slot.id]" :key="wIdx" class="text-[10px] text-amber-700 dark:text-amber-400 p-1.5 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded flex items-start gap-1">
                                                                    <span class="shrink-0">⚠️</span>
                                                                    <span>{{ warn }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- MODO EDIÇÃO -->
                                                        <div v-else class="space-y-1.5">
                                                            <div v-if="!slot.type" class="flex flex-col items-center justify-center gap-1 py-1 h-full min-h-[6rem]">
                                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tipo de aula:</span>
                                                                <div class="flex gap-1 flex-wrap justify-center">
                                                                    <button @click="setSlotType(slot, 'Engenharia de Software')" type="button" class="px-1.5 py-0.5 text-[10px] bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-400 rounded">Eng. SW</button>
                                                                    <button @click="setSlotType(slot, 'Ciências da Computação')" type="button" class="px-1.5 py-0.5 text-[10px] bg-green-100 dark:bg-green-500/20 text-green-800 dark:text-green-400 rounded">C. Comp</button>
                                                                    <button @click="setSlotType(slot, 'Ambos (Core)')" type="button" class="px-1.5 py-0.5 text-[10px] bg-orange-100 dark:bg-orange-500/20 text-orange-800 dark:text-orange-400 rounded">Ambos</button>
                                                                    <button @click="setSlotType(slot, 'Flex')" type="button" class="px-1.5 py-0.5 text-[10px] bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-400 rounded">Flex</button>
                                                                </div>
                                                            </div>

                                                            <div v-if="slot.type && slot.type !== 'Flex'" class="space-y-1.5 min-w-0">
                                                                <p class="text-[11px] font-bold text-center" :class="{'text-blue-800 dark:text-blue-400': slot.type.includes('Engenharia'), 'text-green-800 dark:text-green-400': slot.type.includes('Ciências'), 'text-orange-800 dark:text-orange-400': slot.type.includes('Ambos')}">{{ slot.type }}</p>
                                                                <!-- Busca inline de matéria -->
                                                                <div class="space-y-1">
                                                                    <input
                                                                        type="text"
                                                                        :value="getBuscaMateria(slot.id)"
                                                                        @input="setBuscaMateria(slot.id, $event.target.value)"
                                                                        placeholder="🔎 Buscar matéria..."
                                                                        class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1 focus:border-orange-400 dark:focus:border-orange-500 focus:ring-0"
                                                                    />
                                                                    <div v-if="slot.aula.materia_id" class="flex items-center justify-between px-2 py-1 rounded bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 text-xs font-semibold">
                                                                        <span class="truncate">{{ getMateriaLabel(slot.aula.materia_id, materiasCore) }}</span>
                                                                        <button type="button" @click="slot.aula.materia_id = ''; filteredProfessores[slot.id] = []" class="ml-1 shrink-0 font-bold hover:text-red-500">×</button>
                                                                    </div>
                                                                    <ul v-if="getBuscaMateria(slot.id).trim()" class="max-h-28 overflow-y-auto rounded-md border border-gray-200 dark:border-neutral-600 bg-white dark:bg-neutral-800 divide-y divide-gray-100 dark:divide-neutral-700">
                                                                        <li v-for="m in getMateriasFiltradas(slot.id, materiasCore)" :key="m.id"
                                                                            @mousedown.prevent="selecionarMateria(slot, m, getProfessoresParaMateria); setBuscaMateria(slot.id, '')"
                                                                            class="px-2 py-1.5 text-xs cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-200">
                                                                            {{ m.nome }}
                                                                        </li>
                                                                        <li v-if="getMateriasFiltradas(slot.id, materiasCore).length === 0" class="px-2 py-2 text-xs text-gray-400 text-center italic">Nenhuma encontrada</li>
                                                                    </ul>
                                                                </div>
                                                                <select v-model="slot.aula.professor_id" @change="checkForConflict(slot.aula, gradeVisual[dia][hIndex], slot.id)" :disabled="!slot.aula.materia_id" class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1 disabled:bg-gray-100 dark:disabled:bg-neutral-800">
                                                                    <option value="" disabled>Professor</option>
                                                                    <optgroup v-if="getProfessoresDisponiveisNoSlot(slot.aula.materia_id, dia, gradeVisual[dia][hIndex].horario_bloco).length" label="✅ Disponíveis">
                                                                        <option v-for="prof in getProfessoresDisponiveisNoSlot(slot.aula.materia_id, dia, gradeVisual[dia][hIndex].horario_bloco)" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                                                                    </optgroup>
                                                                    <optgroup v-if="getProfessoresIndisponiveisNoSlot(slot.aula.materia_id, dia, gradeVisual[dia][hIndex].horario_bloco).length" label="⚠️ Indisponíveis">
                                                                        <option v-for="prof in getProfessoresIndisponiveisNoSlot(slot.aula.materia_id, dia, gradeVisual[dia][hIndex].horario_bloco)" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                                                                    </optgroup>
                                                                </select>
                                                                <div v-if="slot.aula.professor_id && !professorDisponivelNoSlot(slot.aula.professor_id, dia, gradeVisual[dia][hIndex].horario_bloco)" class="text-[10px] text-red-600 dark:text-red-400 p-1 bg-red-100 dark:bg-red-500/10 rounded">
                                                                    ⚠️ Professor sem disponibilidade neste horário.
                                                                </div>
                                                                <select v-model="slot.aula.sala" class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1">
                                                                    <option value="" disabled>Sala</option>
                                                                    <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">{{ sala.nome }} (Cap: {{ sala.capacidade }})</option>
                                                                </select>
                                                                <TextInput type="text" v-model="slot.aula.classroom_code" placeholder="Classroom (opcional)" class="text-xs w-full dark:bg-neutral-700 dark:border-gray-300/40 dark:text-gray-200" />
                                                                <div v-if="conflictWarnings[`${gradeVisual[dia][hIndex].dia_semana}-${gradeVisual[dia][hIndex].horario_bloco}-${slot.id}`]" class="text-[10px] text-orange-600 dark:text-orange-400 p-1 bg-orange-100 dark:bg-orange-500/10 rounded">
                                                                    {{ conflictWarnings[`${gradeVisual[dia][hIndex].dia_semana}-${gradeVisual[dia][hIndex].horario_bloco}-${slot.id}`] }}
                                                                </div>
                                                                <button @click="confirmSlot(slot, dia, gradeVisual[dia][hIndex].horario_bloco)" type="button" :disabled="!slot.aula.materia_id || !slot.aula.professor_id || !slot.aula.sala" class="w-full px-3 py-1.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white text-xs font-semibold rounded-md transition-colors">
                                                                    ✓ Confirmar
                                                                </button>
                                                            </div>

                                                            <div v-if="slot.type === 'Flex'" class="space-y-1.5">
                                                                <p class="text-[11px] font-bold text-yellow-800 dark:text-yellow-400 text-center">Flex</p>
                                                                <div v-for="(flexAula, fIndex) in slot.flex_aulas" :key="flexAula.id" class="border-t border-gray-300 dark:border-neutral-700 pt-1.5">
                                                                    <!-- Card confirmado -->
                                                                    <div v-if="flexAula.confirmed" class="bg-white dark:bg-neutral-900 rounded border border-yellow-300 dark:border-yellow-500/40 p-2 flex justify-between items-start gap-1">
                                                                        <div class="flex-1 min-w-0 space-y-0.5">
                                                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ flexAula.materiaName }}</p>
                                                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ flexAula.professorName }}</p>
                                                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ flexAula.sala }}</p>
                                                                        </div>
                                                                        <div class="flex gap-1 shrink-0">
                                                                            <button @click="editFlexAula(slot, flexAula)" type="button" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-neutral-700">
                                                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                            </button>
                                                                            <button @click="removeFlexAula(slot, fIndex)" type="button" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-500/20 text-red-500">&times;</button>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Formulário edição -->
                                                                    <div v-else class="flex gap-1.5 items-start">
                                                                        <div class="flex-1 space-y-1.5 min-w-0">
                                                                            <div class="space-y-1">
                                                                                <input
                                                                                    type="text"
                                                                                    :value="getBuscaMateria(`flex-${slot.id}-${fIndex}`)"
                                                                                    @input="setBuscaMateria(`flex-${slot.id}-${fIndex}`, $event.target.value)"
                                                                                    placeholder="🔎 Buscar matéria..."
                                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1 focus:border-orange-400 dark:focus:border-orange-500 focus:ring-0"
                                                                                />
                                                                                <div v-if="flexAula.materia_id" class="flex items-center justify-between px-2 py-1 rounded bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 text-xs font-semibold">
                                                                                    <span class="truncate">{{ getMateriaLabel(flexAula.materia_id, materiasFlex) }}</span>
                                                                                    <button type="button" @click="flexAula.materia_id = ''" class="ml-1 shrink-0 font-bold hover:text-red-500">×</button>
                                                                                </div>
                                                                                <ul v-if="getBuscaMateria(`flex-${slot.id}-${fIndex}`).trim()" class="max-h-28 overflow-y-auto rounded-md border border-gray-200 dark:border-neutral-600 bg-white dark:bg-neutral-800 divide-y divide-gray-100 dark:divide-neutral-700">
                                                                                    <li v-for="m in getMateriasFiltradas(`flex-${slot.id}-${fIndex}`, materiasFlex)" :key="m.id"
                                                                                        @mousedown.prevent="flexAula.materia_id = m.id; getProfessoresParaMateria(m.id, `flex-${slot.id}-${fIndex}`); setBuscaMateria(`flex-${slot.id}-${fIndex}`, '')"
                                                                                        class="px-2 py-1.5 text-xs cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-200">
                                                                                        {{ m.nome }}
                                                                                    </li>
                                                                                    <li v-if="getMateriasFiltradas(`flex-${slot.id}-${fIndex}`, materiasFlex).length === 0" class="px-2 py-2 text-xs text-gray-400 text-center italic">Nenhuma encontrada</li>
                                                                                </ul>
                                                                            </div>
                                                                            <select v-model="flexAula.professor_id" :disabled="!flexAula.materia_id" class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1 disabled:bg-gray-100 dark:disabled:bg-neutral-800">
                                                                                <option value="" disabled>Professor</option>
                                                                                <option v-for="prof in props.professores" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                                                                            </select>
                                                                            <select v-model="flexAula.sala" class="w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-700 dark:text-gray-200 text-xs px-2 py-1">
                                                                                <option value="" disabled>Sala</option>
                                                                                <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">{{ sala.nome }} (Cap: {{ sala.capacidade }})</option>
                                                                            </select>
                                                                            <TextInput type="text" v-model="flexAula.classroom_code" placeholder="Classroom" class="text-xs w-full dark:bg-neutral-700 dark:border-gray-300/40 dark:text-gray-200" />
                                                                            <button @click="confirmFlexAula(slot, fIndex)" type="button" :disabled="!flexAula.materia_id || !flexAula.professor_id || !flexAula.sala" class="w-full px-3 py-1.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white text-xs font-semibold rounded-md transition-colors">✓ Confirmar</button>
                                                                        </div>
                                                                        <button @click="removeFlexAula(slot, fIndex)" type="button" class="text-red-500 dark:text-red-400 font-bold p-0.5 rounded-full hover:bg-red-100 dark:hover:bg-red-500/20 mt-1">&times;</button>
                                                                    </div>
                                                                </div>
                                                                <button @click="addFlexAula(slot)" type="button" class="w-full text-center text-xs py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-400 rounded hover:bg-yellow-200 dark:hover:bg-yellow-500/30 mt-1">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button v-if="gradeVisual[dia][hIndex].slots.length < 2" @click="addSlot(gradeVisual[dia][hIndex])" type="button" class="w-full text-center text-xs py-1 bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-200 dark:hover:bg-neutral-700 mt-auto">+ Adicionar Aula</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex gap-4 flex-wrap">
                                <button type="button" @click="showSabado = !showSabado" class="px-4 py-2 text-sm font-medium rounded-md transition-colors" :class="showSabado ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-400' : 'bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-700'">
                                    {{ showSabado ? 'Ocultar' : 'Adicionar' }} Sábado
                                </button>
                                <button type="button" @click="showUcd = !showUcd" class="px-4 py-2 text-sm font-medium rounded-md transition-colors" :class="showUcd ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-800 dark:text-purple-400' : 'bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-700'">
                                    {{ showUcd ? 'Ocultar' : 'Adicionar' }} Atividades Digitais
                                </button>
                            </div>

                            <div v-if="showSabado" class="mt-4 p-4 border border-blue-200 dark:border-blue-500/30 rounded-lg bg-blue-50 dark:bg-blue-500/10">
                                <InputLabel class="text-gray-700 dark:text-gray-300 font-semibold">Atividades ao Sábado ({{ horarioSabado.replace('-', ' - ') }})</InputLabel>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">Selecione o tipo de atividade e a fase correspondente.</p>
                                <div class="flex gap-4 flex-wrap mb-3">
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border transition-colors"
                                        :class="gradeSabado.tipo === 'estagio' ? 'border-blue-500 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:border-blue-400'">
                                        <input type="radio" v-model="gradeSabado.tipo" value="estagio" @change="gradeSabado.materia_id = ''" class="text-blue-600 focus:ring-blue-500" />
                                        <span class="text-sm font-medium">Estágio</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border transition-colors"
                                        :class="gradeSabado.tipo === 'tcc' ? 'border-blue-500 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-300 hover:border-blue-400'">
                                        <input type="radio" v-model="gradeSabado.tipo" value="tcc" @change="gradeSabado.materia_id = ''" class="text-blue-600 focus:ring-blue-500" />
                                        <span class="text-sm font-medium">TCC</span>
                                    </label>
                                </div>
                                <div v-if="gradeSabado.tipo" class="mt-2">
                                    <InputLabel class="text-gray-700 dark:text-gray-300 text-xs mb-1">Fase</InputLabel>
                                    <select v-model="gradeSabado.materia_id" class="w-full md:w-1/2 rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled>Selecione a fase</option>
                                        <option v-for="m in materiasSabadoFiltradas" :key="m.id" :value="m.id">{{ m.nome }}</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="showUcd" class="mt-4 p-4 border border-purple-200 dark:border-purple-500/30 rounded-lg bg-purple-50 dark:bg-purple-500/10">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-semibold text-gray-800 dark:text-gray-300">Atividades Digitais (UCDs)</h3>
                                    <button @click="addUcd" type="button" class="px-3 py-1 bg-purple-600 dark:bg-purple-700 text-white text-sm font-medium rounded-md hover:bg-purple-700 dark:hover:bg-purple-600">+</button>
                                </div>
                                <div class="space-y-4">
                                    <p v-if="gradeUcd.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center">Nenhuma atividade digital adicionada.</p>
                                    <div v-for="(ucd, index) in gradeUcd" :key="index" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-3 border border-gray-200 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-900">
                                        <div class="md:col-span-4">
                                            <InputLabel :for="'ucd_materia_'+index" class="text-xs mb-1 text-gray-700 dark:text-gray-300">Matéria</InputLabel>
                                            <div class="space-y-1">
                                                <input
                                                    type="text"
                                                    :value="getBuscaMateria(`ucd-${index}`)"
                                                    @input="setBuscaMateria(`ucd-${index}`, $event.target.value)"
                                                    placeholder="🔎 Buscar UCD..."
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 text-xs px-2 py-1 focus:border-orange-400 dark:focus:border-orange-500 focus:ring-0"
                                                />
                                                <div v-if="ucd.materia_id" class="flex items-center justify-between px-2 py-1 rounded bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300 text-xs font-semibold">
                                                    <span class="truncate">{{ getMateriaLabel(ucd.materia_id, props.materias_ucd) }}</span>
                                                    <button type="button" @click="ucd.materia_id = ''; ucd.professor_id = ''" class="ml-1 shrink-0 font-bold hover:text-red-500">×</button>
                                                </div>
                                                <ul v-if="getBuscaMateria(`ucd-${index}`).trim()" class="max-h-28 overflow-y-auto rounded-md border border-gray-200 dark:border-neutral-600 bg-white dark:bg-neutral-800 divide-y divide-gray-100 dark:divide-neutral-700">
                                                    <li v-for="m in getMateriasFiltradas(`ucd-${index}`, props.materias_ucd)" :key="m.id"
                                                        @mousedown.prevent="ucd.materia_id = m.id; ucd.professor_id = ''; setBuscaMateria(`ucd-${index}`, '')"
                                                        class="px-2 py-1.5 text-xs cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 text-gray-700 dark:text-gray-200">
                                                        {{ m.nome }}
                                                    </li>
                                                    <li v-if="getMateriasFiltradas(`ucd-${index}`, props.materias_ucd).length === 0" class="px-2 py-2 text-xs text-gray-400 text-center italic">Nenhuma encontrada</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="md:col-span-3">
                                            <InputLabel :for="'ucd_prof_'+index" class="text-xs mb-1 text-gray-700 dark:text-gray-300">Professor</InputLabel>
                                            <select :id="'ucd_prof_'+index" v-model="ucd.professor_id" :disabled="!ucd.materia_id" class="block w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 shadow-sm sm:text-xs disabled:bg-gray-100 dark:disabled:bg-neutral-800">
                                                <option value="" disabled>Selecione o Professor</option>
                                                <option v-for="professor in props.professores.filter(p => p.materias.some(m => m.id === ucd.materia_id))" :key="professor.id" :value="professor.id">{{ professor.nome }}</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <InputLabel :for="'ucd_sala_'+index" class="text-xs mb-1 text-gray-700 dark:text-gray-300">Sala</InputLabel>
                                            <select :id="'ucd_sala_'+index" v-model="ucd.sala" class="block w-full rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 shadow-sm sm:text-xs">
                                                <option value="" disabled>Sala</option>
                                                <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">{{ sala.nome }} (Cap: {{ sala.capacidade }})</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <InputLabel :for="'ucd_code_'+index" class="text-xs mb-1 text-gray-700 dark:text-gray-300">Classroom</InputLabel>
                                            <TextInput :id="'ucd_code_'+index" type="text" v-model="ucd.classroom_code" class="w-full text-xs dark:bg-neutral-800 dark:border-gray-300/40 dark:text-gray-200" />
                                        </div>
                                        <div class="md:col-span-1 flex items-end justify-center">
                                            <button @click="removeUcd(index)" type="button" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-bold p-1 rounded-full hover:bg-red-100 dark:hover:bg-red-500/20">X</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <div v-if="form.errors.error" class="mr-auto text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-md px-3 py-2">
                                    ⚠️ {{ form.errors.error }}
                                </div>
                                <Link :href="route('grades.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <PrimaryButton :disabled="form.processing || !canSubmit" class="ms-4 bg-orange-600 hover:bg-orange-700 dark:bg-orange-700 dark:hover:bg-orange-600">
                                    Salvar Alterações
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>