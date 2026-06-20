<script setup>
import { LayoutGrid } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

defineProps({
  modules: { type: Object, required: true },
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
    key: 'description',
    label: 'Descripcion',
    type: 'text',
    required: true,
  },
  {
    key: 'key',
    label: 'Clave',
    type: 'text',
    required: true,
  },
];
</script>

<template>
  <AppShell :page-title="'Modulos'">
    <CatalogHeader
      title="Modulos"
      subtitle="Catalogo de modulos del sistema"
      back-href="/"
      :count="modules.total"
      :icon="LayoutGrid"
    />

    <CatalogTable
      :columns="columns"
      :pagination="modules"
      :search="search"
      store-url="/modulos"
      base-url="/modulos"
      permission-module="modulos"
      search-placeholder="Buscar por nombre o clave..."
    />
  </AppShell>
</template>
