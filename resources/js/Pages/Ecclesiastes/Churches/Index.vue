<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import CatalogTable from '../../../components/catalogs/CatalogTable.vue';

const props = defineProps({
  churches: { type: Array, default: () => [] },
  municipalities: { type: Array, default: () => [] },
  deaneries: { type: Array, default: () => [] },
});

const municipalityOptions = computed(() =>
  props.municipalities.map((m) => ({ value: m.id, label: m.name }))
);

const deaneryOptions = computed(() =>
  props.deaneries.map((d) => ({ value: d.id, label: d.name }))
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
    key: 'deanery_id',
    label: 'Decanato',
    type: 'select',
    required: false,
    options: deaneryOptions.value,
  },
  {
    key: 'name',
    label: 'Nombre',
    type: 'text',
    required: true,
  },
  {
    key: 'alias',
    label: 'Alias',
    type: 'text',
    required: false,
  },
  {
    key: 'email',
    label: 'Correo',
    type: 'text',
    required: false,
  },
  {
    key: 'phone',
    label: 'Telefono',
    type: 'text',
    required: false,
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
  <Head title="Parroquias" />

  <AppShell>
    <CatalogHeader
      title="Parroquias"
      subtitle="Catalogo de parroquias"
      back-href="/"
      :count="churches.length"
    />

    <CatalogTable
      :columns="columns"
      :initial-rows="churches"
      store-url="/parroquias"
      base-url="/parroquias"
      search-key="name"
      search-placeholder="Buscar por nombre de parroquia..."
    />
  </AppShell>
</template>
