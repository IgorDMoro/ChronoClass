<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    professor: Object,
    diasDaSemana: Array,
    finaisDeSemana: Array,
    horariosDeAula: Array,
    horariosDeAulaFinaisDeSemana: Array,
});

const form = useForm({
    matricula: props.professor.matricula,
    nome: props.professor.nome,
    email: props.professor.email,
    telefone: props.professor.telefone,
    horarios_disponiveis_selecionados: [],
});

// Popula os horários já selecionados quando o componente é montado
onMounted(() => {
    if (props.professor.horarios_disponiveis_pivot) {
        form.horarios_disponiveis_selecionados = props.professor.horarios_disponiveis_pivot.map(h => `${h.dia_semana}-${h.horario}`);
    }
});

const submit = () => {
    // A rota de update agora usa POST para simplicidade e para evitar problemas com PUT em alguns servidores
    form.post(route('professores.update-post', props.professor.id), {
        onError: (errors) => {
            console.error('Erro ao atualizar professor:', errors);
        }
    });
};

const formatDia = (dia) => {
    return dia.charAt(0).toUpperCase() + dia.slice(1);
};
</script>

<template>
    <Head :title="`Editar Professor: ${professor.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                Editar Professor
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-zinc-800 overflow-hidden shadow-2xl shadow-black/25 sm:rounded-lg ring-1 ring-inset ring-orange-500/20">
                    <div class="p-6 sm:p-8 text-gray-200">
                        <form @submit.prevent="submit">
                            <h5 class="text-xl font-semibold text-gray-300 mb-3 text-center">
                                Editando: {{ professor.nome }}
                            </h5>
                            <div class="mt-8 border-t border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-300 mb-3">
                                    Dados Pessoais
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div>
                                        <label for="matricula" class="text-sm font-medium text-gray-300">Matrícula:</label>
                                        <input type="number" id="matricula" v-model="form.matricula" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.matricula" />
                                    </div>

                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-300">Nome:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="email" class="text-sm font-medium text-gray-300">Email:</label>
                                        <input type="email" id="email" v-model="form.email" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <label for="telefone" class="text-sm font-medium text-gray-300">Telefone:</label>
                                        <input type="text" id="telefone" v-model="form.telefone" class="mt-1 w-full rounded-md bg-neutral-800 border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.telefone" />
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 border-t border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-300 mb-3">
                                    Disponibilidade (Segunda a Sexta)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-400/80 uppercase tracking-wider">
                                                    Dia / Horário
                                                </th>
                                                <th v-for="horario in horariosDeAula" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-400/80 uppercase tracking-wider">
                                                    {{ horario }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dia in diasDaSemana" :key="dia" class="border-b border-neutral-800">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-300">
                                                    {{ formatDia(dia) }}
                                                </td>
                                                <td v-for="horario in horariosDeAula" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <input type="checkbox" :id="`disponibilidade-${dia}-${horario}`" :value="`${dia}-${horario}`" v-model="form.horarios_disponiveis_selecionados" class="rounded border-gray-300/40 bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-neutral-900" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-300 mb-3">
                                    Disponibilidade (Sábado)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-400/80 uppercase tracking-wider">
                                                    Dia / Horário
                                                </th>
                                                <th v-for="horario in horariosDeAulaFinaisDeSemana" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-400/80 uppercase tracking-wider">
                                                    {{ horario }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dia in finaisDeSemana" :key="dia" class="border-b border-neutral-800 last:border-b-0">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-300">
                                                    {{ formatDia(dia) }}
                                                </td>
                                                <td v-for="horario in horariosDeAulaFinaisDeSemana" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <input type="checkbox" :id="`disponibilidade-${dia}-${horario}`" :value="`${dia}-${horario}`" v-model="form.horarios_disponiveis_selecionados" class="rounded border-gray-300/40 bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-neutral-900" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputError class="mt-2" :message="form.errors.horarios_disponiveis_selecionados" />
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-neutral-700">
                                <Link :href="route('professores.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-600 rounded-md font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-neutral-700 hover:border-neutral-700 focus:outline-none">
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