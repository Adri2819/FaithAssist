<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
  links: { type: Array, required: true },
  from: { type: Number, default: null },
  to: { type: Number, default: null },
  total: { type: Number, default: 0 },
});
</script>

<template>
  <div
    v-if="total > 0"
    class="mt-4 flex flex-col items-center gap-3 sm:flex-row sm:justify-between text-sm"
  >
    <span class="ui-help">
      <template v-if="from && to">
        Mostrando {{ from }}–{{ to }} de {{ total }} registros
      </template>
      <template v-else> {{ total }} registro{{ total !== 1 ? 's' : '' }} </template>
    </span>

    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
      <template v-for="(link, index) in links" :key="link.label">
        <!-- Previous -->
        <Link
          v-if="index === 0"
          :href="link.url ?? ''"
          class="ui-icon-btn ui-icon-btn-sm"
          :class="{ 'pointer-events-none cursor-not-allowed opacity-40': !link.url }"
          aria-label="Página anterior"
        >
          <ChevronLeft class="h-4 w-4" />
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
          class="ui-icon-btn ui-icon-btn-sm"
          :class="{ 'pointer-events-none cursor-not-allowed opacity-40': !link.url }"
          aria-label="Página siguiente"
        >
          <ChevronRight class="h-4 w-4" />
        </Link>
      </template>
    </div>
  </div>
</template>
