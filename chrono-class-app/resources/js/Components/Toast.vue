<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success');
let timer = null;

const flash = computed(() => page.props.flash);

watch(flash, (val) => {
    if (val?.success) {
        message.value = val.success;
        type.value = 'success';
        showToast();
    } else if (val?.error) {
        message.value = val.error;
        type.value = 'error';
        showToast();
    }
}, { immediate: true, deep: true });

function showToast() {
    show.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => { show.value = false; }, 4000);
}

function close() {
    show.value = false;
    clearTimeout(timer);
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div v-if="show" class="fixed bottom-6 right-6 z-50 max-w-sm w-full pointer-events-auto">
            <div
                class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
                :class="{
                    'bg-green-600 text-white': type === 'success',
                    'bg-red-600 text-white': type === 'error',
                }"
            >
                <svg v-if="type === 'success'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ message }}</span>
                <button @click="close" class="ml-auto p-0.5 rounded hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
