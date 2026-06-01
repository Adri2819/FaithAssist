<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { KeyRound } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  permissions: { type: Object, required: true },
  modules: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const columns = computed(() => [
  {
    key: 'name',
    label: 'Clave',
    type: 'text',
    uppercase: false,
    required: true,
  },
  {
    key: 'description',
    label: 'Descripcion',
    type: 'text',
    required: true,
  },
  {
    key: 'module_key',
    label: 'Modulo',
    type: 'select',
    required: true,
    options: props.modules.map((m) => ({ value: m.key, label: m.name })),
  },
]);
</script>

<template>
  <Head title="Permisos" />

  <AppShell>
    <CatalogHeader
      title="Permisos"
      subtitle="Catalogo de permisos del sistema"
      back-href="/"
      :count="permissions.total"
      :icon="KeyRound"
    />

    <CatalogTable
      :columns="columns"
      :pagination="permissions"
      :search="search"
      store-url="/permisos"
      base-url="/permisos"
      permission-module="permisos"
      search-placeholder="Buscar por clave, descripcion o modulo..."
    />
  </AppShell>
</template>
