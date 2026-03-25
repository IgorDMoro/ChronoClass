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

const getHorario = (dia, bloco) => {
    return props.grade.horarios?.find(h => h.dia_semana === dia && h.horario_bloco === bloco);
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
                                                class="px-2 py-4 text-center border-l border-gray-100 dark:border-zinc-800/30"
                                            >
                                                <div v-if="getHorario(dia, bloco)" class="space-y-2 p-1">
                                                    <p class="font-bold text-gray-800 dark:text-zinc-100 leading-tight break-words text-[11px] md:text-xs">
                                                        {{ getHorario(dia, bloco).materia?.nome }}
                                                    </p>
                                                    <p class="text-[10px] text-orange-600 dark:text-orange-400 font-medium">
                                                        👨‍🏫 {{ getHorario(dia, bloco).professor?.nome }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-400 dark:text-zinc-500">
                                                        📍 {{ getHorario(dia, bloco).sala_relacionamento?.nome || getHorario(dia, bloco).sala }}
                                                    </p>
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

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>