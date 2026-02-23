<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    grupo: Object,
});

const form = useForm({
    nome: props.grupo.nome,
    descricao: props.grupo.descricao,
});

const submitForm = () => {
    form.post(route('grupos_materias.update-post', props.grupo.id));
};
</script>

<template>
    <Head :title="`Editar Grupo: ${form.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editar Grupo de Matérias
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submitForm">
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-300 mb-3 text-center">
                                Editando: {{ form.nome }}
                            </h5>
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome do Grupo:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="descricao" class="text-sm font-medium text-gray-700 dark:text-gray-300">Descrição (Opcional):</label>
                                        <textarea id="descricao" v-model="form.descricao" rows="4" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200"></textarea>
                                        <InputError class="mt-2" :message="form.errors.descricao" />
                                    </div>
                                </div>
                            </div>

                            <div v-if="grupo.materias && grupo.materias.length > 0" class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-4">
                                    Matérias Associadas ({{ grupo.materias.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="materia in grupo.materias" :key="materia.id" class="flex items-center p-3 bg-gray-50 dark:bg-neutral-700 rounded-md">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <strong>{{ materia.nome }}</strong> ({{ materia.codigo }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="grupo.professores && grupo.professores.length > 0" class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-4">
                                    Professores Associados ({{ grupo.professores.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="professor in grupo.professores" :key="professor.id" class="flex items-center p-3 bg-gray-50 dark:bg-neutral-700 rounded-md">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            <strong>{{ professor.nome }}</strong> (Mat: {{ professor.matricula }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <Link :href="route('grupos_materias.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
