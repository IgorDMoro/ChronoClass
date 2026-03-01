<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    grade: Object,
    existingHorarios: Array,
    materias_presenciais: Array,
    materias_ucd: Array,
    professores: Array,
    salas: Array,
});

// --- Estado do Componente ---
const form = useForm({
    nome: props.grade?.nome || '',
    description: props.grade?.description || '',
    curso: props.grade?.curso || [],
    semestre: props.grade?.semestre || '',
    horarios: [],
});

const showSabado = ref(false);
const showUcd = ref(false);
const conflictWarnings = ref({});
const filteredProfessores = ref({});
const loadingProfessores = ref({});
const editingSlots = ref(new Set());
const materiaNames = ref({});
const professorNames = ref({});
const salaInfo = ref({});

const diasDaSemana = ['SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA'];
const horariosBlocos = ['19:00-20:30', '20:45-22:15'];
const horarioSabado = '08:00-12:00';
const cursoOptions = ['Engenharia de Software', 'Ciências da Computação'];

// --- Estrutura de Dados Visual ---
const gradeVisual = ref(diasDaSemana.reduce((acc, dia) => {
    acc[dia] = horariosBlocos.map(bloco => ({
        dia_semana: dia,
        horario_bloco: bloco,
        slots: [{
            id: Date.now() + Math.random(),
            type: null,
            confirmed: false,
            aula: { professor_id: '', materia_id: '', sala: '', classroom_code: '' }, 
            flex_aulas: [],
        }],
    }));
    return acc;
}, {}));

const gradeSabado = ref({ estagio: false });
const gradeUcd = ref([]);

// --- Carrega dados existentes ---
const loadExistingGrade = () => {
    if (!props.existingHorarios || props.existingHorarios.length === 0) {
        return;
    }

    // Agrupa horários por dia da semana e horário de bloco
    const horariosGrouped = {};
    props.existingHorarios.forEach(horario => {
        const key = `${horario.dia_semana}-${horario.horario_bloco}`;
        if (!horariosGrouped[key]) horariosGrouped[key] = [];
        horariosGrouped[key].push(horario);
    });

    // Popula a grade visual
    Object.values(gradeVisual.value).forEach(diaDeAulas => {
        diaDeAulas.forEach((celula, celulaIndex) => {
            const key = `${celula.dia_semana}-${celula.horario_bloco}`;
            const horariosCelula = horariosGrouped[key] || [];

            if (horariosCelula.length > 0) {
                celula.slots = horariosCelula.map(horario => {
                    const materia = props.materias_presenciais?.find(m => m.id === horario.materia_id);
                    const professor = props.professores?.find(p => p.id === horario.professor_id);
                    const sala = props.salas?.find(s => s.nome === horario.sala);

                    const slot = {
                        id: horario.id,
                        type: materia?.curso || 'Ambos (Core)',
                        confirmed: true,
                        aula: {
                            professor_id: horario.professor_id,
                            materia_id: horario.materia_id,
                            sala: horario.sala,
                            classroom_code: horario.classroom_code || '',
                        },
                        flex_aulas: [],
                        original_id: horario.id,
                    };

                    materiaNames.value[slot.id] = materia?.nome || '';
                    professorNames.value[slot.id] = professor?.nome || '';
                    salaInfo.value[slot.id] = sala || { nome: horario.sala };

                    return slot;
                });

                // Remove o slot vazio inicial se houver slots carregados
                if (celula.slots.length > 0 && !celula.slots[0].aula.materia_id) {
                    celula.slots = celula.slots.filter(s => s.aula.materia_id);
                }
            }
        });
    });
};

// --- Lógica de Filtros e Verificações ---
const materiasCore = computed(() => props.materias_presenciais);
const materiasFlex = computed(() => props.materias_presenciais);

const canSubmit = computed(() => {
    return form.nome && form.semestre && form.curso.length > 0;
});

