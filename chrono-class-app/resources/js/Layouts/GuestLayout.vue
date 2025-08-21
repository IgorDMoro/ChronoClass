<script setup>
// O ApplicationLogo e o Link não são mais necessários aqui,
// pois a própria página de Login.vue agora controla a exibição da logo.
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[radial-gradient(ellipse_at_top_center,_var(--tw-gradient-stops))] from-zinc-800 via-neutral-800 to-black pt-6 sm:justify-center sm:pt-0"
    >
<h3 class="text-2xl font-bold text-gray-300 mb-4 sm:mb-9">Bem Vindo ao ChronoClass!</h3>

        <div
            class="w-full overflow-hidden rounded-lg bg-zinc-900/60 px-6 py-8 shadow-2xl backdrop-blur-sm border border-orange-500/30 sm:max-w-md"
            
        >
        
            <slot />
            
        </div>
    </div>
</template>