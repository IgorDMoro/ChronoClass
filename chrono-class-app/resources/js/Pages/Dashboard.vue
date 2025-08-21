<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="bg-zinc-800 overflow-hidden shadow-2xl shadow-black/25 sm:rounded-lg ring-1 ring-inset ring-orange-500/20"
                >
                    <div class="p-6 text-gray-200 text-center">
                        Bem vindo ao ChronoClass! Você está logado.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
