<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    professor: Object, // O objeto professor sendo editado
    diasDaSemana: Array, // Recebido do Controller
    horariosDeAula: Array, // Recebido do Controller
});

// Pré-preenche o formulário com os dados do professor
const form = useForm({
    // Preenche com os dados existentes do professor
    nome: props.professor.nome,
    email: props.professor.email,
    telefone: props.professor.telefone,
    // Converte os horários de disponibilidade para o formato que as checkboxes esperam
    // `horarios_disponiveis_pivot` é o relacionamento que vem do backend
    horarios_disponiveis_selecionados: props.professor.horarios_disponiveis_pivot.map(
        phd => `${phd.dia_semana}-${phd.horario}`
    ),
});

const submit = () => {
    // Envia a requisição PUT para a rota de atualização
    form.put(route('professores.update', props.professor.id));
};

const formatDia = (dia) => {
    // Garante que o primeiro caractere seja maiúsculo para exibição
    return dia.charAt(0).toUpperCase() + dia.slice(1);
};
</script>

<template>
    <Head :title="`Editar Professor: ${professor.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Professor: {{ professor.nome }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="nome" value="Nome" />
                                <TextInput
                                    id="nome"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nome"
                                    required
                                    autofocus
                                    autocomplete="nome"
                                />
                                <InputError class="mt-2" :message="form.errors.nome" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    autocomplete="email"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="telefone" value="Telefone" />
                                <TextInput
                                    id="telefone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.telefone"
                                    autocomplete="telefone"
                                />
                                <InputError class="mt-2" :message="form.errors.telefone" />
                            </div>

                            <div class="mt-6">
                                <InputLabel value="Grade de Disponibilidade" class="mb-4 text-lg font-medium" />

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Dia / Horário</th>
                                                <th v-for="horario in horariosDeAula" :key="horario" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300 last:border-r-0">
                                                    {{ horario }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="dia in diasDaSemana" :key="dia">
                                                <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900 border-r border-gray-300">{{ formatDia(dia) }}</td>
                                                <td v-for="horario in horariosDeAula" :key="`${dia}-${horario}`" class="px-3 py-2 text-center border-r border-gray-300 last:border-r-0">
                                                    <input
                                                        type="checkbox"
                                                        :id="`disponibilidade-${dia}-${horario}`"
                                                        :value="`${dia}-${horario}`"
                                                        v-model="form.horarios_disponiveis_selecionados"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    />
                                                    <label :for="`disponibilidade-${dia}-${horario}`" class="sr-only">
                                                        {{ formatDia(dia) }} - {{ horario }}
                                                    </label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputError class="mt-2" :message="form.errors.horarios_disponiveis_selecionados" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Atualizar Professor
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>