const getProfessoresParaMateria = async (materiaId, slotId) => {
    if (!materiaId) {
        filteredProfessores.value[slotId] = [];
        return;
    }

    const key = `slot-${slotId}`;
    loadingProfessores.value[key] = true;

    try {
        const response = await fetch(`/grades/api/professores-por-materia/${materiaId}`);
        const data = await response.json();
        filteredProfessores.value[slotId] = data;
    } catch (error) {
        console.error('Erro ao buscar professores:', error);
        filteredProfessores.value[slotId] = [];
    } finally {
        loadingProfessores.value[key] = false;
    }
};

const checkForConflict = (aula, celula, slotId) => {
    const key = `${celula.dia_semana}-${celula.horario_bloco}-${slotId}`;
    if (!aula.professor_id) {
        delete conflictWarnings.value[key];
        return;
    }
    const conflict = props.existingHorarios.find(h => 
        h.dia_semana === celula.dia_semana &&
        h.horario_bloco === celula.horario_bloco &&
        h.professor_id === aula.professor_id &&
        h.id !== slotId
    );

    if (conflict) {
        conflictWarnings.value[key] = `Conflito: Prof. já alocado.`;
    } else {
        delete conflictWarnings.value[key];
    }
};

// --- Funções de Manipulação da Grade ---
const addSlot = (celula) => {
    if (celula.slots.length < 2) {
        const newId = Date.now() + Math.random();
        celula.slots.push({
            id: newId,
            type: null,
            confirmed: false,
            aula: { professor_id: '', materia_id: '', sala: '', classroom_code: '' },
            flex_aulas: [],
        });
        editingSlots.value.add(newId);
    }
};

const removeSlot = (celula, slotIndex) => celula.slots.splice(slotIndex, 1);
const setSlotType = (slot, type) => slot.type = type;

const confirmSlot = (slot) => {
    if (slot.aula.materia_id && slot.aula.professor_id && slot.aula.sala) {
        slot.confirmed = true;
        editingSlots.value.delete(slot.id);
        materiaNames.value[slot.id] = props.materias_presenciais.find(m => m.id == slot.aula.materia_id)?.nome || '';
        professorNames.value[slot.id] = props.professores.find(p => p.id == slot.aula.professor_id)?.nome || '';
        salaInfo.value[slot.id] = props.salas.find(s => s.nome === slot.aula.sala) || { nome: slot.aula.sala };
    }
};

const editSlot = (slot) => {
    slot.confirmed = false;
    editingSlots.value.add(slot.id);
};

const addFlexAula = (slot) => slot.flex_aulas.push({ id: Date.now(), professor_id: '', materia_id: '', sala: '', classroom_code: '' });
const removeFlexAula = (slot, flexIndex) => slot.flex_aulas.splice(flexIndex, 1);

const addUcd = () => gradeUcd.value.push({ dia_semana: 'ATIVIDADE_DIGITAL', horario_bloco: 'N/A', materia_id: '', professor_id: '', sala: '', classroom_code: '' });
const removeUcd = (index) => gradeUcd.value.splice(index, 1);

