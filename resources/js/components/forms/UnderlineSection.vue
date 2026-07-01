<script setup>
defineProps({
  title: { type: String, required: true },
});
</script>

<template>
  <section class="space-y-6">
    <div class="flex items-center gap-4">
      <h2
        class="shrink-0 text-xs font-black uppercase tracking-[0.42em] text-rose-800 dark:text-rose-300"
      >
        {{ title }}
      </h2>
      <span class="h-px flex-1 bg-slate-300 dark:bg-slate-700" />
    </div>

    <slot />
  </section>
</template>
