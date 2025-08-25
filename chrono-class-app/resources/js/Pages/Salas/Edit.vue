<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    sala: Object,
});

const form = useForm({
    nome: props.sala.nome,
    capacidade: props.sala.capacidade,
    campus: props.sala.campus,
});

const submit = () => {
    form.post(route("salas.update-post", props.sala.id));
};
</script>

<template>
    <Head :title="`Editar Sala: ${form.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                Editar Sala
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
                                        <label for="nome" class="text-sm font-medium text-gray-300">Nome da Sala:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required autofocus />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="capacidade" class="text-sm font-medium text-gray-300">Capacidade:</label>
                                        <input type="number" id="capacidade" v-model.number="form.capacidade" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required min="1" />
                                        <InputError class="mt-2" :message="form.errors.capacidade" />
                                    </div>

                                    <div>
                                        <label for="campus" class="text-sm font-medium text-gray-300">Campus:</label>
                                        <select id="campus" v-model="form.campus" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required>
                                            <option value="campus ipolon">Campus Ipolon</option>
                                            <option value="campus sede">Campus Sede</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.campus" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-neutral-700">
                                <Link :href="route('salas.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-600 rounded-md font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-neutral-700 hover:border-neutral-700 focus:outline-none">
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
