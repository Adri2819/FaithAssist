<script setup>
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

defineProps({
  states: { type: Object, required: true },
  search: { type: String, default: '' },
});

const columns = [
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'short_name',
    label: 'Abreviatura',
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
];
</script>

<template>
  <Head title="Estados" />

  <AppShell>
    <CatalogHeader
      title="Estados"
      subtitle="Catalogo de estados del pais"
      back-href="/"
      :count="states.total"
    />

    <CatalogTable
      :columns="columns"
      :pagination="states"
      :search="search"
      store-url="/estados"
      base-url="/estados"
      permission-module="estados"
      search-placeholder="Buscar por nombre de estado..."
    />
  </AppShell>
</template>
