<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: { type: Array, required: true },
  from: { type: Number, default: null },
  to: { type: Number, default: null },
  total: { type: Number, default: 0 },
});
</script>

<template>
  <div v-if="total > 0" class="mt-4 flex flex-col items-center gap-3 sm:flex-row sm:justify-between text-sm">
    <span class="text-slate-500 dark:text-slate-400">
      <template v-if="from && to">
        Mostrando {{ from }}–{{ to }} de {{ total }} registros
      </template>
      <template v-else>
        {{ total }} registro{{ total !== 1 ? 's' : '' }}
      </template>
    </span>

    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
      <template v-for="(link, index) in links" :key="link.label">
        <!-- Previous -->
        <Link
          v-if="index === 0"
          :href="link.url ?? ''"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
          :class="{ 'pointer-events-none cursor-not-allowed opacity-40': !link.url }"
          aria-label="Página anterior"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </Link>

        <!-- Page numbers -->
        <Link
          v-else-if="index !== links.length - 1"
          :href="link.url ?? ''"
          class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm transition"
          :class="[
            link.active
              ? 'bg-sky-600 font-semibold text-white shadow-sm'
              : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800',
            !link.url ? 'pointer-events-none cursor-not-allowed opacity-40' : '',
          ]"
          v-html="link.label"
        />

        <!-- Next -->
        <Link
          v-else
          :href="link.url ?? ''"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800"
          :class="{ 'pointer-events-none cursor-not-allowed opacity-40': !link.url }"
          aria-label="Página siguiente"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6" />
          </svg>
        </Link>
      </template>
    </div>
  </div>
</template>
