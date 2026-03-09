<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    grades: Array,
});

// --- Exclusão ---
const showConfirmModal = ref(false);
const gradeToDeleteId = ref(null);

const confirmGradeDeletion = (id) => {
    gradeToDeleteId.value = id;
    showConfirmModal.value = true;
};

const deleteGrade = () => {
    if (gradeToDeleteId.value) {
        router.post(route('grades.destroy', gradeToDeleteId.value), {}, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showConfirmModal.value = false;
    gradeToDeleteId.value = null;
};

// --- Fixar ---
const togglePin = (gradeId) => {
    router.post(route('grades.pin', gradeId), {}, { preserveScroll: true });
};

// --- Filtros ---
const searchQuery   = ref('');
const filterAno     = ref('');
const filterBimestre = ref('');

// Anos disponíveis extraídos das grades existentes (sem repetição, ordenados desc)
const anosDisponiveis = computed(() => {
    const anos = [...new Set(props.grades.map(g => g.ano).filter(Boolean))];
    return anos.sort((a, b) => b - a);
});

// Quando o ano muda, limpa o filtro de bimestre
const onAnoChange = () => {
    filterBimestre.value = '';
};

// Bimestres disponíveis para o ano selecionado
const bimestresDisponiveis = computed(() => {
    if (!filterAno.value) return [1, 2, 3, 4];
    const bimestres = [...new Set(
        props.grades
            .filter(g => g.ano == filterAno.value)
            .map(g => g.bimestre)
            .filter(Boolean)
    )];
    return bimestres.sort();
});

const filteredGrades = computed(() => {
    return props.grades.filter(grade => {
        const search = searchQuery.value.toLowerCase().trim();
        const matchSearch = !search ||
            grade.nome.toLowerCase().includes(search) ||
            (grade.description && grade.description.toLowerCase().includes(search));

        const matchAno      = !filterAno.value     || grade.ano      == filterAno.value;
        const matchBimestre = !filterBimestre.value || grade.bimestre == filterBimestre.value;

        return matchSearch && matchAno && matchBimestre;
    });
});

const clearFilters = () => {
    searchQuery.value    = '';
    filterAno.value      = '';
    filterBimestre.value = '';
};

const hasActiveFilters = computed(() =>
    searchQuery.value || filterAno.value || filterBimestre.value
);

const bimestreLabel = (n) => n ? `${n}º Bimestre` : '—';
</script>

<template>
    <Head title="Minhas Grades" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8">

                        <!-- Cabeçalho -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Minhas Grades de Horários</h3>
                            <div class="flex gap-2">
    <Link :href="route('grades.planner')" class="inline-flex items-center px-4 py-2 bg-zinc-700 dark:bg-zinc-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-zinc-800 dark:hover:bg-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 transition ease-in-out duration-150">
        🗂 Planner
    </Link>
    <Link :href="route('grades.create')" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150">
        + Criar Nova Grade
    </Link>
</div>
                        </div>

                        <!-- Filtros -->
                        <div class="mb-6 space-y-3">
                            <!-- Busca por texto -->
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="🔎 Pesquisar grades..."
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-neutral-800 border border-gray-300 dark:border-gray-300/40 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:focus:border-orange-500 transition"
                            />

                            <!-- Filtros de Ano e Bimestre em cascata -->
                            <div class="flex flex-wrap gap-3 items-center">
                                <!-- Filtro: Ano -->
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap">Ano</label>
                                    <select
                                        v-model="filterAno"
                                        @change="onAnoChange"
                                        class="rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 text-sm shadow-sm focus:ring-orange-500 focus:border-orange-500 py-1.5 pl-3 pr-8"
                                    >
                                        <option value="">Todos os anos</option>
                                        <option v-for="ano in anosDisponiveis" :key="ano" :value="ano">{{ ano }}</option>
                                    </select>
                                </div>

                                <!-- Separador visual -->
                                <span class="text-gray-300 dark:text-gray-600 select-none">›</span>

                                <!-- Filtro: Bimestre (habilitado só após selecionar ano) -->
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide whitespace-nowrap">Bimestre</label>
                                    <select
                                        v-model="filterBimestre"
                                        :disabled="!filterAno"
                                        class="rounded-md border-gray-300 dark:border-gray-300/40 dark:bg-neutral-800 dark:text-gray-200 text-sm shadow-sm focus:ring-orange-500 focus:border-orange-500 py-1.5 pl-3 pr-8 disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <option value="">Todos os bimestres</option>
                                        <option v-for="b in bimestresDisponiveis" :key="b" :value="b">{{ bimestreLabel(b) }}</option>
                                    </select>
                                </div>

                                <!-- Limpar filtros -->
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="text-xs text-orange-600 dark:text-orange-400 hover:underline ml-1"
                                >
                                    Limpar filtros
                                </button>

                                <!-- Contador de resultados -->
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">
                                    {{ filteredGrades.length }} grade{{ filteredGrades.length !== 1 ? 's' : '' }} encontrada{{ filteredGrades.length !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Flash de sucesso -->
                        <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 dark:bg-green-500/10 dark:border-green-500/30 dark:text-green-400 px-4 py-3 rounded-md relative mb-6" role="alert">
                            {{ $page.props.flash.success }}
                        </div>

                        <!-- Lista de Grades -->
                        <div class="space-y-4">
                            <div
                                v-for="grade in filteredGrades"
                                :key="grade.id"
                                class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-6 hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-black/20 transition-shadow duration-150"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition">
                                            {{ grade.nome }}
                                        </h4>

                                        <div class="flex items-center gap-x-3 text-xs mt-3 flex-wrap gap-y-2">
                                            <!-- Badge: Bimestre -->
                                            <span v-if="grade.bimestre" class="px-3 py-1 bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 rounded-full font-semibold">
                                                {{ bimestreLabel(grade.bimestre) }}
                                            </span>

                                            <!-- Badge: Ano -->
                                            <span v-if="grade.ano" class="px-3 py-1 bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-300 rounded-full font-semibold">
                                                {{ grade.ano }}
                                            </span>

                                            <!-- Badge: Curso(s) -->
                                            <span v-if="grade.curso && grade.curso.length" class="px-3 py-1 bg-purple-100 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 rounded-full">
                                                {{ grade.curso.join(' & ') }}
                                            </span>

                                            <!-- Badge: Turma -->
                                            <span v-if="grade.turma" class="px-3 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-full">
                                                {{ grade.turma.nome }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Ações -->
                                    <div class="flex items-center gap-3 ml-4">
                                        <!-- Fixar -->
                                        <div class="relative group">
                                            <button @click.prevent="togglePin(grade.id)" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors" :title="grade.pinned_at ? 'Desafixar' : 'Fixar'">
                                                <span :class="[grade.pinned_at ? 'text-orange-500 dark:text-orange-400' : 'text-gray-400 dark:text-gray-500']" class="text-lg">📌</span>
                                            </button>
                                            <div class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-700 dark:bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none whitespace-nowrap">
                                                {{ grade.pinned_at ? 'Desafixar' : 'Fixar' }}
                                            </div>
                                        </div>

                                        <!-- Editar -->
                                        <div class="relative group">
                                            <Link :href="route('grades.edit', grade.id)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition duration-150">
                                                Editar
                                            </Link>
                                        </div>

                                        <!-- Excluir -->
                                        <div class="relative group">
                                            <button @click.prevent="confirmGradeDeletion(grade.id)" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition duration-150">
                                                Excluir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado vazio -->
                            <div v-if="filteredGrades.length === 0" class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhuma grade encontrada.</p>
                                <button v-if="hasActiveFilters" @click="clearFilters" class="mt-2 text-sm text-orange-600 dark:text-orange-400 hover:underline">
                                    Limpar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showConfirmModal"
            title="Excluir Grade"
            message="Tem certeza de que quer excluir esta grade? Todas as aulas associadas serão apagadas permanentemente."
            @close="closeModal"
            @confirm="deleteGrade"
        />
    </AuthenticatedLayout>
</template>