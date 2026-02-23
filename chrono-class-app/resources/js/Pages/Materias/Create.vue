<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    grupos: Array,
});

const form = useForm({
    codigo: '',
    nome: '',
    carga_horaria: '',
    modalidade: 'Presencial',
    comp_tipo: 'Core',
    ensw_tipo: 'Core',
    grupo_id: null,
});

const submitForm = () => {
    form.post(route('materias.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Cadastrar Unidade Curricular" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cadastrar Nova Unidade Curricular
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submitForm">
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-300 mb-3 text-center">
                                Dados da Unidade Curricular
                            </h5>
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="codigo" class="text-sm font-medium text-gray-700 dark:text-gray-300">Código:</label>
                                        <input type="text" id="codigo" v-model="form.codigo" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.codigo" />
                                    </div>

                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="carga_horaria" class="text-sm font-medium text-gray-700 dark:text-gray-300">Carga Horária:</label>
                                        <input type="number" id="carga_horaria" v-model="form.carga_horaria" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.carga_horaria" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                     <div>
                                        <label for="modalidade" class="text-sm font-medium text-gray-700 dark:text-gray-300">Modalidade:</label>
                                        <select id="modalidade" v-model="form.modalidade" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required>
                                            <option value="Presencial">Presencial</option>
                                            <option value="UCD">UCD</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.modalidade" />
                                    </div>
                                    <div>
                                        <label for="comp_tipo" class="text-sm font-medium text-gray-700 dark:text-gray-300">Comp. Tipo (CC):</label>
                                        <select id="comp_tipo" v-model="form.comp_tipo" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required>
                                            <option value="Core">Core</option>
                                            <option value="Flex">Flex</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.comp_tipo" />
                                    </div>
                                    <div>
                                        <label for="ensw_tipo" class="text-sm font-medium text-gray-700 dark:text-gray-300">Ensw. Tipo (ES):</label>
                                        <select id="ensw_tipo" v-model="form.ensw_tipo" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required>
                                            <option value="Core">Core</option>
                                            <option value="Flex">Flex</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.ensw_tipo" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mt-6">
                                    <div>
                                        <label for="grupo_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">Grupo de Matérias:</label>
                                        <select id="grupo_id" v-model="form.grupo_id" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200">
                                            <option :value="null">-- Nenhum --</option>
                                            <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                                {{ grupo.nome }}
                                            </option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.grupo_id" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <Link :href="route('materias.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    Cadastrar UC
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
