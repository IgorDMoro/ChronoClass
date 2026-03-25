<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const logoUrl = '/imgs/UniFil_Logo Isolado Horizontal-01.png';
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="flex flex-col items-center justify-center">
            <div class="mb-8">
                <Link href="/">
                    <img :src="logoUrl" alt="UniFil Logo" class="w-48 h-auto" />
                </Link>
            </div>

            <p class="mb-6 text-center text-lg text-gray-400">
                Faça seu login com a conta do Google
            </p>

            <div class="w-full text-center">
                <a :href="route('google.login')"
                   class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    
                    <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#fbc02d" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        <path fill="#e53935" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                        <path fill="#4caf50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                        <path fill="#1565c0" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571l6.19,5.238C44.572,36.348,48,30.655,48,24C48,22.659,47.862,21.35,47.611,20.083z"></path>
                    </svg>
                    
                    <span>Entrar com Google</span>
                </a>
            </div>
        </div>
    </GuestLayout>
</template>