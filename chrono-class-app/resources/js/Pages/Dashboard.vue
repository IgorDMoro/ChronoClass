<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

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
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    Minhas Grades
                </h2>
                <Link
                    :href="route('grades.create')"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-md text-sm font-bold transition shadow-lg shadow-orange-500/20"
                >
                    + Nova Grade
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="grades.length === 0"
                    class="bg-zinc-800 p-12 text-center rounded-lg border border-zinc-700"
                >
                    <p class="text-gray-400">
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
                        class="bg-white dark:bg-zinc-800 overflow-hidden shadow-xl rounded-lg border border-gray-200 dark:border-orange-500/20 flex flex-col"
                    >
                        <div
                            class="p-4 border-b border-gray-100 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800/50"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3
                                        class="font-bold text-gray-900 dark:text-white"
                                    >
                                        {{ grade.turma?.nome }}
                                    </h3>
                                    <p
                                        class="text-xs text-orange-500 font-medium uppercase tracking-wider"
                                    >
                                        {{ grade.bimestre }}º Bimestre -
                                        {{ grade.ano }}
                                    </p>
                                </div>
                                <span
                                    class="px-2 py-1 text-[10px] rounded bg-zinc-700 text-zinc-300"
                                >
                                    {{ formatCursos(grade.curso) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 flex-1 overflow-auto max-h-64">
                            <table class="w-full text-[10px] border-collapse">
                                <thead>
                                    <tr class="text-gray-500">
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            H.
                                        </th>
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            SEG
                                        </th>
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            TER
                                        </th>
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            QUA
                                        </th>
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            QUI
                                        </th>
                                        <th
                                            class="p-1 border dark:border-zinc-700"
                                        >
                                            SEX
                                        </th>
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
                                            class="p-1 border dark:border-zinc-700 font-bold text-gray-400 italic"
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
                                            class="p-1 border dark:border-zinc-700 text-center"
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
                                                class="w-2 h-2 bg-orange-500 rounded-full mx-auto"
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
                            class="p-4 border-t border-gray-100 dark:border-zinc-700 flex gap-2"
                        >
                            <Link
                                :href="route('grades.show', grade.id)"
                                class="flex-1 text-center py-2 bg-zinc-700 hover:bg-zinc-600 text-white text-xs rounded transition"
                            >
                                Visualizar Detalhes
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
