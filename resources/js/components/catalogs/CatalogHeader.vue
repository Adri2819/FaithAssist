<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, LayoutGrid } from 'lucide-vue-next';

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  backHref: { type: String, default: '/' },
  count: { type: Number, default: null },
  icon: { type: [Object, Function], default: () => LayoutGrid },
});
</script>

<template>
  <div class="ui-page-header">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <Link :href="backHref" class="ui-icon-btn" aria-label="Regresar">
          <ChevronLeft class="h-4 w-4" />
        </Link>

        <div>
          <div class="flex items-center gap-2.5">
            <span class="ui-header-icon">
              <component :is="icon" class="h-4 w-4" />
            </span>

            <h1 class="ui-section-title font-black">
              {{ title }}
            </h1>

            <span v-if="count !== null" class="ui-badge">
              {{ count }}
            </span>
          </div>

          <p v-if="subtitle" class="ui-section-subtitle mt-1">
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
