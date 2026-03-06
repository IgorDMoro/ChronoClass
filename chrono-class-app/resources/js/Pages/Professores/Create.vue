<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { computed, ref } from 'vue';

const props = defineProps({
    diasDaSemana: Array,
    finaisDeSemana: Array,
    horariosDeAula: Array,
    horariosDeAulaFinaisDeSemana: Array,
    materias: Array,
});

const form = useForm({
    matricula: "",
    nome: "",
    email: "",
    telefone: "",
    horarios_disponiveis_selecionados: [],
    materias_ids: [],
});

// --- Busca e paginação de matérias ---
const materiaBusca = ref('');
const materiasPorPagina = 8;
const materiasPagina = ref(1);

const materiasFiltradas = computed(() => {
    const q = materiaBusca.value.toLowerCase().trim();
    if (!q) return props.materias;
    return props.materias.filter(m => m.nome.toLowerCase().includes(q));
});

const totalPaginas = computed(() =>
    Math.max(1, Math.ceil(materiasFiltradas.value.length / materiasPorPagina))
);

const materiasPaginadas = computed(() => {
    const inicio = (materiasPagina.value - 1) * materiasPorPagina;
    return materiasFiltradas.value.slice(inicio, inicio + materiasPorPagina);
});

// Reseta para página 1 ao buscar
const onBuscaMateria = () => {
    materiasPagina.value = 1;
};
// ------------------------------------

const isSabadoSelected = computed(() => {
    return props.horariosDeAulaFinaisDeSemana.some(horario =>
        form.horarios_disponiveis_selecionados.includes(`sábado-${horario}`)
    );
});

const handleSabadoChange = (event) => {
    const isChecked = event.target.checked;
    props.horariosDeAulaFinaisDeSemana.forEach(horario => {
        const sabadoValue = `sábado-${horario}`;
        if (isChecked) {
            if (!form.horarios_disponiveis_selecionados.includes(sabadoValue)) {
                form.horarios_disponiveis_selecionados.push(sabadoValue);
            }
        } else {
            const index = form.horarios_disponiveis_selecionados.indexOf(sabadoValue);
            if (index > -1) form.horarios_disponiveis_selecionados.splice(index, 1);
        }
    });
};

const submit = () => {
    form.post(route("professores.store"), {
        onSuccess: () => form.reset(),
    });
};

const formatDia = (dia) => dia.charAt(0).toUpperCase() + dia.slice(1);
</script>

