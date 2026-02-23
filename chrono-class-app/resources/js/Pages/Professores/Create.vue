<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { computed } from 'vue';

const props = defineProps({
    diasDaSemana: Array,
    finaisDeSemana: Array,
    horariosDeAula: Array,
    horariosDeAulaFinaisDeSemana: Array,
    grupos: Array,
});

const form = useForm({
    matricula: "",
    nome: "",
    email: "",
    telefone: "",
    horarios_disponiveis_selecionados: [],
    grupos_ids: [],
});

const isSabadoSelected = computed(() => {
    return props.horariosDeAulaFinaisDeSemana.some(horario =>
        form.horarios_disponiveis_selecionados.includes(`sábado-${horario}`)
    );
});

const handleSabadoChange = (event) => {
    const isChecked = event.target.checked;

    props.horariosDeAulaFinaisDeSemana.forEach(horario => {
        const sabadoValue = `sábado-${horario}`;

        if (isChecked) {
            if (!form.horarios_disponiveis_selecionados.includes(sabadoValue)) {
                form.horarios_disponiveis_selecionados.push(sabadoValue);
            }
        } else {
            const index = form.horarios_disponiveis_selecionados.indexOf(sabadoValue);
            if (index > -1) {
                form.horarios_disponiveis_selecionados.splice(index, 1);
            }
        }
    });
};

const submit = () => {
    form.post(route("professores.store"), {
        onSuccess: () => form.reset(),
    });
};

const formatDia = (dia) => {
    return dia.charAt(0).toUpperCase() + dia.slice(1);
};
</script>

<template>
    <Head title="Cadastrar Professor" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Texto do header adaptado para os temas -->
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cadastrar Novo Professor
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Container principal do formulário adaptado para os temas -->
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <!-- Conteúdo do formulário com texto adaptado -->
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Título do formulário adaptado -->
                            <h5 class="text-xl font-semibold text-gray-800 dark:text-gray-300 mb-3 text-center">
                                Cadastro
                            </h5>
                            <!-- Divisor e título da seção adaptados -->
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">
                                    Dados Pessoais
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div>
                                        <!-- Label adaptado -->
                                        <label for="matricula" class="text-sm font-medium text-gray-700 dark:text-gray-300">Matrícula:</label>
                                        <!-- Input adaptado -->
                                        <input type="number" id="matricula" v-model="form.matricula" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.matricula" />
                                    </div>

                                    <div>
                                        <label for="nome" class="text-sm font-medium text-gray-700 dark:text-gray-300">Nome:</label>
                                        <input type="text" id="nome" v-model="form.nome" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" required />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>

                                    <div>
                                        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</label>
                                        <input type="email" id="email" v-model="form.email" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <label for="telefone" class="text-sm font-medium text-gray-700 dark:text-gray-300">Telefone:</label>
                                        <input type="text" id="telefone" v-model="form.telefone" class="mt-1 w-full rounded-md bg-gray-50 dark:bg-neutral-800 border-gray-300 dark:border-gray-300/40 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 text-gray-900 dark:text-gray-200" />
                                        <InputError class="mt-2" :message="form.errors.telefone" />
                                    </div>
                                </div>
                            </div>

                            <!-- Seção de Disponibilidade adaptada -->
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">
                                    Disponibilidade (Segunda a Sexta)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <!-- Borda e texto do header da tabela adaptados -->
                                            <tr class="border-b border-gray-200 dark:border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">
                                                    Dia / Horário
                                                </th>
                                                <th v-for="horario in horariosDeAula" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">
                                                    {{ horario }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Borda e texto da linha da tabela adaptados -->
                                            <tr v-for="dia in diasDaSemana" :key="dia" class="border-b border-gray-200 dark:border-neutral-800">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                                                    {{ formatDia(dia) }}
                                                </td>
                                                <td v-for="horario in horariosDeAula" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <!-- Checkbox adaptado -->
                                                    <input type="checkbox" :id="`disponibilidade-${dia}-${horario}`" :value="`${dia}-${horario}`" v-model="form.horarios_disponiveis_selecionados" class="rounded border-gray-300 dark:border-gray-300/40 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-white dark:focus:ring-offset-neutral-900" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">
                                    Disponibilidade (Sábado)
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-neutral-700">
                                                <th class="px-3 py-3 text-left text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">
                                                    Dia / Horário
                                                </th>
                                                <th v-for="horario in horariosDeAulaFinaisDeSemana" :key="horario" class="px-3 py-3 text-center text-xs font-medium text-orange-600 dark:text-orange-400/80 uppercase tracking-wider">
                                                    {{ horario }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dia in finaisDeSemana" :key="dia" class="border-b border-gray-200 dark:border-neutral-800 last:border-b-0">
                                                <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                                                    {{ formatDia(dia) }}
                                                </td>
                                                <td v-for="horario in horariosDeAulaFinaisDeSemana" :key="`${dia}-${horario}`" class="px-3 py-3 text-center">
                                                    <input
                                                        type="checkbox"
                                                        :id="`disponibilidade-${dia}-${horario}`"
                                                        :value="`${dia}-${horario}`"
                                                        v-model="form.horarios_disponiveis_selecionados"
                                                        class="rounded border-gray-300 dark:border-gray-300/40 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-white dark:focus:ring-offset-neutral-900"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputError class="mt-2" :message="form.errors.horarios_disponiveis_selecionados" />
                            </div>

                            <!-- Seção de Grupos de Matérias -->
                            <div class="mt-8 border-t border-gray-200 dark:border-neutral-700 pt-6">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-300 mb-3">
                                    Grupos de Matérias
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Selecione os grupos de matérias que este professor pode ensinar:
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="grupo in grupos" :key="grupo.id" class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            :id="`grupo-${grupo.id}`" 
                                            :value="grupo.id" 
                                            v-model.number="form.grupos_ids"
                                            class="rounded border-gray-300 dark:border-gray-300/40 bg-gray-100 dark:bg-neutral-900 text-orange-500 shadow-sm focus:ring-orange-500 focus:ring-offset-white dark:focus:ring-offset-neutral-900" 
                                        />
                                        <label :for="`grupo-${grupo.id}`" class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            {{ grupo.nome }}
                                        </label>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.grupos_ids" />
                            </div>
                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <!-- Botão Cancelar adaptado -->
                                <Link :href="route('professores.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <!-- Botão de Submissão com foco adaptado -->
                                <button type="submit" :disabled="form.processing" class="ms-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-900 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    Cadastrar Professor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>