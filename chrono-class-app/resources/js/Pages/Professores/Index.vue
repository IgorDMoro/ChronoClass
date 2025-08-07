<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

// O script permanece o mesmo, pois a lógica não muda.
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

const deleteProfessor = (professorId) => {
    if (!professorId) {
        alert('Não foi possível excluir o professor: ID inválido. Verifique o console.');
        return;
    }
    // Lembre-se que estamos usando POST para a exclusão, conforme sua necessidade.
    const deleteUrl = route('professores.delete-post', professorId);
    if (confirm('Tem certeza que deseja excluir este professor? Esta ação é irreversível.')) {
        form.post(deleteUrl, {
            preserveScroll: true,
            onSuccess: () => {
                // Ação de sucesso, se necessário. O reload já atualiza a lista.
            },
            onError: (errors) => {
                console.error('Erro ao excluir professor via POST:', errors);
                alert('Erro ao excluir professor. Verifique o console para mais detalhes.');
            }
        });
    }
};
</script>

<template>
    <Head title="Professores" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Card Principal: Gradiente removido para uma cor sólida. -->
                <div class="bg-neutral-900 overflow-hidden shadow-2xl shadow-black/25 sm:rounded-lg ring-1 ring-inset ring-orange-500/20">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-100 mb-4 sm:mb-0">Lista de Professores</h3>

                            <!-- Botão "Ghost": Bordas internas voltaram a ser neutras. -->
                            <Link :href="route('professores.create')" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-neutral-900 transition ease-in-out duration-150">
                                Cadastrar Novo Professor
                            </Link>
                        </div>

                        <!-- Container da Tabela: Borda superior neutra. -->
                        <div class="overflow-x-auto border-t border-neutral-700">
                            <table class="min-w-full">
                                <thead>
                                    <!-- Cabeçalho da Tabela: Borda inferior neutra. -->
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
                                    <!-- Linhas da Tabela: Borda inferior neutra. -->
                                    <tr v-for="professor in professores" :key="professor.id" class="border-b border-neutral-800 hover:bg-white/5 transition-colors duration-150">
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-200">{{ professor.nome }}</div>
                                            <div class="text-xs text-gray-300">{{ professor.email }}</div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ professor.telefone }}</td>
                                        
                                        <td class="px-6 py-4 whitespace-pre-line text-sm text-gray-300">
                                            {{ formatDisponibilidade(professor.horarios_disponiveis_pivot) }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('professores.edit', professor.id)" class="font-semibold text-gray-300 hover:text-orange-400 transition-colors duration-150 mr-6">Editar</Link>
                                            <button @click.prevent="deleteProfessor(professor.id)" class="font-semibold text-gray-500 hover:text-red-500 transition-colors duration-150 focus:outline-none">
                                                Excluir
                                            </button>
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
    </AuthenticatedLayout>
</template>