<template>
    <Head title="Cadastrar Professor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cadastrar Novo Professor
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submit">
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-300 mb-3 text-center">
                                Cadastro
                            </h5>

                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">Dados Pessoais</h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div>
                                        <label for="matricula" class="text-sm font-medium text-gray-700 dark:text-gray-300">Matrícula:</label>
                                        <input type="number" id="matricula" v-model="form.matricula" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.matricula" />
                                    </div>
                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>
                                    <div>
                                        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</label>
                                        <input type="email" id="email" v-model="form.email" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>
                                    <div>
                                        <label for="telefone" class="text-sm font-medium text-gray-700 dark:text-gray-300">Telefone:</label>
                                        <input type="text" id="telefone" v-model="form.telefone" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.telefone" />
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">Disponibilidade (Segunda a Sexta)</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">Dia / Horário</th>
                                                <th v-for="horario in horariosDeAula" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">{{ horario }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dia in diasDaSemana" :key="dia" class="border-b border-gray-200 dark:border-neutral-800">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">{{ formatDia(dia) }}</td>
                                                <td v-for="horario in horariosDeAula" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <input type="checkbox" :value="`${dia}-${horario}`" v-model="form.horarios_disponiveis_selecionados" class="rounded border-gray-300 dark:border-gray-300/40 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">Disponibilidade (Sábado)</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">Dia / Horário</th>
                                                <th v-for="horario in horariosDeAulaFinaisDeSemana" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">{{ horario }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dia in finaisDeSemana" :key="dia" class="border-b border-gray-200 dark:border-neutral-800 last:border-b-0">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">{{ formatDia(dia) }}</td>
                                                <td v-for="horario in horariosDeAulaFinaisDeSemana" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <input type="checkbox" :value="`${dia}-${horario}`" v-model="form.horarios_disponiveis_selecionados" class="rounded border-gray-300 dark:border-gray-300/40 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputError class="mt-2" :message="form.errors.horarios_disponiveis_selecionados" />
                            </div>

                            <!-- Seção de Matérias com busca e paginação -->
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300">Unidades Curriculares</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Selecione as matérias que este professor leciona
                                            <span v-if="form.materias_ids.length > 0" class="ml-1 font-semibold text-orange-500 dark:text-orange-400">
                                                ({{ form.materias_ids.length }} selecionada{{ form.materias_ids.length > 1 ? 's' : '' }})
                                            </span>
                                        </p>
                                    </div>
                                    <!-- Campo de busca -->
                                    <div class="relative w-full sm:w-72">
                                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                                            </svg>
                                        </span>
                                        <input
                                            type="text"
                                            v-model="materiaBusca"
                                            @input="onBuscaMateria"
                                            placeholder="Buscar matéria..."
                                            class="pl-9 pr-4 py-2 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 text-sm text-gray-900 dark:text-gray-200 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50"
                                        />
                                    </div>
                                </div>

                                <!-- Grid de matérias paginadas -->
                                <div v-if="materiasFiltradas.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <label
                                        v-for="materia in materiasPaginadas"
                                        :key="materia.id"
                                        :for="`materia-${materia.id}`"
                                        class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                                        :class="form.materias_ids.includes(materia.id)
                                            ? 'border-orange-400 dark:border-orange-500/60 bg-orange-50 dark:bg-orange-500/10'
                                            : 'border-gray-200 dark:border-neutral-700 hover:border-gray-300 dark:hover:border-neutral-600 hover:bg-gray-50 dark:hover:bg-neutral-800/60'"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`materia-${materia.id}`"
                                            :value="materia.id"
                                            v-model.number="form.materias_ids"
                                            class="rounded border-gray-300 dark:border-gray-500 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 w-4 h-4 shrink-0"
                                        />
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ materia.nome }}</span>
                                    </label>
                                </div>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
                                    Nenhuma matéria encontrada para "<span class="font-semibold">{{ materiaBusca }}</span>".
                                </p>

                                <!-- Paginação -->
                                <div v-if="totalPaginas > 1" class="mt-4 flex items-center justify-between">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Página {{ materiasPagina }} de {{ totalPaginas }}
                                        ({{ materiasFiltradas.length }} matéria{{ materiasFiltradas.length > 1 ? 's' : '' }})
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            @click="materiasPagina = 1"
                                            :disabled="materiasPagina === 1"
                                            class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            title="Primeira página"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="materiasPagina--"
                                            :disabled="materiasPagina === 1"
                                            class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            title="Página anterior"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </button>

                                        <template v-for="p in totalPaginas" :key="p">
                                            <button
                                                v-if="p === 1 || p === totalPaginas || (p >= materiasPagina - 1 && p <= materiasPagina + 1)"
                                                type="button"
                                                @click="materiasPagina = p"
                                                class="min-w-[2rem] h-8 px-2 rounded-md text-xs font-semibold transition-colors"
                                                :class="materiasPagina === p
                                                    ? 'bg-orange-500 dark:bg-orange-600 text-white'
                                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-700'"
                                            >{{ p }}</button>
                                            <span
                                                v-else-if="p === materiasPagina - 2 || p === materiasPagina + 2"
                                                class="text-gray-400 dark:text-gray-500 text-xs px-1"
                                            >…</span>
                                        </template>

                                        <button
                                            type="button"
                                            @click="materiasPagina++"
                                            :disabled="materiasPagina === totalPaginas"
                                            class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            title="Próxima página"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="materiasPagina = totalPaginas"
                                            :disabled="materiasPagina === totalPaginas"
                                            class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            title="Última página"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M6 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <InputError class="mt-2" :message="form.errors.materias_ids" />
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <Link :href="route('professores.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    Cadastrar Professor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>