// --- Submissão do Formulário ---
const submit = () => {
    if (!form.nome || form.nome.trim() === '') {
        alert('Preencha o Nome da Grade!');
        return;
    }
    
    if (!form.semestre || form.semestre.trim() === '') {
        alert('Preencha o Semestre!');
        return;
    }
    
    // Garantir que curso é um array
    const cursoArray = Array.isArray(form.curso) ? form.curso : [];
    if (cursoArray.length === 0) {
        alert('Selecione pelo menos um curso!');
        return;
    }

    const horariosPreenchidos = [];
    Object.values(gradeVisual.value).forEach(diaDeAulas => {
        diaDeAulas.forEach(celula => {
            const { slots, ...celulaBase } = celula;
            celula.slots.forEach(slot => {
                // Apenas coletar slots que foram confirmados
                if (!slot.confirmed) return;
                
                const aulaBase = {
                    dia_semana: celulaBase.dia_semana,
                    horario_bloco: celulaBase.horario_bloco,
                };
                if (slot.type === 'Flex') {
                    slot.flex_aulas.forEach(flexAula => {
                        if (flexAula.materia_id && flexAula.professor_id) horariosPreenchidos.push({ ...aulaBase, ...flexAula });
                    });
                } else if (slot.type && slot.aula.materia_id && slot.aula.professor_id) {
                    horariosPreenchidos.push({ ...aulaBase, ...slot.aula });
                }
            });
        });
    });

    if (showSabado.value && gradeSabado.value.estagio) {
        const materiaEstagio = props.materias_presenciais.find(m => m.nome.toLowerCase().includes('estágio'));
        if (materiaEstagio) {
            const primeiroProfessorId = props.professores[0]?.id || null;
            horariosPreenchidos.push({ dia_semana: 'SABADO', horario_bloco: horarioSabado, materia_id: materiaEstagio.id, professor_id: primeiroProfessorId, sala: null, classroom_code: 'N/A' });
        }
    }

    if (showUcd.value) {
        gradeUcd.value.forEach(aula => {
            if (aula.materia_id && aula.professor_id) horariosPreenchidos.push(aula);
        });
    }
    
    // Validar que pelo menos 1 horário foi confirmado
    if (horariosPreenchidos.length === 0) {
        alert('Confirme pelo menos 1 aula antes de salvar!');
        return;
    }
    
    // Preparar dados para envio
    form.horarios = horariosPreenchidos;
    
    console.log('Enviando para backend:', {
        nome: form.nome,
        description: form.description,
        semestre: form.semestre,
        curso: cursoArray,
        horarios: horariosPreenchidos,
        total_horarios: horariosPreenchidos.length,
    });
    
    form.put(route('grades.update', props.grade.id), {
        onError: (errors) => {
            console.error('Erros ao atualizar grade:', errors);
        },
    });
};

onMounted(() => {
    loadExistingGrade();
});
</script>

