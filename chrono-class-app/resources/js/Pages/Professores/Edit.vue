<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    professor: Object,
    diasDaSemana: Array,
    finaisDeSemana: Array, // Novo prop
    horariosDeAula: Array,
    horariosDeAulaFinaisDeSemana: Array, // Novo prop
});

const selectedHorarios = ref([]);

const form = useForm({
    nome: props.professor.nome,
    email: props.professor.email,
    telefone: props.professor.telefone,
    horarios_disponiveis_selecionados: [],
});

onMounted(() => {
    if (props.professor.horarios_disponiveis_pivot) {
        selectedHorarios.value = props.professor.horarios_disponiveis_pivot.map(h => `${h.dia_semana}-${h.horario}`);
    }
});

watch(selectedHorarios, (newValue) => {
    form.horarios_disponiveis_selecionados = newValue;
}, { deep: true });

const formatDia = (dia) => {
    return dia.charAt(0).toUpperCase() + dia.slice(1);
};

const submit = () => {
    const updateUrl = route('professores.update', props.professor.id);
    form.put(updateUrl, { // Usando PUT para update, conforme padrão RESTful
        onSuccess: () => {
            router.visit(route('professores.index'));
        },
        onError: (errors) => {
            console.error('Erro ao atualizar professor:', errors);
        }
    });
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
                            <div class="mb-4">
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome:</label>
                                <input type="text" id="nome" v-model="form.nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <div v-if="form.errors.nome" class="text-red-600 text-sm mt-1">{{ form.errors.nome }}</div>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                                <input type="email" id="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                            </div>
                            <div class="mb-4">
                                <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone:</label>
                                <input type="text" id="telefone" v-model="form.telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div v-if="form.errors.telefone" class="text-red-600 text-sm mt-1">{{ form.errors.telefone }}</div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Disponibilidade (Segunda a Sexta)</h4>
                                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-r border-gray-200">DIA / HORÁRIO</th>
                                                <th v-for="horario in horariosDeAula" :key="horario" scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-l border-gray-200">{{ horario }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="dia in diasDaSemana" :key="dia">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border-r border-gray-200">{{ formatDia(dia) }}</td>
                                                <td v-for="horario in horariosDeAula" :key="`${dia}-${horario}-checkbox`" class="px-6 py-4 text-center">
                                                    <input type="checkbox" :id="`${dia}-${horario}`" :value="`${dia}-${horario}`" v-model="selectedHorarios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Disponibilidade (Sábado)</h4>
                                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-r border-gray-200">DIA / HORÁRIO</th>
                                                <th v-for="horario in horariosDeAulaFinaisDeSemana" :key="horario" scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-b border-l border-gray-200">{{ horario }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="dia in finaisDeSemana" :key="dia">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border-r border-gray-200">{{ formatDia(dia) }}</td>
                                                <td v-for="horario in horariosDeAulaFinaisDeSemana" :key="`${dia}-${horario}-checkbox`" class="px-6 py-4 text-center">
                                                    <input type="checkbox" :id="`${dia}-${horario}`" :value="`${dia}-${horario}`" v-model="selectedHorarios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="form.errors.horarios_disponiveis_selecionados" class="text-red-600 text-sm mt-1">{{ form.errors.horarios_disponiveis_selecionados }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <span v-if="form.processing">Salvando...</span>
                                    <span v-else>Salvar Alterações</span>
                                </button>
                                <Link :href="route('professores.index')" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancelar
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>