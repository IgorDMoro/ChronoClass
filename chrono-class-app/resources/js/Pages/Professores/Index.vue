<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// ** Importe 'router' aqui para usar router.reload() **
import { Head, Link, useForm, router } from '@inertiajs/vue3'; 

const props = defineProps({
    professores: Array,
    diasDaSemana: Array, // Recebido do Controller
    horariosDeAula: Array, // Recebido do Controller
});

// ** MENSAGEM DE VERSÃO ATUALIZADA - PARA CONFIRMAR SE O ARQUIVO CORRETO ESTÁ SENDO CARREGADO **
console.log('Versão do Index.vue: RESOLUCAO_FINAL_V7'); 

// LOG PARA VER A LISTA COMPLETA DE PROFESSORES QUE CHEGA NAS PROPS DA VIEW
console.log('Professores recebidos nas props do Index.vue:', props.professores);

const form = useForm({}); // Usado para operações form.delete, form.post, etc.

// Função para formatar e exibir a disponibilidade
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
        formattedString += `${formatDia(dia)}: ${groupedByDay[dia].join(', ')}\n`;
    }
    return formattedString.trim();
};

const formatDia = (dia) => {
    return dia.charAt(0).toUpperCase() + dia.slice(1);
};

// ** Lógica de exclusão **
// Esta função recebe APENAS O ID do professor.
const deleteProfessor = (professorId) => {
    // Este log é CRUCIAL: ele deve mostrar o ID correto do professor a ser excluído
    console.log('ID do professor a ser excluído (na função deleteProfessor):', professorId);

    if (!professorId) {
        console.error('Erro: ID do professor não fornecido para exclusão. Cancelando requisição.');
        alert('Não foi possível excluir o professor: ID inválido. Verifique o console.');
        return; // Impede a requisição se o ID for inválido
    }

    if (confirm('Tem certeza que deseja excluir este professor? Esta ação é irreversível.')) {
        form.delete(route('professores.destroy', professorId), {
            onSuccess: () => {
                // Força o recarregamento da página após o sucesso da exclusão.
                // Isso garante que a lista seja atualizada e a mensagem de erro desapareça.
                router.reload({ preserveScroll: true }); 
            },
            onError: (errors) => {
                // Em caso de erro do Laravel (como validação, mas aqui é menos provável na exclusão)
                console.error('Erro ao excluir professor:', errors);
                alert('Erro ao excluir professor. Verifique o console para mais detalhes.');
            }
        });
    }
};
</script>

<template>
    <Head title="Professores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Professores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Professores</h3>

                        <div class="mb-4">
                            <Link :href="route('professores.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cadastrar Novo Professor
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponibilidade</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Ações</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="professor in professores" :key="professor.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ professor.nome }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ professor.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ professor.telefone }}</td>
                                        <td class="px-6 py-4 whitespace-pre-line text-sm text-gray-800">
                                            {{ formatDisponibilidade(professor.horarios_disponiveis_pivot) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('professores.edit', professor.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                            <!-- ** MUDANÇA: Usando <button> no lugar de <a> e passando professor.id diretamente ** -->
                                            <button
                                                @click.prevent="deleteProfessor(professor.id)"
                                                class="text-red-600 hover:text-red-900 focus:outline-none"
                                            >
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="professores.length === 0">
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Nenhum professor cadastrado.</td>
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
