<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  chapels: { type: Array, default: () => [] },
  communities: { type: Array, default: () => [] },
  churches: { type: Array, default: () => [] },
});

const communityOptions = computed(() =>
  props.communities.map((c) => ({ value: c.id, label: c.name }))
);

const churchOptions = computed(() =>
  props.churches.map((c) => ({ value: c.id, label: c.name }))
);

const columns = computed(() => [
  {
    key: 'community_id',
    label: 'Comunidad',
    type: 'select',
    required: true,
    options: communityOptions.value,
  },
  {
    key: 'church_id',
    label: 'Parroquia',
    type: 'select',
    required: false,
    options: churchOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'address',
    label: 'Direccion',
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
  <Head title="Capillas" />

  <AppShell>
    <CatalogHeader
      title="Capillas"
      subtitle="Catalogo de capillas"
      back-href="/"
      :count="chapels.length"
    />

    <CatalogTable
      :columns="columns"
      :initial-rows="chapels"
      store-url="/capillas"
      base-url="/capillas"
      search-key="name"
      search-placeholder="Buscar por nombre de capilla..."
    />
  </AppShell>
</template>
