<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  dioceses: { type: Object, required: true },
  states: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const stateOptions = computed(() =>
  props.states.map((s) => ({
    value: s.id,
    label: s.short_name ? `${s.name} (${s.short_name})` : s.name,
  })),
);

const columns = computed(() => [
  {
    key: 'state_id',
    label: 'Estado',
    type: 'select',
    required: true,
    options: stateOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'bishop',
    label: 'Obispo',
    type: 'text',
    required: false,
  },
  {
    key: 'status',
    label: 'Estatus',
    type: 'select',
    default: 'active',
    options: [
      { value: 'active', label: 'Activo' },
      { value: 'inactive', label: 'Inactivo' },
    ],
    badges: {
      active: 'badge-success',
      inactive: 'badge-error',
    },
  },
]);
</script>

<template>
  <Head title="Diocesis" />

  <AppShell>
    <CatalogHeader
      title="Diocesis"
      subtitle="Catalogo de diocesis"
      back-href="/"
      :count="dioceses.total"
    />

    <CatalogTable
      :columns="columns"
      :pagination="dioceses"
      :search="search"
      store-url="/diocesis"
      base-url="/diocesis"
      permission-module="diocesis"
      search-placeholder="Buscar por nombre de diocesis..."
    />
  </AppShell>
</template>
