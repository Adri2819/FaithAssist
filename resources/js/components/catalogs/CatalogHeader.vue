<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  backHref: { type: String, default: '/' },
  count: { type: Number, default: null },
});
</script>

<template>
  <div class="mb-6 rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-slate-700 dark:bg-slate-900/70 sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
      <Link
        :href="backHref"
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        aria-label="Regresar"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </Link>

      <div>
        <div class="flex items-center gap-2.5">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5z" />
              <path d="M8 3v4" />
              <path d="M16 3v4" />
              <path d="M3 10h18" />
            </svg>
          </span>

          <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-slate-100">
            {{ title }}
          </h1>

          <span
            v-if="count !== null"
            class="inline-flex items-center rounded-full border border-sky-200 bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700 dark:border-sky-800 dark:bg-sky-900/40 dark:text-sky-300"
          >
            {{ count }}
          </span>
        </div>

        <p v-if="subtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          {{ subtitle }}
        </p>
      </div>
    </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
