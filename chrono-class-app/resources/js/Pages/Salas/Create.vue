<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    capacidade: '',
    campus: 'campus ipolon', // Valor padrão para o campo select
});

const submit = () => {
    form.post(route('salas.store'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Cadastrar Sala" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cadastrar Sala</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="nome" value="Nome da Sala" />
                                <TextInput
                                    id="nome"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nome"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="capacidade" value="Capacidade" />
                                <TextInput
                                    id="capacidade"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model.number="form.capacidade"
                                    required
                                    min="1"
                                />
                                <InputError class="mt-2" :message="form.errors.capacidade" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="campus" value="Campus" />
                                <select
                                    id="campus"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.campus"
                                    required
                                >
                                    <option value="campus ipolon">Campus Ipolon</option>
                                    <option value="campus sede">Campus Sede</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.campus" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Salvar Sala
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>