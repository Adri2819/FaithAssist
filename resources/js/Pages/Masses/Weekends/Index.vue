<script setup>
import { computed } from 'vue';
import { CalendarDays } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  weekends: { type: Object, required: true },
  churches: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const churchOptions = computed(() =>
  props.churches.map((church) => ({
    value: church.id,
    label: church.name,
  })),
);

const statusOptions = [
  { value: 'upcoming', label: 'Próximo' },
  { value: 'in_progress', label: 'En curso' },
  { value: 'completed', label: 'Terminado' },
];

const columns = computed(() => [
  {
    key: 'church_id',
    label: 'Parroquia',
    type: 'select',
    required: true,
    options: churchOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: false,
  },
  {
    key: 'starts_at',
    label: 'Sábado',
    type: 'date',
    required: true,
  },
  {
    key: 'ends_at',
    label: 'Domingo',
    type: 'date',
    required: true,
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
]);
</script>

<template>
  <AppShell :page-title="'Fines de semana de misas'">
    <CatalogHeader
      title="Fines de semana de misas"
      subtitle="Gestión de fines de semana por parroquia"
      back-href="/"
      :count="weekends.total"
      :icon="CalendarDays"
    />

    <CatalogTable
      :columns="columns"
      :pagination="weekends"
      :search="search"
      store-url="/fines-semana-misas"
      base-url="/fines-semana-misas"
      permission-module="weekends"
      search-placeholder="Buscar por nombre o parroquia..."
    />
  </AppShell>
</template>
