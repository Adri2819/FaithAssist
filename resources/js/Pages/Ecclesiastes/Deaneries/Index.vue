<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  deaneries: { type: Array, default: () => [] },
  dioceses: { type: Array, default: () => [] },
});

const dioceseOptions = computed(() =>
  props.dioceses.map((d) => ({
    value: d.id,
    label: d.name,
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
  <Head title="Decanatos" />

  <AppShell>
    <CatalogHeader
      title="Decanatos"
      subtitle="Catalogo de decanatos"
      back-href="/"
      :count="deaneries.length"
    />

    <CatalogTable
      :columns="columns"
      :initial-rows="deaneries"
      store-url="/decanatos"
      base-url="/decanatos"
      search-key="name"
      search-placeholder="Buscar por nombre de decanato..."
    />
  </AppShell>
</template>
