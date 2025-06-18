<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    salas: Array, // Propriedade 'salas' que virá do Controller
});

const form = useForm({});

const deleteSala = (salaId) => {
    if (confirm('Tem certeza que deseja excluir esta sala? Esta ação é irreversível.')) {
        // MUDANÇA AQUI: Use form.post() e a nova rota 'salas.delete-post'
        form.post(route('salas.delete-post', salaId), {
            onSuccess: () => {
                // O Inertia já recarrega a página ao receber um redirect do controller,
                // então você não precisa de lógica extra aqui, a menos que queira algo específico.
            },
            onError: (errors) => {
                console.error('Erro ao excluir sala:', errors);
                alert('Erro ao excluir sala. Verifique o console para mais detalhes.');
            }
        });
    }
};
</script>

<template>
    <Head title="Salas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Salas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Salas</h3>

                        <div class="mb-4">
                            <Link :href="route('salas.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cadastrar Nova Sala
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidade</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Ações</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sala in salas" :key="sala.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sala.nome }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sala.capacidade }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sala.campus }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('salas.edit', sala.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
                                            <button
                                                @click.prevent="deleteSala(sala.id)"
                                                class="text-red-600 hover:text-red-900 focus:outline-none"
                                            >
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="salas.length === 0">
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">Nenhuma sala cadastrada.</td>
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