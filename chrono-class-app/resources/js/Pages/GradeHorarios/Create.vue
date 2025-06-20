<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Precisaremos criar este componente ou usar um input padrão
import Checkbox from '@/Components/Checkbox.vue'; // Para seleção de turmas
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    materias: Array,
    professores: Array,
    salas: Array,
    turmas: Array, // Turmas para seleção (inclui 'num_alunos' agora)
    diasDaSemana: Array,
    horariosDeAula: Array,
});

const form = useForm({
    materia_id: '',
    professor_id: '',
    sala_id: '',
    dia_semana: '',
    horario: '',
    turmas_ids: [], // Array para IDs das turmas selecionadas
});

const submit = () => {
    form.post(route('grade_horarios.store'));
};
</script>

<template>
    <Head title="Criar Grade Horária" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Criar Nova Grade Horária</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel for="materia_id" value="Matéria" />
                                <SelectInput
                                    id="materia_id"
                                    class="mt-1 block w-full"
                                    v-model="form.materia_id"
                                    :options="materias.map(m => ({ value: m.id, label: m.nome }))"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.materia_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="professor_id" value="Professor" />
                                <SelectInput
                                    id="professor_id"
                                    class="mt-1 block w-full"
                                    v-model="form.professor_id"
                                    :options="professores.map(p => ({ value: p.id, label: p.nome }))"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.professor_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="sala_id" value="Sala" />
                                <SelectInput
                                    id="sala_id"
                                    class="mt-1 block w-full"
                                    v-model="form.sala_id"
                                    :options="salas.map(s => ({ value: s.id, label: s.nome + ' (Cap: ' + s.capacidade + ')' }))"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.sala_id" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="dia_semana" value="Dia da Semana" />
                                <SelectInput
                                    id="dia_semana"
                                    class="mt-1 block w-full"
                                    v-model="form.dia_semana"
                                    :options="diasDaSemana.map(d => ({ value: d, label: d.charAt(0).toUpperCase() + d.slice(1) }))"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.dia_semana" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="horario" value="Horário" />
                                <SelectInput
                                    id="horario"
                                    class="mt-1 block w-full"
                                    v-model="form.horario"
                                    :options="horariosDeAula.map(h => ({ value: h, label: h }))"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.horario" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="turmas_ids" value="Turmas" />
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-1">
                                    <label v-for="turma in turmas" :key="turma.id" class="flex items-center">
                                        <Checkbox
                                            :value="turma.id"
                                            v-model:checked="form.turmas_ids"
                                        />
                                        <span class="ms-2 text-sm text-gray-600">{{ turma.nome }} ({{ turma.curso }} - Sem {{ turma.semestre }} - {{ turma.num_alunos }} alunos)</span>
                                    </label>
                                </div>
                                <InputError class="mt-2" :message="form.errors.turmas_ids" />
                                <InputError v-if="form.errors['turmas_ids.0']" class="mt-2" :message="form.errors['turmas_ids.0']" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Salvar Grade Horária
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>