<script setup>
import { ref, onMounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Toast from "@/Components/Toast.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, usePage } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);

// Apenas para debug no console (pode apagar depois se quiser)
const userRole = usePage().props.auth.user?.role;
console.log("Cargo recebido pelo Vue:", userRole); 

// Lógica para o seletor de tema (claro/escuro)
const isDarkMode = ref(false);

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  if (isDarkMode.value) {
    document.documentElement.classList.add("dark");
    localStorage.setItem("theme", "dark");
  } else {
    document.documentElement.classList.remove("dark");
    localStorage.setItem("theme", "light");
  }
};

onMounted(() => {
  if (
    localStorage.getItem("theme") === "dark" ||
    (!("theme" in localStorage) &&
      window.matchMedia("(prefers-color-scheme: dark)").matches)
  ) {
    isDarkMode.value = true;
    document.documentElement.classList.add("dark");
  } else {
    isDarkMode.value = false;
    document.documentElement.classList.remove("dark");
  }
});
</script>

<template>
  <div>
    <Toast />
    <div
      class="min-h-screen bg-gray-100 dark:bg-[radial-gradient(ellipse_at_top_center,_var(--tw-gradient-stops))] dark:from-zinc-800 dark:via-neutral-800 dark:to-black"
    >
      <nav class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-orange-500/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')">
                  <img
                    src="../../../public/imgs/UniFil_Logo Isolado Horizontal-01.png"
                    alt="UniFil Logo"
                    class="header-logo"
                  />
                </Link>
              </div>

              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')"> Dashboard </NavLink>
                
                <NavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('salas.index')" :active="route().current('salas.index')"> Salas </NavLink>
                <NavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('professores.index')" :active="route().current('professores.index')"> Professores </NavLink>
                <NavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('materias.index')" :active="route().current('materias.index')"> Unidades Curriculares </NavLink>
                <NavLink :href="route('grades.index')" :active="route().current('grades.index')"> Grades </NavLink>
                <NavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('turmas.index')" :active="route().current('turmas.index')"> Turmas </NavLink>
              </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <button @click="toggleTheme" class="mr-4 p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-offset-black transition-colors duration-200">
                <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>

              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-black focus:ring-orange-500">
                        {{ $page.props.auth.user.name }}
                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </span>
                  </template>
                  
                  <template #content>
                    <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
              <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-neutral-800 hover:text-gray-700 dark:hover:text-gray-200 focus:bg-gray-100 dark:focus:bg-neutral-800 focus:outline-none">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown, }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                  <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown, }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown, }" class="sm:hidden">
          <div class="space-y-1 px-2 pb-3 pt-2">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')"> Dashboard </ResponsiveNavLink>
            <ResponsiveNavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('salas.index')" :active="route().current('salas.index')"> Salas </ResponsiveNavLink>
            <ResponsiveNavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('professores.index')" :active="route().current('professores.index')"> Professores </ResponsiveNavLink>
            <ResponsiveNavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('materias.index')" :active="route().current('materias.index')"> Unidades Curriculares </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('grades.index')" :active="route().current('grades.index')"> Grades </ResponsiveNavLink>
            <ResponsiveNavLink v-if="$page.props.auth.user?.role !== 'aluno'" :href="route('turmas.index')" :active="route().current('turmas.index')"> Turmas </ResponsiveNavLink>
          </div>
        </div>
      </nav>

      <main>
        <slot />
      </main>

      <a href="https://unifil.br/" target="_blank" rel="noopener noreferrer" class="bg-neutral-100 backdrop-blur-sm fixed bottom-4 left-4 z-50 opacity-40 flex items-end justify-end justify-content-end rounded-xl hover:opacity-100 transition-opacity duration-300"> 
        <img src="../../../public/imgs/UniFil_Logo_Verical-01.png" alt="UniFil Logo" class="block h-full w-auto"/>
      </a>
    </div>
  </div>
</template>