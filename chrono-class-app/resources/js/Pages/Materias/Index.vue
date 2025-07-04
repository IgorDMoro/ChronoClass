<template>
    <Head title="Matérias" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matérias
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Lista de Matérias
                            </h3>
                            <Link
                                :href="route('materias.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Nova Matéria
                            </Link>
                        </div>

                        <div
                            v-if="$page.props.flash.success"
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert"
                        >
                            <span class="block sm:inline">{{
                                $page.props.flash.success
                            }}</span>
                        </div>
                        <div
                            v-if="$page.props.flash.error"
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert"
                        >
                            <span class="block sm:inline">{{
                                $page.props.flash.error
                            }}</span>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Código
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Nome
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Carga Horária
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Modalidade
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Comp. Tipo
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Ensw. Tipo
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        <span>Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="materia in materias"
                                    :key="materia.id"
                                >
                                    <td class="px-6 py-4">
                                        {{ materia.codigo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ materia.nome }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ materia.carga_horaria }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ materia.modalidade }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ materia.comp_tipo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ materia.ensw_tipo }}
                                    </td>
                                    <td
                                        class="px-6 py-10 text-right text-sm font-medium flex items-center justify-end"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'materias.edit',
                                                    materia.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >Editar</Link
                                        >
                                        <button
                                            @click="deleteMateria(materia.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">
                            Importar Matérias da Planilha
                        </h3>
                        <form
                            @submit.prevent="submitImport"
                            enctype="multipart/form-data"
                            class="flex flex-col space-y-4"
                        >
                            <div>
                                <label
                                    for="fileInput"
                                    class="block text-sm font-medium text-gray-700"
                                    >Selecione o arquivo Excel/CSV:</label
                                >
                                <input
                                    type="file"
                                    @change="handleFileUpload"
                                    id="fileInput"
                                    required
                                    class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                />
                                <div
                                    v-if="importForm.errors.file"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ importForm.errors.file }}
                                </div>
                            </div>
                            <button
                                type="submit"
                                :disabled="importForm.processing"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Importar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    materias: {
        type: Array,
        required: true,
    },
});

const importForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    importForm.file = event.target.files[0];
};

const submitImport = () => {
    importForm.post(route("materias.import"), {
        onSuccess: () => {
            importForm.reset("file"); // Limpa o campo 'file' do formulário
            document.getElementById("fileInput").value = ""; // Limpa o elemento input do DOM
        },
        onError: (errors) => {
            console.error("Erro na importação:", errors);
        },
    });
};

const deleteMateria = (id) => {
    if (confirm("Tem certeza que deseja excluir esta matéria?")) {
        // Crie um novo formulário useForm para a requisição DELETE
        const deleteForm = useForm({
            _method: "delete", // ESTA LINHA É CRÍTICA: Simula o método DELETE
        });

        // ESTA LINHA É CRÍTICA: Chame .post() e aponte para a rota materias.destroy
        deleteForm.post(route("materias.destroy", id), {
            onSuccess: () => {
                // A página será recarregada com a lista atualizada e a flash message
            },
            onError: (errors) => {
                alert(
                    "Erro ao excluir matéria: " +
                        (errors.message || "Verifique o console.")
                );
                console.error(errors);
            },
        });
    }
};
</script>
