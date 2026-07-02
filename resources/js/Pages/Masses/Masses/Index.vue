<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Church, QrCode } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  masses: { type: Object, required: true },
  weekends: { type: Array, default: () => [] },
  churches: { type: Array, default: () => [] },
  chapels: { type: Array, default: () => [] },
  search: { type: String, default: '' },
  filters: { type: Object, default: () => ({ weekend_id: null }) },
});

const selectedWeekend = ref(props.filters.weekend_id);
let debounce = null;

watch(selectedWeekend, () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => {
    router.get(
      '/misas',
      { weekend_id: selectedWeekend.value || undefined, search: props.search || undefined },
      { preserveState: true, replace: true },
    );
  }, 250);
});

const weekendOptions = computed(() =>
  props.weekends.map((weekend) => ({
    value: weekend.id,
    label: `${weekend.name} · ${weekend.church}`,
  })),
);

const churchOptions = computed(() =>
  props.churches.map((church) => ({
    value: church.id,
    label: church.name,
  })),
);

const chapelOptions = computed(() => [
  { value: null, label: 'En parroquia' },
  ...props.chapels.map((chapel) => ({
    value: chapel.id,
    label: chapel.name,
  })),
]);

const statusOptions = [
  { value: 'upcoming', label: 'Próxima' },
  { value: 'in_progress', label: 'En curso' },
  { value: 'completed', label: 'Terminada' },
];

const columns = computed(() => [
  {
    key: 'weekend_id',
    label: 'Fin de semana',
    type: 'select',
    required: true,
    options: weekendOptions.value,
  },
  {
    key: 'church_id',
    label: 'Parroquia',
    type: 'select',
    required: true,
    options: churchOptions.value,
  },
  {
    key: 'chapel_id',
    label: 'Capilla',
    type: 'select',
    default: null,
    options: chapelOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'celebrated_at',
    label: 'Celebración',
    type: 'text',
    required: true,
    uppercase: false,
  },
  {
    key: 'status',
    label: 'Estatus',
    type: 'select',
    default: 'upcoming',
    options: statusOptions,
    badges: {
      upcoming: 'badge-info',
      in_progress: 'badge-warning',
      completed: 'badge-success',
    },
  },
  {
    key: 'attendance_status',
    label: 'Captura',
    type: 'select',
    default: 'upcoming',
    options: statusOptions,
    badges: {
      upcoming: 'badge-info',
      in_progress: 'badge-warning',
      completed: 'badge-success',
    },
  },
]);
</script>

<template>
  <AppShell :page-title="'Misas'">
    <CatalogHeader
      title="Misas"
      subtitle="Gestión de misas por fin de semana y ubicación"
      back-href="/"
      :count="masses.total"
      :icon="Church"
    />

    <section
      class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
        <label>
          <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">
            Filtrar por fin de semana
          </span>
          <select v-model="selectedWeekend" class="select select-bordered w-full">
            <option :value="null">Todos los fines de semana</option>
            <option v-for="weekend in weekends" :key="weekend.id" :value="weekend.id">
              {{ weekend.name }} · {{ weekend.church }}
            </option>
          </select>
        </label>

        <Link
          v-if="masses.data.length"
          :href="`/misas/${masses.data[0].id}/asistencias`"
          class="btn btn-outline btn-sm gap-1.5"
        >
          <QrCode class="h-4 w-4" />
          Capturar última misa
        </Link>
      </div>
    </section>

    <CatalogTable
      :columns="columns"
      :pagination="masses"
      :search="search"
      store-url="/misas"
      base-url="/misas"
      permission-module="masses"
      search-placeholder="Buscar por misa, parroquia o capilla..."
    />

    <section
      v-if="masses.data.length"
      class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
        Captura de asistencias
      </h2>
      <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
        <Link
          v-for="mass in masses.data"
          :key="mass.id"
          :href="`/misas/${mass.id}/asistencias`"
          class="rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-sky-300 hover:bg-sky-50 dark:border-slate-800 dark:hover:border-sky-800 dark:hover:bg-sky-950/30"
        >
          <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ mass.name }}</span>
          <span class="block text-xs text-slate-500">{{ mass.celebrated_at }} · {{ mass.location }}</span>
        </Link>
      </div>
    </section>
  </AppShell>
</template>
