<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const isAluno = computed(() => usePage().props.auth.user.role === 'aluno');

const props = defineProps({
    grade: Object,
});

const formatCursos = (cursos) => {
    if (!cursos) return '';
    const lista = Array.isArray(cursos) ? cursos : JSON.parse(cursos);
    return lista.map(c => c.includes('Engenharia') ? 'ES' : 'CC').join(' + ');
};

const dias = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA'];
const blocos = ['19:00-20:30', '20:45-22:15'];

const getHorarios = (dia, bloco) => {
    return props.grade.horarios?.filter(h => h.dia_semana === dia && h.horario_bloco === bloco) || [];
};

const tipoSlotLabel = (tipo) => {
    if (!tipo) return null;
    if (tipo === 'Flex') return 'Flex';
    if (tipo.includes('Engenharia')) return 'Eng. SW';
    if (tipo.includes('Ciências')) return 'C. Comp';
    if (tipo.includes('Ambos')) return 'Ambos';
    return tipo;
};

const tipoSlotClass = (tipo) => {
    if (!tipo) return 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300';
    if (tipo === 'Flex') return 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300';
    if (tipo.includes('Engenharia')) return 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300';
    if (tipo.includes('Ciências')) return 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300';
    if (tipo.includes('Ambos')) return 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-300';
    return 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300';
};
</script>

<template>
    <Head :title="`Grade - ${grade.turma?.nome}`" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Container principal igual ao da index -->
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8">

                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                Detalhes da Grade:
                                <span class="text-orange-600 dark:text-orange-400">{{ grade.turma?.nome }}</span>
                            </h3>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('grades.index')"
                                    class="inline-flex items-center px-4 py-2 bg-zinc-100 dark:bg-zinc-700 border border-gray-200 dark:border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-zinc-300 uppercase tracking-widest hover:bg-zinc-200 dark:hover:bg-zinc-600 transition ease-in-out duration-150"
                                >
                                    ⬅️ Voltar
                                </Link>
                                <Link
                                    v-if="!isAluno"
                                    :href="route('grades.edit', grade.id)"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition ease-in-out duration-150"
                                >
                                    Editar Grade
                                </Link>
                            </div>
                        </div>

                        <!-- Cards de info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-5 border-l-4 border-l-orange-500">
                                <p class="text-xs text-gray-500 dark:text-zinc-500 uppercase font-bold tracking-wider">Turma</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ grade.turma?.nome }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-5 border-l-4 border-l-gray-300 dark:border-l-zinc-600">
                                <p class="text-xs text-gray-500 dark:text-zinc-500 uppercase font-bold tracking-wider">Período</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ grade.bimestre }}º Bimestre / {{ grade.ano }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg p-5 border-l-4 border-l-gray-300 dark:border-l-zinc-600">
                                <p class="text-xs text-gray-500 dark:text-zinc-500 uppercase font-bold tracking-wider">Cursos</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ formatCursos(grade.curso) }}</p>
                            </div>
                        </div>

                        <!-- Tabela -->
                        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left table-fixed min-w-[800px]">
                                    <thead class="text-xs text-gray-500 dark:text-zinc-400 uppercase bg-gray-50 dark:bg-black/40">
                                        <tr>
                                            <th class="px-4 py-4 border-b border-gray-200 dark:border-zinc-800 w-32 text-center">Horário</th>
                                            <th
                                                v-for="dia in dias"
                                                :key="dia"
                                                class="px-2 py-4 border-b border-gray-200 dark:border-zinc-800 text-center"
                                            >
                                                {{ dia }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                        <tr
                                            v-for="bloco in blocos"
                                            :key="bloco"
                                            class="hover:bg-orange-50 dark:hover:bg-orange-500/5 transition-colors"
                                        >
                                            <td class="px-4 py-8 font-bold text-orange-600 dark:text-orange-500 bg-gray-50 dark:bg-black/20 text-center border-r border-gray-200 dark:border-zinc-800/50">
                                                {{ bloco }}
                                            </td>
                                            <td
                                                v-for="dia in dias"
                                                :key="dia"
                                                class="px-2 py-4 text-center border-l border-gray-100 dark:border-zinc-800/30 align-top"
                                            >
                                                <div v-if="getHorarios(dia, bloco).length" class="space-y-2 p-1">
                                                    <div
                                                        v-for="h in getHorarios(dia, bloco)"
                                                        :key="h.id"
                                                        class="rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-2 shadow-sm"
                                                    >
                                                        <p class="font-bold text-gray-800 dark:text-zinc-100 leading-tight break-words text-[11px] md:text-xs">
                                                            {{ h.materia?.nome }}
                                                        </p>
                                                        <span v-if="h.tipo_slot" class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-bold rounded-full" :class="tipoSlotClass(h.tipo_slot)">
                                                            {{ tipoSlotLabel(h.tipo_slot) }}
                                                        </span>
                                                        <p class="text-[10px] text-orange-600 dark:text-orange-400 font-medium mt-1">
                                                            👨‍🏫 {{ h.professor?.nome }}
                                                        </p>
                                                        <p class="text-[10px] text-gray-400 dark:text-zinc-500">
                                                            📍 {{ h.sala_relacionamento?.nome || h.sala }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div v-else class="text-gray-300 dark:text-zinc-700 italic text-[10px] flex flex-col items-center opacity-60">
                                                    <span>Vago</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Atividades ao Sábado -->
                        <div v-if="grade.horarios?.some(h => h.dia_semana === 'SABADO')" class="mt-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-5">
                            <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider mb-3">📅 Atividades ao Sábado</h4>
                            <div class="flex gap-3 flex-wrap">
                                <span
                                    v-for="h in grade.horarios.filter(h => h.dia_semana === 'SABADO')"
                                    :key="h.id"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-neutral-800 border border-blue-200 dark:border-blue-500/30 shadow-sm"
                                >
                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ h.materia?.nome }}</span>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>