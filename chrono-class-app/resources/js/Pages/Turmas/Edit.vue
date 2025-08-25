<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    turma: Object,
});

const form = useForm({
    nome: props.turma.nome,
    periodo: props.turma.periodo,
    ano_letivo: props.turma.ano_letivo,
});

const submit = () => {
    form.post(route('turmas.update-post', props.turma.id));
};
</script>

<template>
    <Head :title="`Editar Turma: ${form.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                Editar Turma
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-zinc-800 overflow-hidden shadow-2xl shadow-black/25 sm:rounded-lg ring-1 ring-inset ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-200">
                        <form @submit.prevent="submit">
                            <h5 class="text-xl font-semibold text-gray-300 mb-3 text-center">
                                Editando: {{ form.nome }}
                            </h5>
                            <div class="mt-8 border-t border-neutral-700 pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-300">Nome da Turma:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required autofocus />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="periodo" class="text-sm font-medium text-gray-300">Período:</label>
                                        <input type="text" id="periodo" v-model="form.periodo" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.periodo" />
                                    </div>

                                    <div>
                                        <label for="ano_letivo" class="text-sm font-medium text-gray-300">Ano Letivo:</label>
                                        <input type="number" id="ano_letivo" v-model="form.ano_letivo" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.ano_letivo" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-neutral-700">
                                <Link :href="route('turmas.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-600 rounded-md font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-neutral-700 hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
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
