<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue'; // Importado
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    grades: Array,
});

// Lógica de Exclusão
const showConfirmModal = ref(false);
const gradeToDeleteId = ref(null);

const confirmGradeDeletion = (id) => {
    gradeToDeleteId.value = id;
    showConfirmModal.value = true;
};

const deleteGrade = () => {
    if (gradeToDeleteId.value) {
        // CORRIGIDO: Usa router.post
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

// Lógica da Busca
const searchQuery = ref('');
const filteredGrades = computed(() => {
    if (!searchQuery.value) return props.grades;
    const search = searchQuery.value.toLowerCase().trim();
    return props.grades.filter(grade =>
        grade.nome.toLowerCase().includes(search) ||
        (grade.description && grade.description.toLowerCase().includes(search))
    );
});

// Lógica para Fixar
const togglePin = (gradeId) => {
    // CORRIGIDO: Usa router.post
    router.post(route('grades.pin', gradeId), {}, {
        preserveScroll: true,
    });
};
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
                            <Link :href="route('grades.create')" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150">
                                + Criar Nova Grade
                            </Link>
                        </div>

                        <div class="mb-6">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="🔎 Pesquisar grades..."
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-neutral-800 border border-gray-300 dark:border-gray-300/40 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:focus:border-orange-500 transition"
                            />
                        </div>

                        <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 dark:bg-green-500/10 dark:border-green-500/30 dark:text-green-400 px-4 py-3 rounded-md relative mb-6" role="alert">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="space-y-4">
                            <div v-for="grade in filteredGrades" :key="grade.id" class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-6 hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-black/20 transition-shadow duration-150">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <Link :href="route('grades.show', grade.id)" class="block">
                                            <h4 class="text-lg font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition">{{ grade.nome }}</h4>
                                        </Link>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ grade.description || 'Sem descrição' }}</p>
                                        <div class="flex items-center gap-x-4 text-xs mt-3 flex-wrap">
                                            <span v-if="grade.semestre" class="px-3 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-full font-medium">{{ grade.semestre }}</span>
                                            <span v-if="grade.curso && grade.curso.length" class="px-3 py-1 bg-purple-100 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 rounded-full">{{ grade.curso.join(' & ') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 ml-4">
                                        <div class="relative group">
                                            <button @click.prevent="togglePin(grade.id)" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors" :title="grade.pinned_at ? 'Desafixar' : 'Fixar'">
                                                <span :class="[grade.pinned_at ? 'text-orange-500 dark:text-orange-400' : 'text-gray-400 dark:text-gray-500']" class="text-lg">📌</span>
                                            </button>
                                            <div class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-700 dark:bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none whitespace-nowrap">
                                                {{ grade.pinned_at ? 'Desafixar' : 'Fixar' }}
                                            </div>
                                        </div>
                                        <div class="relative group">
                                            <Link :href="route('grades.edit', grade.id)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition duration-150">
                                                Editar
                                            </Link>
                                            <div class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-700 dark:bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none whitespace-nowrap">Editar</div>
                                        </div>
                                        <div class="relative group">
                                            <button @click.prevent="confirmGradeDeletion(grade.id)" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition duration-150">
                                                Excluir
                                            </button>
                                            <div class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-700 dark:bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none whitespace-nowrap">Excluir</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="filteredGrades.length === 0" class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400 text-lg">Nenhuma grade encontrada.</p>
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
