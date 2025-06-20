<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: [String, Number, Array], // Pode ser string, number (para single select) ou Array (para multiple select)
    options: {
        type: Array,
        required: true,
        // Cada item no array de options deve ter { value: any, label: string }
    },
    id: {
        type: String,
        default: () => Math.random().toString(36).substring(2, 9), // Gera um ID único se não for fornecido
    },
    name: String,
    required: Boolean,
    multiple: Boolean, // Propriedade para ativar seleção múltipla
    disabled: Boolean,
    autofocus: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const select = ref(null);

// Usa um computed property para v-model, lidando com single e multiple selection
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    },
});

onMounted(() => {
    if (props.autofocus) {
        select.value.focus();
    }
});

// Função para focar o input (útil para acessibilidade/testes)
const focus = () => select.value.focus();

defineExpose({ focus });
</script>

<template>
    <select
        :id="id"
        :name="name"
        :required="required"
        :multiple="multiple"
        :disabled="disabled"
        v-model="proxyValue"
        ref="select"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    >
        <option v-if="!multiple && !modelValue" value="" disabled selected>Selecione uma opção</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>