<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  deaneries: { type: Object, required: true },
  dioceses: { type: Array, default: () => [] },
  search: { type: String, default: '' },
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
      :count="deaneries.total"
      :icon="BookOpen"
    />

    <CatalogTable
      :columns="columns"
      :pagination="deaneries"
      :search="search"
      store-url="/decanatos"
      base-url="/decanatos"
      permission-module="decanato"
      search-placeholder="Buscar por nombre de decanato..."
    />
  </AppShell>
</template>
