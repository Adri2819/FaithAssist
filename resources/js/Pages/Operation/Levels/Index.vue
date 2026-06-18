<script setup>
import { computed } from 'vue';
import { Layers } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  levels: { type: Object, required: true },
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
      inactive: 'badge-secondary',
    },
  },
]);
</script>

<template>
    <AppShell :page-title="'Niveles'">
        <CatalogHeader
            title="Niveles"
            subtitle="Gestiona los niveles de formación"
            back-href="/"
            :count="levels.total"
            :icon="Layers"
        />

        <CatalogTable
            :columns="columns"
            :pagination="levels"
            :search="search"
            store-url="/niveles"
            base-url="/niveles"
            permission-module="niveles"
            search-placeholder="Buscar por nombre o diocesis"
        />
    </AppShell>
</template>