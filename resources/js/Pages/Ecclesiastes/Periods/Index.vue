<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CalendarDays } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  periods: { type: Object, required: true },
  dioceses: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const dioceseOptions = computed(() =>
  props.dioceses.map((diocese) => ({
    value: diocese.id,
    label: diocese.name,
  })),
);

const columns = computed(() => [
  {
    key: 'diocese_id',
    label: 'Diocesis',
    type: 'select',
    required: true,
    options: dioceseOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'start_date',
    label: 'Inicio',
    type: 'date',
    required: true,
  },
  {
    key: 'end_date',
    label: 'Fin',
    type: 'date',
    required: true,
  },
  {
    key: 'status',
    label: 'Estatus',
    type: 'select',
    default: 'upcoming',
    options: [
      { value: 'upcoming', label: 'Proximo' },
      { value: 'in_progress', label: 'En curso' },
      { value: 'completed', label: 'Completado' },
    ],
    badges: {
      upcoming: 'badge-info',
      in_progress: 'badge-warning',
      completed: 'badge-success',
    },
  },
]);
</script>

<template>
  <Head title="Periodos" />

  <AppShell>
    <CatalogHeader
      title="Periodos"
      subtitle="Gestion de periodos escolares por diocesis"
      back-href="/"
      :count="periods.total"
      :icon="CalendarDays"
    />

    <CatalogTable
      :columns="columns"
      :pagination="periods"
      :search="search"
      store-url="/periodos"
      base-url="/periodos"
      permission-module="periodos"
      search-placeholder="Buscar por nombre, anio o diocesis..."
    />
  </AppShell>
</template>
