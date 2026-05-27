<script setup>
import { Head } from '@inertiajs/vue3';
import { BookOpen, Building2, Home, Landmark, MapPinned, Users } from 'lucide-vue-next';
import AppShell from '../components/layouts/AppShell.vue';

const modules = [
  {
    name: 'Regiones',
    accent: 'from-slate-400 via-slate-300 to-slate-200',
    titleClass: 'text-sky-700',
    items: [
      { label: 'Estados', icon: MapPinned },
      { label: 'Municipios', icon: Building2 },
      { label: 'Comunidades', icon: Users },
    ],
  },
  {
    name: 'Eclesiásticos',
    accent: 'from-blue-200 via-sky-100 to-slate-100',
    titleClass: 'text-sky-700',
    items: [
      { label: 'Diócesis', icon: Landmark },
      { label: 'Decanatos', icon: BookOpen },
      { label: 'Parroquias', icon: Home },
      { label: 'Iglesias', icon: Landmark },
    ],
  },
];
</script>

<template>
  <Head title="Inicio" />

  <AppShell>
    <div class="mx-auto grid w-full max-w-7xl gap-6 xl:grid-cols-2">
      <article
        v-for="module in modules"
        :key="module.name"
        class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_16px_50px_-30px_rgba(15,23,42,0.35)] transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 dark:shadow-[0_16px_50px_-30px_rgba(0,0,0,0.6)]"
      >
        <header class="border-b border-slate-200 px-6 py-5 sm:px-7" :class="module.accent">
          <h2
            class="text-2xl font-black tracking-tight dark:text-slate-50"
            :class="module.titleClass"
          >
            {{ module.name }}
          </h2>
        </header>

        <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-4">
          <button
            v-for="item in module.items"
            :key="item.label"
            type="button"
            class="group flex min-h-28 flex-col items-center justify-center rounded-[1.25rem] border border-slate-200 bg-slate-50 px-3 py-4 text-center transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:bg-white hover:shadow-lg dark:border-slate-800 dark:bg-slate-950 dark:hover:border-slate-700 dark:hover:bg-slate-800"
          >
            <span
              class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition group-hover:bg-sky-100 group-hover:text-sky-700 group-hover:ring-sky-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:group-hover:bg-sky-900/30 dark:group-hover:text-sky-200 dark:group-hover:ring-sky-800"
            >
              <component :is="item.icon" class="h-5 w-5" />
            </span>

            <span
              class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200"
            >
              {{ item.label }}
            </span>
          </button>
        </div>
      </article>
    </div>
  </AppShell>
</template>
