<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const isAluno = computed(() => usePage().props.auth.user.role === 'aluno');

const props = defineProps({
    grades: Object,
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
const searchQuery = ref('');
const filterAno = ref('');
const filterBimestre = ref('');

const anosDisponiveis = computed(() => {
    const anos = [...new Set(props.grades.data.map(g => g.ano).filter(Boolean))];
    return anos.sort((a, b) => b - a);
});

const onAnoChange = () => {
    filterBimestre.value = '';
};

const bimestresDisponiveis = computed(() => {
    if (!filterAno.value) return [1, 2, 3, 4];
    const bimestres = [...new Set(
        props.grades.data
            .filter(g => g.ano == filterAno.value)
            .map(g => g.bimestre)
            .filter(Boolean)
    )];
    return bimestres.sort();
});

const filteredGrades = computed(() => {
    return props.grades.data.filter(grade => {
        const search = searchQuery.value.toLowerCase().trim();
        const matchSearch = !search ||
            grade.nome.toLowerCase().includes(search) ||
            (grade.description && grade.description.toLowerCase().includes(search));

        const matchAno = !filterAno.value || grade.ano == filterAno.value;
        const matchBimestre = !filterBimestre.value || grade.bimestre == filterBimestre.value;

        return matchSearch && matchAno && matchBimestre;
    });
});

const clearFilters = () => {
    searchQuery.value = '';
    filterAno.value = '';
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

                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Minhas Grades de Horários</h3>
                            <div class="flex gap-2">
                                <Link v-if="!isAluno" :href="route('grades.planner')" class="inline-flex items-center px-4 py-2 bg-zinc-700 dark:bg-zinc-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-zinc-800 dark:hover:bg-zinc-500 transition ease-in-out duration-150">
                                    🗂 Planner
                                </Link>
                                <Link v-if="!isAluno" :href="route('grades.create')" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition ease-in-out duration-150">
                                    + Criar Nova Grade
                                </Link>
                            </div>
                        </div>

                        <div class="mb-6 space-y-3">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="🔎 Pesquisar grades..."
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-neutral-800 border border-gray-300 dark:border-gray-300/40 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 transition"
                            />

                            <div class="flex flex-wrap gap-3 items-center">
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

                                <span class="text-gray-300 dark:text-gray-600 select-none">›</span>

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

                                <button v-if="hasActiveFilters" @click="clearFilters" class="text-xs text-orange-600 dark:text-orange-400 hover:underline ml-1">
                                    Limpar filtros
                                </button>

                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">
                                    {{ filteredGrades.length }} grade{{ filteredGrades.length !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="grade in filteredGrades"
                                :key="grade.id"
                                class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-6 hover:shadow-md transition-shadow duration-150"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                                            {{ grade.nome }}
                                        </h4>

                                        <div class="flex items-center gap-x-3 text-xs mt-3 flex-wrap gap-y-2">
                                            <span v-if="grade.bimestre" class="px-3 py-1 bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 rounded-full font-semibold">
                                                {{ bimestreLabel(grade.bimestre) }}
                                            </span>
                                            <span v-if="grade.ano" class="px-3 py-1 bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-gray-300 rounded-full font-semibold">
                                                {{ grade.ano }}
                                            </span>
                                            <span v-if="grade.turma" class="px-3 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-full">
                                                {{ grade.turma.nome }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 ml-4">
                                        <Link :href="route('grades.show', grade.id)" class="p-2 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition" title="Visualizar Grade">
                                            👁️
                                        </Link>

                                        <button v-if="!isAluno" @click.prevent="togglePin(grade.id)" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-neutral-800 transition" :title="grade.pinned_at ? 'Desafixar' : 'Fixar'">
                                            <span :class="[grade.pinned_at ? 'text-orange-500' : 'text-gray-400']" class="text-lg">📌</span>
                                        </button>

                                        <Link v-if="!isAluno" :href="route('grades.edit', grade.id)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-blue-700 transition">
                                            Editar
                                        </Link>

                                        <button v-if="!isAluno" @click.prevent="confirmGradeDeletion(grade.id)" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-md font-semibold text-xs uppercase hover:bg-red-700 transition">
                                            Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="filteredGrades.length === 0" class="text-center py-12 text-gray-500">
                                Nenhuma grade encontrada.
                            </div>
                        </div>

                        <div v-if="grades.links.length > 3" class="flex justify-center mt-6">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, key) in grades.links" :key="key">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded-md bg-white dark:bg-neutral-800" v-html="link.label" />
                                    <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded-md transition-colors duration-150" :class="{ 'bg-orange-600 text-white border-orange-600': link.active, 'bg-white hover:bg-gray-100 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-800 dark:text-gray-300': !link.active }" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showConfirmModal"
            title="Excluir Grade"
            message="Tem certeza de que quer excluir esta grade?"
            @close="closeModal"
            @confirm="deleteGrade"
        />
    </AuthenticatedLayout>
</template>