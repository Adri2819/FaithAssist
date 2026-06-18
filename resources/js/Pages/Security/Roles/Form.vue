<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import PermissionSelector from '../../../components/security/PermissionSelector.vue';

const props = defineProps({
  role: { type: Object, default: null },
  permissionGroups: { type: Array, required: true },
  selectedPermissions: { type: Array, default: () => [] },
});

const isEditing = computed(() => !!props.role);
const pageTitle = computed(() => (isEditing.value ? `Editar Rol: ${props.role.name}` : 'Nuevo Rol'));

const form = useForm({
  name: props.role?.name ?? '',
  description: props.role?.description ?? '',
  permissions: [...props.selectedPermissions],
});

const submit = () => {
  if (isEditing.value) {
    form.put(`/roles/${props.role.id}`, { preserveScroll: true });
  } else {
    form.post('/roles');
  }
};
</script>

<template>
  <AppShell :page-title="pageTitle">
    <CatalogHeader
      :title="pageTitle"
      subtitle="Gestiona el nombre, descripcion y permisos del rol"
      back-href="/roles"
    />

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Basic info card -->
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">
          Datos del rol
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <!-- Name -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
              Nombre <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Ej. Administrador"
              class="input input-bordered w-full"
              :class="{ 'input-error': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Description -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
              Descripcion
            </label>
            <input
              v-model="form.description"
              type="text"
              placeholder="Descripcion del rol"
              class="input input-bordered w-full"
              :class="{ 'input-error': form.errors.description }"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
              {{ form.errors.description }}
            </p>
          </div>
        </div>
      </div>

      <!-- Permission selector -->
      <PermissionSelector
        v-model="form.permissions"
        :groups="permissionGroups"
      />

      <!-- Form errors -->
      <p v-if="form.errors.permissions" class="text-sm text-red-500">
        {{ form.errors.permissions }}
      </p>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link href="/roles" class="btn btn-ghost btn-sm">
          Cancelar
        </Link>
        <button
          type="submit"
          class="btn btn-primary btn-sm"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar rol' : 'Crear rol') }}
        </button>
      </div>
    </form>
  </AppShell>
</template>