<template>
    <Head :title="`Editar Grade: ${grade.nome}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Grade de Horários: {{ grade.nome }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="p-6 border rounded-lg mb-8 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                                    <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <TextInput v-model="form.nome" placeholder="Nome da Grade *" class="md:col-span-2" required />
                                        <TextInput v-model="form.semestre" placeholder="Semestre (Ex: 2025/1) *" required />
                                    </div>
                                    <div class="md:col-span-2">
                                        <InputLabel>Curso(s) <span class="text-red-500">*</span></InputLabel>
                                        <div class="mt-2 flex flex-wrap gap-4">
                                            <label v-for="option in cursoOptions" :key="option" class="flex items-center">
                                                <input type="checkbox" v-model="form.curso" :value="option" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                <span class="ml-2 text-sm">{{ option }}</span>
                                            </label>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.curso" />
                                    </div>
                                    <div class="md:col-span-1">
                                        <InputLabel for="grade_description">Descrição (Opcional)</InputLabel>
                                        <TextInput id="grade_description" type="text" v-model="form.description" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed border-collapse text-xs">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border p-2 font-semibold text-gray-600 w-[10%]">Horário</th>
                                            <th v-for="dia in diasDaSemana" :key="dia" class="border p-2 font-semibold text-gray-600 uppercase w-[18%]">{{ dia }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <tr v-for="(bloco, hIndex) in horariosBlocos" :key="hIndex">
                                            <td class="border p-2 font-semibold text-gray-700 bg-gray-50 text-center align-middle">{{ bloco.replace('-', ' - ') }}</td>
                                            <td v-for="dia in diasDaSemana" :key="dia + hIndex" class="border p-1 align-top overflow-hidden">
                                                <div class="space-y-2 h-full flex flex-col min-w-0">
                                                    <div v-for="(slot, sIndex) in gradeVisual[dia][hIndex].slots" :key="slot.id" class="bg-gray-50 p-1.5 rounded-md border relative flex-1 flex flex-col min-w-0">
                                                        <button @click="removeSlot(gradeVisual[dia][hIndex], sIndex)" type="button" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full h-4 w-4 text-xs flex items-center justify-center font-bold">&times;</button>
                                                        
                                                        <!-- MODO VISUALIZAÇÃO (Confirmado) -->
                                                        <div v-if="slot.confirmed" class="space-y-2 h-full flex flex-col">
                                                            <div class="bg-white rounded-lg border border-gray-200 p-3 flex-1 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                                                                <div class="space-y-2">
                                                                    <div class="flex justify-between items-start gap-2">
                                                                        <div class="flex-1">
                                                                            <p class="text-[11px] font-semibold text-gray-500 uppercase">Matéria</p>
                                                                            <p class="text-sm font-bold text-gray-800">{{ materiaNames[slot.id] }}</p>
                                                                        </div>
                                                                        <button @click="editSlot(slot)" type="button" class="p-1.5 rounded hover:bg-gray-100 transition-colors" title="Editar">
                                                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[11px] font-semibold text-gray-500 uppercase">Professor</p>
                                                                        <p class="text-sm font-medium text-gray-700">{{ professorNames[slot.id] }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[11px] font-semibold text-gray-500 uppercase">Sala</p>
                                                                        <p class="text-sm font-medium text-gray-700">{{ salaInfo[slot.id]?.nome }}</p>
                                                                    </div>
                                                                    <div v-if="slot.aula.classroom_code" class="pt-2 border-t">
                                                                        <p class="text-[11px] font-semibold text-gray-500 uppercase">Classroom</p>
                                                                        <p class="text-sm font-mono text-blue-600">{{ slot.aula.classroom_code }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- MODO EDIÇÃO -->
                                                        <div v-else class="space-y-1.5">
                                                            <div v-if="!slot.type" class="flex flex-col items-center justify-center gap-1 py-1 h-full min-h-[6rem]">
                                                                <span class="text-xs font-medium text-gray-500">Tipo de aula:</span>
                                                                <div class="flex gap-1 flex-wrap justify-center">
                                                                    <button @click="setSlotType(slot, 'Engenharia de Software')" type="button" class="px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-800 rounded">Eng. SW</button>
                                                                    <button @click="setSlotType(slot, 'Ciências da Computação')" type="button" class="px-1.5 py-0.5 text-[10px] bg-green-100 text-green-800 rounded">C. Comp</button>
                                                                    <button @click="setSlotType(slot, 'Ambos (Core)')" type="button" class="px-1.5 py-0.5 text-[10px] bg-indigo-100 text-indigo-800 rounded">Ambos</button>
                                                                    <button @click="setSlotType(slot, 'Flex')" type="button" class="px-1.5 py-0.5 text-[10px] bg-yellow-100 text-yellow-800 rounded">Flex</button>
                                                                </div>
                                                            </div>

                                                            <div v-if="slot.type && slot.type !== 'Flex'" class="space-y-1.5 min-w-0">
                                                                <p class="text-[11px] font-bold text-center" :class="{'text-blue-800': slot.type.includes('Engenharia'), 'text-green-800': slot.type.includes('Ciências'), 'text-indigo-800': slot.type.includes('Ambos')}">{{ slot.type }}</p>
                                                                <select v-model="slot.aula.materia_id" @change="getProfessoresParaMateria(slot.aula.materia_id, slot.id)" class="w-full rounded-md border-gray-300 text-xs px-2 py-1">
                                                                    <option value="" disabled>Matéria</option>
                                                                    <option v-for="materia in materiasCore" :key="materia.id" :value="materia.id">{{ materia.nome }}</option>
                                                                </select>
                                                                <div v-if="loadingProfessores[`slot-${slot.id}`]" class="text-[10px] text-gray-500 italic">Carregando professores...</div>
                                                                <select v-model="slot.aula.professor_id" @change="checkForConflict(slot.aula, gradeVisual[dia][hIndex], slot.id)" :disabled="!slot.aula.materia_id || loadingProfessores[`slot-${slot.id}`]" class="w-full rounded-md border-gray-300 text-xs px-2 py-1 disabled:bg-gray-100">
                                                                    <option value="" disabled>Professor</option>
                                                                    <option v-for="prof in (filteredProfessores[slot.id] || [])" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                                                                </select>
                                                                
                                                                <select v-model="slot.aula.sala" class="w-full rounded-md border-gray-300 text-xs px-2 py-1">
                                                                    <option value="" disabled>Sala</option>
                                                                    <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">
                                                                        {{ sala.nome }} (Cap: {{ sala.capacidade }})
                                                                    </option>
                                                                </select>

                                                                <TextInput type="text" v-model="slot.aula.classroom_code" placeholder="Classroom (opcional)" class="text-xs w-full" />
                                                                <div v-if="conflictWarnings[`${gradeVisual[dia][hIndex].dia_semana}-${gradeVisual[dia][hIndex].horario_bloco}-${slot.id}`]" class="text-[10px] text-orange-600 p-1 bg-orange-100 rounded">
                                                                    {{ conflictWarnings[`${gradeVisual[dia][hIndex].dia_semana}-${gradeVisual[dia][hIndex].horario_bloco}-${slot.id}`] }}
                                                                </div>
                                                                <button 
                                                                    @click="confirmSlot(slot)" 
                                                                    type="button" 
                                                                    :disabled="!slot.aula.materia_id || !slot.aula.professor_id || !slot.aula.sala"
                                                                    class="w-full px-3 py-1.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white text-xs font-semibold rounded-md transition-colors"
                                                                >
                                                                    ✓ Confirmar
                                                                </button>
                                                            </div>

                                                            <div v-if="slot.type === 'Flex'" class="space-y-1.5">
                                                                <p class="text-[11px] font-bold text-yellow-800 text-center">Flex</p>
                                                                <div v-for="(flexAula, fIndex) in slot.flex_aulas" :key="flexAula.id" class="space-y-1.5 border-t pt-1.5">
                                                                    <div class="flex gap-1.5 items-start">
                                                                        <div class="flex-1 space-y-1.5">
                                                                            <select v-model="flexAula.materia_id" @change="getProfessoresParaMateria(flexAula.materia_id, `flex-${slot.id}-${fIndex}`)" class="w-full rounded-md border-gray-300 text-xs px-2 py-1">
                                                                                <option value="" disabled>Matéria</option>
                                                                                <option v-for="materia in materiasFlex" :key="materia.id" :value="materia.id">{{ materia.nome }}</option>
                                                                            </select>
                                                                            <div v-if="loadingProfessores[`slot-flex-${slot.id}-${fIndex}`]" class="text-[10px] text-gray-500 italic">Carregando professores...</div>
                                                                            <select v-model="flexAula.professor_id" :disabled="!flexAula.materia_id || loadingProfessores[`slot-flex-${slot.id}-${fIndex}`]" class="w-full rounded-md border-gray-300 text-xs px-2 py-1 disabled:bg-gray-100">
                                                                                <option value="" disabled>Professor</option>
                                                                                <option v-for="prof in (filteredProfessores[`flex-${slot.id}-${fIndex}`] || [])" :key="prof.id" :value="prof.id">{{ prof.nome }}</option>
                                                                            </select>
                                                                            
                                                                            <select v-model="flexAula.sala" class="w-full rounded-md border-gray-300 text-xs px-2 py-1">
                                                                                <option value="" disabled>Sala</option>
                                                                                <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">
                                                                                    {{ sala.nome }} (Cap: {{ sala.capacidade }})
                                                                                </option>
                                                                            </select>

                                                                            <TextInput type="text" v-model="flexAula.classroom_code" placeholder="Classroom" class="text-xs w-full" />
                                                                        </div>
                                                                        <button @click="removeFlexAula(slot, fIndex)" type="button" class="text-red-500 font-bold p-0.5 rounded-full hover:bg-red-100 mt-1">&times;</button>
                                                                    </div>
                                                                </div>
                                                                <button @click="addFlexAula(slot)" type="button" class="w-full text-center text-xs py-1 bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button v-if="gradeVisual[dia][hIndex].slots.length < 2" @click="addSlot(gradeVisual[dia][hIndex])" type="button" class="w-full text-center text-xs py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 mt-auto">+ Adicionar Aula</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex gap-4">
                                <button type="button" @click="showSabado = !showSabado" class="px-4 py-2 text-sm font-medium rounded-md transition-colors" :class="showSabado ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                                    {{ showSabado ? 'Ocultar' : 'Adicionar' }} Sábado
                                </button>
                                <button type="button" @click="showUcd = !showUcd" class="px-4 py-2 text-sm font-medium rounded-md transition-colors" :class="showUcd ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                                    {{ showUcd ? 'Ocultar' : 'Adicionar' }} Atividades Digitais
                                </button>
                            </div>

                            <div v-if="showSabado" class="mt-4 p-4 border rounded-lg bg-blue-50/50">
                                <InputLabel>Atividade de Sábado ({{ horarioSabado.replace('-', ' - ') }})</InputLabel>
                                <label class="relative inline-flex items-center cursor-pointer mt-2">
                                    <input type="checkbox" v-model="gradeSabado.estagio" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-300 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Estágio</span>
                                </label>
                            </div>

                            <div v-if="showUcd" class="mt-4 p-4 border rounded-lg bg-purple-50/50">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-semibold text-gray-800">Atividades Digitais (UCDs)</h3>
                                    <button @click="addUcd" type="button" class="px-3 py-1 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700">+</button>
                                </div>
                                <div class="space-y-4">
                                    <p v-if="gradeUcd.length === 0" class="text-sm text-gray-500 text-center">Nenhuma atividade digital adicionada.</p>
                                    <div v-for="(ucd, index) in gradeUcd" :key="index" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-3 border rounded-md bg-white">
                                        <div class="md:col-span-4">
                                            <InputLabel :for="'ucd_materia_'+index" class="text-xs mb-1">Matéria</InputLabel>
                                            <select :id="'ucd_materia_'+index" v-model="ucd.materia_id" @change="ucd.professor_id = ''" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                                <option value="" disabled>Selecione a UCD</option>
                                                <option v-for="materia in props.materias_ucd" :key="materia.id" :value="materia.id">{{ materia.nome }}</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <InputLabel :for="'ucd_prof_'+index" class="text-xs mb-1">Professor</InputLabel>
                                            <select :id="'ucd_prof_'+index" v-model="ucd.professor_id" :disabled="!ucd.materia_id" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-xs disabled:bg-gray-100">
                                                <option value="" disabled>Selecione o Professor</option>
                                                <option v-for="professor in props.professores.filter(p => p.materias.some(m => m.id === ucd.materia_id))" :key="professor.id" :value="professor.id">{{ professor.nome }}</option>
                                            </select>
                                        </div>
                                        
                                        <div class="md:col-span-2">
                                            <InputLabel :for="'ucd_sala_'+index" class="text-xs mb-1">Sala</InputLabel>
                                            <select :id="'ucd_sala_'+index" v-model="ucd.sala" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-xs">
                                                <option value="" disabled>Selecione a Sala</option>
                                                <option v-for="sala in props.salas" :key="sala.id" :value="sala.nome">
                                                    {{ sala.nome }} (Cap: {{ sala.capacidade }})
                                                </option>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <InputLabel :for="'ucd_code_'+index" class="text-xs mb-1">Classroom</InputLabel>
                                            <TextInput :id="'ucd_code_'+index" type="text" v-model="ucd.classroom_code" class="w-full text-xs" />
                                        </div>
                                        <div class="md:col-span-1 flex items-end justify-center">
                                            <button @click="removeUcd(index)" type="button" class="text-red-500 hover:text-red-700 font-bold p-1 rounded-full hover:bg-red-100">X</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                                <Link :href="route('grades.index')" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-neutral-700 dark:hover:border-neutral-700 focus:outline-none">
                                    Cancelar
                                </Link>
                                <PrimaryButton :disabled="form.processing || !canSubmit">Salvar Alterações</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
