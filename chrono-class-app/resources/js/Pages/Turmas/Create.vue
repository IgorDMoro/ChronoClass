<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const bimestres = ['B1', 'B2', 'B3', 'B4'];

const form = useForm({
    nome: '',
    periodo: '',
    ano_entrada: new Date().getFullYear(),
    bimestre_entrada: '',
});

const submit = () => {
    form.post(route('turmas.store'));
};
</script>

<template>
    <Head title="Cadastrar Turma" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cadastrar Nova Turma
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submit">
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-300 mb-3 text-center">
                                Dados da Turma
                            </h5>
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome da Turma (Ex: E3 2024):</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required autofocus />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="periodo" class="text-sm font-medium text-gray-700 dark:text-gray-300">Período (Ex: Matutino):</label>
                                        <input type="text" id="periodo" v-model="form.periodo" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.periodo" />
                                    </div>

                                    <div>
                                        <label for="ano_entrada" class="text-sm font-medium text-gray-700 dark:text-gray-300">Ano de Entrada:</label>
                                        <input type="number" id="ano_entrada" v-model="form.ano_entrada" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.ano_entrada" />
                                    </div>

                                    <div>
                                        <label for="bimestre_entrada" class="text-sm font-medium text-gray-700 dark:text-gray-300">Bimestre de Entrada:</label>
                                        <select id="bimestre_entrada" v-model="form.bimestre_entrada" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required>
                                            <option value="" disabled>Selecione...</option>
                                            <option v-for="b in bimestres" :key="b" :value="b">{{ b }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.bimestre_entrada" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <Link :href="route('turmas.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    Cadastrar Turma
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>