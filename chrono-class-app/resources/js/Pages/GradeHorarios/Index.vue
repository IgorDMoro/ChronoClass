<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// As props 'grades', 'diasDaSemana', 'horariosDeAula' vêm do GradeHorarioController@index
const props = defineProps({
    grades: Array,
    diasDaSemana: Array,
    horariosDeAula: Array,
});
</script>

<template>
    <Head title="Grade Horária" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grade Horária</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Listagem de Grades Horárias</h3>
                            <Link :href="route('grade_horarios.create')" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Adicionar Nova Grade
                            </Link>
                        </div>

                        <div v-if="grades.length === 0" class="text-gray-600">
                            Nenhuma grade horária cadastrada ainda.
                        </div>

                        <div v-else>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Matéria
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Professor
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sala
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dia
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Horário
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Turmas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="grade in grades" :key="grade.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ grade.materia ? grade.materia.nome : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ grade.professor ? grade.professor.nome : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ grade.sala ? grade.sala.nome : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ grade.dia_semana }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ grade.horario }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-for="(turma, index) in grade.turmas" :key="turma.id">
                                                {{ turma.nome }}<span v-if="index < grade.turmas.length - 1">, </span>
                                            </span>
                                            <span v-if="grade.turmas.length === 0">Nenhuma</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('grade_horarios.edit', grade.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</Link>
                                            </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>