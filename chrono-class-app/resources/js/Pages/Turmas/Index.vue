<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    turmas: Array,
});
</script>

<template>
    <Head title="Turmas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lista de Turmas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <Link :href="route('turmas.create')" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                            Adicionar Nova Turma
                        </Link>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ano Letivo</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="turma in turmas" :key="turma.id">
    <td class="px-6 py-4 whitespace-nowrap">{{ turma.nome }}</td>
    <td class="px-6 py-4 whitespace-nowrap">{{ turma.periodo }}</td>
    <td class="px-6 py-4 whitespace-nowrap">{{ turma.ano_letivo }}</td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <Link :href="route('turmas.edit', turma.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</Link>
        
        <Link 
            :href="route('turmas.destroy', turma.id)" 
            method="post" 
            as="button" 
            type="button" 
            class="text-red-600 hover:text-red-900" 
            onclick="return confirm('Tem certeza que deseja excluir?')"
        >
            Excluir
        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>