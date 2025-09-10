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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Minhas Grades de Horários</h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <Link :href="route('grades.create')" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">
                        + Criar Nova Grade
                    </Link>
                    <div class="w-full sm:w-auto">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="🔎 Pesquisar..."
                            class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>

                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="grade in filteredGrades" :key="grade.id">
                            <div class="px-4 py-4 sm:px-6 flex justify-between items-center hover:bg-gray-50 transition">
                                <div>
                                    <Link :href="route('grades.show', grade.id)" class="block">
                                        <h3 class="font-semibold text-lg text-indigo-600">{{ grade.nome }}</h3>
                                    </Link>
                                    <p class="text-sm text-gray-500 mt-1">{{ grade.description || 'Sem descrição' }}</p>
                                    <div class="flex items-center gap-x-4 text-xs text-gray-500 mt-1">
                                        <p v-if="grade.semestre" class="font-medium">{{ grade.semestre }}</p>
                                        <p v-if="grade.curso && grade.curso.length" class="px-2 py-0.5 bg-gray-100 rounded-full">{{ grade.curso.join(' & ') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button @click.prevent="togglePin(grade.id)" class="p-1.5 rounded-full hover:bg-gray-200 transition-colors" :title="grade.pinned_at ? 'Desafixar' : 'Fixar'">
                                        <!-- Ícone omitido para simplicidade, a lógica está aqui -->
                                        <span :class="grade.pinned_at ? 'text-indigo-600' : 'text-gray-400'">📌</span>
                                    </button>
                                    <Link :href="route('grades.edit', grade.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button @click.prevent="confirmGradeDeletion(grade.id)" class="font-medium text-red-600 hover:text-red-900">Excluir</button>
                                </div>
                            </div>
                        </li>
                        <li v-if="filteredGrades.length === 0" class="text-center py-4 text-gray-500">
                            Nenhuma grade encontrada.
                        </li>
                    </ul>
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
