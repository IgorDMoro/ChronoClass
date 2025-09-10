<script setup>
import Modal from './Modal.vue';
import SecondaryButton from './SecondaryButton.vue';
import DangerButton from './DangerButton.vue';

const emit = defineEmits(['close', 'confirm']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
});

const closeModal = () => {
    emit('close');
};

const confirm = () => {
    emit('confirm');
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ title }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ message }}
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>

                <DangerButton class="ms-3" @click="confirm">
                    Confirmar Exclusão
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
