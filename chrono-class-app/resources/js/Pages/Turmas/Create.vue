<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    periodo: '',
    ano_letivo: new Date().getFullYear(),
});

const submit = () => {
    form.post(route('turmas.store'));
};
</script>

<template>
    <Head title="Adicionar Turma" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Nova Turma</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="nome" value="Nome da Turma (Ex: 3º Ano A)" />
                                <TextInput id="nome" v-model="form.nome" type="text" class="mt-1 block w-full" required autofocus />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="periodo" value="Período (Ex: Matutino)" />
                                <TextInput id="periodo" v-model="form.periodo" type="text" class="mt-1 block w-full" required />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="ano_letivo" value="Ano Letivo" />
                                <TextInput id="ano_letivo" v-model="form.ano_letivo" type="number" class="mt-1 block w-full" required />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Salvar Turma
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>