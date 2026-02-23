<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    grupos: Array,
});

const deletingId = ref(null);

const confirmDelete = (id) => {
    if (confirm('Tem certeza que deseja deletar este grupo? Isso afetará as matérias associadas.')) {
        deletingId.value = id;
        router.post(route('grupos_materias.destroy-post', id), {}, {
            onSuccess: () => {
                deletingId.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Grupos de Matérias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Grupos de Matérias
                </h2>
                <Link :href="route('grupos_materias.create')" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150">
                    + Novo Grupo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="grupos.length === 0" class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 text-center text-gray-900 dark:text-gray-200">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Nenhum grupo de matérias cadastrado ainda.</p>
                        <Link :href="route('grupos_materias.create')" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150">
                            + Criar Primeiro Grupo
                        </Link>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="grupo in grupos" :key="grupo.id" class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ grupo.nome }}
                                    </h3>
                                    <p v-if="grupo.descricao" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ grupo.descricao }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="route('grupos_materias.edit', grupo.id)" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150">
                                        Editar
                                    </Link>
                                    <button @click="confirmDelete(grupo.id)" :disabled="deletingId === grupo.id" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150 disabled:opacity-50">
                                        {{ deletingId === grupo.id ? 'Deletando...' : 'Deletar' }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-neutral-700">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Matérias ({{ grupo.materias?.length || 0 }})
                                        </h4>
                                        <div v-if="grupo.materias && grupo.materias.length > 0" class="space-y-1">
                                            <div v-for="materia in grupo.materias" :key="materia.id" class="text-sm text-gray-600 dark:text-gray-400">
                                                • {{ materia.nome }} ({{ materia.codigo }})
                                            </div>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                            Nenhuma matéria associada
                                        </p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Professores ({{ grupo.professores?.length || 0 }})
                                        </h4>
                                        <div v-if="grupo.professores && grupo.professores.length > 0" class="space-y-1">
                                            <div v-for="professor in grupo.professores" :key="professor.id" class="text-sm text-gray-600 dark:text-gray-400">
                                                • {{ professor.nome }} (Mat: {{ professor.matricula }})
                                            </div>
                                        </div>
                                        <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                            Nenhum professor associado
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
