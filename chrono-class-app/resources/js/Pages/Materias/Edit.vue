<template>
    <Head title="Editar Matéria" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Matéria</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm">
                            <div class="mb-4">
                                <label for="codigo" class="block text-sm font-medium text-gray-700">Código:</label>
                                <input type="text" v-model="form.codigo" id="codigo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div v-if="form.errors.codigo" class="text-red-600 text-sm mt-1">{{ form.errors.codigo }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome:</label>
                                <input type="text" v-model="form.nome" id="nome" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div v-if="form.errors.nome" class="text-red-600 text-sm mt-1">{{ form.errors.nome }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="carga_horaria" class="block text-sm font-medium text-gray-700">Carga Horária:</label>
                                <input type="number" v-model="form.carga_horaria" id="carga_horaria" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div v-if="form.errors.carga_horaria" class="text-red-600 text-sm mt-1">{{ form.errors.carga_horaria }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="modalidade" class="block text-sm font-medium text-gray-700">Modalidade:</label>
                                <select v-model="form.modalidade" id="modalidade" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Presencial">Presencial</option>
                                    <option value="UCD">UCD</option>
                                </select>
                                <div v-if="form.errors.modalidade" class="text-red-600 text-sm mt-1">{{ form.errors.modalidade }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="comp_tipo" class="block text-sm font-medium text-gray-700">Comp. Tipo (Ciência da Computação):</label>
                                <select v-model="form.comp_tipo" id="comp_tipo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Core">Core</option>
                                    <option value="Flex">Flex</option>
                                </select>
                                <div v-if="form.errors.comp_tipo" class="text-red-600 text-sm mt-1">{{ form.errors.comp_tipo }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="ensw_tipo" class="block text-sm font-medium text-gray-700">Ensw. Tipo (Engenharia de Software):</label>
                                <select v-model="form.ensw_tipo" id="ensw_tipo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Core">Core</option>
                                    <option value="Flex">Flex</option>
                                </select>
                                <div v-if="form.errors.ensw_tipo" class="text-red-600 text-sm mt-1">{{ form.errors.ensw_tipo }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('materias.index')" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</Link>
                                <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Atualizar Matéria
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    materia: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    _method: 'put', // ESTA LINHA É CRÍTICA: Simula o método PUT
    codigo: props.materia.codigo,
    nome: props.materia.nome,
    carga_horaria: props.materia.carga_horaria,
    modalidade: props.materia.modalidade,
    comp_tipo: props.materia.comp_tipo,
    ensw_tipo: props.materia.ensw_tipo,
});

const submitForm = () => {
    // ESTA LINHA É CRÍTICA: Chame .post() e aponte para a rota materias.update
    form.post(route('materias.update', props.materia.id), {
        onSuccess: () => {
            // O Inertia automaticamente redirecionará para o index com a flash message.
            // Nada precisa ser feito aqui, a menos que você queira alguma lógica específica.
        },
        onError: (errors) => {
            // Erros de validação serão preenchidos automaticamente no `form.errors`
            console.error("Erro na atualização:", errors);
        },
    });
};
</script>