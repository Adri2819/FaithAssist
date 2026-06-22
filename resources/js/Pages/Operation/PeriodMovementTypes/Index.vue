<script setup>
import { computed } from 'vue';
import { Tags } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  movementTypes: { type: Object, required: true },
  statusOptions: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const columns = computed(() => [
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'description',
    label: 'Descripcion',
    type: 'text',
    required: false,
    uppercase: false,
  },
  {
    key: 'status',
    label: 'Estatus',
    type: 'select',
    default: 'active',
    options: props.statusOptions,
    badges: {
      active: 'badge-success',
      inactive: 'badge-secondary',
    },
  },
]);
</script>

<template>
  <AppShell :page-title="'Tipos de Movimiento del Periodo'">
    <CatalogHeader
      title="Tipos de Movimiento del Periodo"
      subtitle="Administra el catálogo base para los movimientos de cada periodo"
      back-href="/"
      :count="movementTypes.total"
      :icon="Tags"
    />

    <CatalogTable
      :columns="columns"
      :pagination="movementTypes"
      :search="search"
      store-url="/tipos-movimientos-periodo"
      base-url="/tipos-movimientos-periodo"
      permission-module="tipos_movimientos_periodo"
      search-placeholder="Buscar por nombre, descripcion o estatus"
    />
  </AppShell>
</template>
