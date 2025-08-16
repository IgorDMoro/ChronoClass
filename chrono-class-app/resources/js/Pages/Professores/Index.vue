<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// --- Lógica do Modal de Exclusão ---
const showConfirmDeleteModal = ref(false);
const professorToDelete = ref(null);

const openConfirmModal = (professor) => {
    professorToDelete.value = professor;
    showConfirmDeleteModal.value = true;
};

const closeModal = () => {
    showConfirmDeleteModal.value = false;
    professorToDelete.value = null;
};

const executeDelete = () => {
    if (!professorToDelete.value) return;
    
    const deleteUrl = route('professores.delete-post', professorToDelete.value.id);
    
    form.post(deleteUrl, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error('Erro ao excluir professor via POST:', errors);
            closeModal();
        }
    });
};
// --- Fim da Lógica do Modal ---


const props = defineProps({
    professores: Array,
    diasDaSemana: Array,
    horariosDeAula: Array,
});

const form = useForm({});

const formatDisponibilidade = (horariosDisponiveisPivot) => {
    if (!horariosDisponiveisPivot || horariosDisponiveisPivot.length === 0) {
        return 'N/A';
    }
    const groupedByDay = horariosDisponiveisPivot.reduce((acc, current) => {
        if (!acc[current.dia_semana]) {
            acc[current.dia_semana] = [];
        }
        acc[current.dia_semana].push(current.horario);
        return acc;
    }, {});
    let formattedString = '';
    const sortedDays = Object.keys(groupedByDay).sort((a, b) => {
        const order = ['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo'];
        return order.indexOf(a) - order.indexOf(b);
    });
    for (const dia of sortedDays) {
        const capitalizedDia = dia.charAt(0).toUpperCase() + dia.slice(1);
        formattedString += `${capitalizedDia}: ${groupedByDay[dia].join(', ')}\n`;
    }
    return formattedString.trim();
};
</script>

<template>
    <Head title="Professores" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-zinc-800 overflow-hidden shadow-2xl shadow-black/25 sm:rounded-lg ring-1 ring-inset ring-orange-500/20">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-100 mb-4 sm:mb-0">Lista de Professores</h3>
                            <Link :href="route('professores.create')" class="ms-4 inline-flex items-center px-4 py-2 bg-neutral-700/30 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none ring-1 ring-orange-500 ring-offset-0 ring-offset-neutral-900 transition ease-in-out duration-150 ">
                                Cadastrar Novo Professor
                            </Link>
                        </div>

                        <div class="overflow-x-auto border-t border-neutral-700">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-neutral-700">
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Professor</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Contato</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Disponibilidade</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Ações</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="professor in professores" :key="professor.id" class="border-b border-neutral-800 hover:bg-white/5 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-200">{{ professor.nome }}</div>
                                            <div class="text-xs text-gray-400">Matrícula: {{ professor.matricula }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <div>{{ professor.telefone }}</div>
                                            <div>{{ professor.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-pre-line text-sm text-gray-300">
                                            {{ formatDisponibilidade(professor.horarios_disponiveis_pivot) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-x-6">
                                                <div class="relative group">
                                                    <Link :href="route('professores.edit', professor.id)" class="text-gray-400 hover:text-orange-400 transition-colors duration-150">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                            <path d="m13.586 3.586.707.707a1 1 0 0 1 0 1.414l-9.5 9.5a1 1 0 0 1-.493.275l-2.5 1a1 1 0 0 1-1.11-1.11l1-2.5a1 1 0 0 1 .275-.493l9.5-9.5a1 1 0 0 1 1.414 0Z" />
                                                            <path d="m11.5 6.5 2 2" />
                                                        </svg>
                                                    </Link>
                                                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none">
                                                        Editar
                                                    </div>
                                                </div>
                                                
                                                <div class="relative group">
                                                    <button @click.prevent="openConfirmModal(professor)" class="text-gray-500 hover:text-red-500 transition-colors duration-150 focus:outline-none">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.58.22-2.365.468a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193v-.443A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-neutral-600 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none">
                                                        Excluir
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="professores.length === 0">
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            Nenhum professor cadastrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showConfirmDeleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-neutral-800 max-w-lg w-full rounded-lg shadow-2xl ring-1 ring-white/10 p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-white">
                    Confirmar Exclusão
                </h3>
                <p class="mt-2 text-sm text-gray-400">
                    Tem certeza que deseja excluir o professor <strong class="text-orange-400">{{ professorToDelete.nome }}</strong>? Esta ação não pode ser desfeita.
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <button @click="closeModal" class="px-4 py-2 text-sm font-semibold text-gray-300 bg-neutral-700 hover:bg-neutral-600 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button @click="executeDelete" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                        Excluir
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>