<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  communities: { type: Object, required: true },
  municipalities: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const municipalityOptions = computed(() =>
  props.municipalities.map((m) => ({ value: m.id, label: m.name }))
);

const columns = computed(() => [
  {
    key: 'municipality_id',
    label: 'Municipio',
    type: 'select',
    required: true,
    options: municipalityOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
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
  <Head title="Comunidades" />

  <AppShell>
    <CatalogHeader
      title="Comunidades"
      subtitle="Catalogo de comunidades"
      back-href="/"
      :count="communities.total"
    />

    <CatalogTable
      :columns="columns"
      :pagination="communities"
      :search="search"
      store-url="/comunidades"
      base-url="/comunidades"
      permission-module="comunidades"
      search-placeholder="Buscar por nombre de comunidad..."
    />
  </AppShell>
</template>
