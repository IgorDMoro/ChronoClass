<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const isAluno = computed(() => usePage().props.auth.user.role === 'aluno');

const props = defineProps({
    grades: Array, // Você precisará passar as grades existentes do Controller
});

// Helper para formatar o nome dos cursos no card
const formatCursos = (cursos) => {
    if (!cursos) return "";
    const lista = Array.isArray(cursos) ? cursos : JSON.parse(cursos);
    return lista
        .map((c) => (c.includes("Engenharia") ? "ES" : "CC"))
        .join(" + ");
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
            <div class="py-12">
            <div class="mx-auto max-w-8xl sm:px-6 lg:px-8">
                
                <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-lg dark:shadow-2xl dark:shadow-black/25 sm:rounded-lg ring-1 ring-gray-200 dark:ring-inset dark:ring-orange-500/20">
                    <div class="p-6 sm:p-8">

                        <div class="mb-8 border-b border-gray-100 dark:border-zinc-700 pb-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                Grades Disponíveis
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                                Selecione uma grade abaixo para visualizar os horários e detalhes completos das aulas.
                            </p>
                        </div>

                        <div
                            v-if="grades.length === 0"
                            class="bg-gray-50 dark:bg-zinc-800/50 p-12 text-center rounded-lg border border-dashed border-gray-300 dark:border-zinc-600"
                        >
                            <div class="flex justify-center mb-4">
                                <span class="text-4xl">📅</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 font-medium">
                                Nenhuma grade encontrada. Comece criando uma agora!
                            </p>
                        </div>

                        <div
                            v-else
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                        >
                            <div
                                v-for="grade in grades"
                                :key="grade.id"
                                class="bg-white dark:bg-zinc-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-zinc-700 hover:border-orange-500/50 dark:hover:border-orange-500/50 transition-colors duration-200 flex flex-col"
                            >
                                <div
                                    class="p-4 border-b border-gray-100 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900/50"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3
                                                class="font-bold text-gray-900 dark:text-white"
                                            >
                                                {{ grade.turma?.nome }}
                                            </h3>
                                            <p
                                                class="text-xs text-orange-600 dark:text-orange-500 font-medium uppercase tracking-wider mt-0.5"
                                            >
                                                {{ grade.bimestre }}º Bimestre -
                                                {{ grade.ano }}
                                            </p>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-[10px] font-semibold rounded bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
                                        >
                                            {{ formatCursos(grade.curso) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4 flex-1 overflow-auto max-h-64">
                                    <table class="w-full text-[10px] border-collapse">
                                        <thead>
                                            <tr class="text-gray-500 dark:text-zinc-400">
                                                <th class="p-1 border dark:border-zinc-700/50">H.</th>
                                                <th class="p-1 border dark:border-zinc-700/50">SEG</th>
                                                <th class="p-1 border dark:border-zinc-700/50">TER</th>
                                                <th class="p-1 border dark:border-zinc-700/50">QUA</th>
                                                <th class="p-1 border dark:border-zinc-700/50">QUI</th>
                                                <th class="p-1 border dark:border-zinc-700/50">SEX</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="bloco in [
                                                    '19:00-20:30',
                                                    '20:45-22:15',
                                                ]"
                                                :key="bloco"
                                            >
                                                <td
                                                    class="p-1 border dark:border-zinc-700/50 font-bold text-gray-400 dark:text-zinc-500 italic text-center"
                                                >
                                                    {{ bloco.split(":")[0] }}h
                                                </td>
                                                <td
                                                    v-for="dia in [
                                                        'SEGUNDA',
                                                        'TERÇA',
                                                        'QUARTA',
                                                        'QUINTA',
                                                        'SEXTA',
                                                    ]"
                                                    :key="dia"
                                                    class="p-1 border dark:border-zinc-700/50 text-center"
                                                >
                                                    <div
                                                        v-if="
                                                            grade.horarios?.find(
                                                                (h) =>
                                                                    h.dia_semana ===
                                                                        dia &&
                                                                    h.horario_bloco ===
                                                                        bloco,
                                                            )
                                                        "
                                                        class="w-2.5 h-2.5 bg-orange-500 rounded-full mx-auto shadow-sm"
                                                        :title="
                                                            grade.horarios.find(
                                                                (h) =>
                                                                    h.dia_semana ===
                                                                        dia &&
                                                                    h.horario_bloco ===
                                                                        bloco,
                                                            )?.materia?.nome
                                                        "
                                                    ></div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div
                                    class="p-4 border-t border-gray-100 dark:border-zinc-700 bg-white dark:bg-zinc-800"
                                >
                                    <Link
                                        :href="route('grades.show', grade.id)"
                                        class="block w-full text-center p-5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-white text-xs font-semibold uppercase tracking-wider rounded transition-colors duration-200"
                                    >
                                        Visualizar Detalhes
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>