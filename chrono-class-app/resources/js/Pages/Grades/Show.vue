<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    grade: Object,
});

const diasDaSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA'];
const horariosBlocos = ['19:00-20:30', '20:45-22:15'];

const scheduleGrid = computed(() => {
    const grid = {};
    diasDaSemana.forEach(dia => {
        grid[dia] = {};
        horariosBlocos.forEach(hora => {
            grid[dia][hora] = props.grade.horarios.filter(h => h.dia_semana === dia && h.horario_bloco === hora);
        });
    });
    return grid;
});
</script>

<template>
    <Head :title="grade.nome" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-xl text-gray-800 uppercase">{{ grade.nome }}</h2>
                                    </div>
                <Link :href="route('grades.edit', grade.id)" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">
                    Editar Grade
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="overflow-x-auto p-4">
                        <table class="min-w-full border-collapse text-sm">
                                                        <tbody class="text-center align-top">
                                <template v-for="(bloco, hIndex) in horariosBlocos" :key="bloco">
                                    <tr>
                                        <td class="p-2 font-semibold text-gray-700 align-middle">{{ bloco.replace('-', ' - ') }}</td>
                                        <td v-for="dia in diasDaSemana" :key="`${dia}_${bloco}`" class="border border-gray-200 p-1 min-h-[100px]">
                                                                                    </td>
                                    </tr>
                                                                        <tr v-if="hIndex === 0" class="h-8">
                                        <td colspan="6" class="bg-gray-100 text-gray-500 font-bold text-xs tracking-wider border-y border-gray-200">INTERVALO</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>