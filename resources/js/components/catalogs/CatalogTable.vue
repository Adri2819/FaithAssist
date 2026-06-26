<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Download, Plus, Search, X } from 'lucide-vue-next';

import AppPagination from '../AppPagination.vue';
import CatalogRow from './CatalogRow.vue';
import { useCatalogCrud } from '../../composables/useCatalogCrud';

const props = defineProps({
  columns: { type: Array, required: true },
  pagination: { type: Object, required: true },
  search: { type: String, default: '' },
  storeUrl: { type: String, required: true },
  baseUrl: { type: String, required: true },
  permissionModule: { type: String, default: null },
  createRequiresFullScope: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: 'Buscar...' },
  exportUrl: { type: String, default: null },
  exportPermission: { type: String, default: null },
  exportLabel: { type: String, default: 'Exportar Excel' },
});

const emit = defineEmits(['row-added', 'row-updated', 'row-deleted']);

const page = usePage();

const rows = ref([...props.pagination.data]);
const searchTerm = ref(props.search);
const editingId = ref(null);
const isAdding = ref(false);

const {
  loading,
  addErrors,
  editErrors,
  addGeneralError,
  editGeneralError,
  clearAddErrors,
  clearEditErrors,
  createRow,
  updateRow,
  deleteRow,
} = useCatalogCrud({
  baseUrl: props.baseUrl,
  storeUrl: props.storeUrl,
});

let debounce = null;

watch(searchTerm, (val) => {
  clearTimeout(debounce);

  debounce = setTimeout(() => {
    router.get(
      props.baseUrl,
      { search: val, page: 1 },
      {
        preserveState: true,
        replace: true,
      },
    );
  }, 400);
});

watch(
  () => props.pagination.data,
  (newData) => {
    rows.value = [...newData];
    editingId.value = null;
    isAdding.value = false;
    clearAddErrors();
    clearEditErrors();
  },
);

const permissions = computed(() => page.props.auth?.permissions ?? []);

const fullScopeAccess = computed(() => page.props.auth?.scope?.full_access ?? {});

const hasPermission = (action) => {
  if (!props.permissionModule) return true;

  return permissions.value.includes(`${props.permissionModule}.${action}`);
};

const hasModuleFullScope = computed(() => {
  if (!props.permissionModule) return true;

  return fullScopeAccess.value?.[props.permissionModule] === true;
});

const canCreate = computed(
  () => hasPermission('create') && (!props.createRequiresFullScope || hasModuleFullScope.value),
);

const canUpdate = computed(() => hasPermission('update'));

const canDelete = computed(() => hasPermission('delete'));

const showActions = computed(() => canCreate.value || canUpdate.value || canDelete.value);

const validateRow = (data) => {
  const errors = {};

  props.columns.forEach((col) => {
    if (
      col.required &&
      (data[col.key] === '' || data[col.key] === null || data[col.key] === undefined)
    ) {
      errors[col.key] = [`El campo ${col.label} es obligatorio.`];
    }
  });

  return errors;
};

const startAdd = () => {
  if (!canCreate.value) return;
  if (isAdding.value || editingId.value !== null) return;

  clearAddErrors();
  isAdding.value = true;
};

const cancelAdd = () => {
  isAdding.value = false;
  clearAddErrors();
};

const saveAdd = async (payload) => {
  const clientErrors = validateRow(payload);

  if (Object.keys(clientErrors).length) {
    addErrors.value = clientErrors;
    return;
  }

  const created = await createRow(payload);

  if (created) {
    rows.value.push(created);
    isAdding.value = false;
    emit('row-added', created);
  }
};

const startEdit = (row) => {
  if (!canUpdate.value) return;
  if (isAdding.value) return;

  clearEditErrors();
  editingId.value = row.id;
};

const cancelEdit = () => {
  editingId.value = null;
  clearEditErrors();
};

const saveEdit = async (row, payload) => {
  const clientErrors = validateRow(payload);

  if (Object.keys(clientErrors).length) {
    editErrors.value = clientErrors;
    return;
  }

  const updated = await updateRow(row.id, payload);

  if (updated) {
    const idx = rows.value.findIndex((r) => r.id === row.id);

    if (idx !== -1) {
      rows.value[idx] = updated;
    }

    editingId.value = null;
    emit('row-updated', updated);
  }
};

const handleDelete = async (row) => {
  const deleted = await deleteRow(row);

  if (deleted) {
    rows.value = rows.value.filter((r) => r.id !== row.id);

    emit('row-deleted', row.id);
  }
};
</script>

<template>
  <div>
    <div class="ui-table-toolbar">
      <label class="ui-search sm:w-80">
        <Search class="h-4 w-4 shrink-0 text-slate-400" />

        <input
          v-model="searchTerm"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-slate-100 dark:placeholder:text-slate-500"
        />

        <button
          v-if="searchTerm"
          type="button"
          @click="searchTerm = ''"
          class="text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200"
        >
          <X class="h-3.5 w-3.5" />
        </button>
      </label>

      <div class="flex items-center gap-2">
        <button
          v-if="canExport"
          class="ui-btn ui-btn-secondary ui-btn-sm"
          :disabled="loading"
          @click="exportTable"
        >
          <Download class="h-4 w-4" />
          {{ exportLabel }}
        </button>

        <button
          v-if="canCreate"
          class="ui-btn ui-btn-primary ui-btn-sm"
          :disabled="isAdding || editingId !== null || loading"
          @click="startAdd"
        >
          <Plus class="h-4 w-4" />
          Agregar registro
        </button>
      </div>
    </div>

    <div class="ui-table-wrap">
      <table class="ui-table">
        <thead>
          <tr>
            <th v-for="col in columns" :key="col.key">
              {{ col.label }}
            </th>

            <th v-if="showActions">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <CatalogRow
            v-if="isAdding"
            mode="create"
            :columns="columns"
            :errors="addErrors"
            :general-error="addGeneralError"
            :loading="loading"
            :show-actions="showActions"
            @save="saveAdd"
            @cancel="cancelAdd"
          />

          <CatalogRow
            v-for="row in rows"
            :key="row.id"
            :row="row"
            :columns="columns"
            :mode="editingId === row.id ? 'edit' : 'view'"
            :errors="editErrors"
            :general-error="editGeneralError"
            :loading="loading"
            :show-actions="showActions"
            :can-update="canUpdate"
            :can-delete="canDelete"
            @edit="startEdit(row)"
            @delete="handleDelete(row)"
            @cancel="cancelEdit"
            @save="(payload) => saveEdit(row, payload)"
          />

          <tr v-if="rows.length === 0 && !isAdding">
            <td :colspan="columns.length + (showActions ? 1 : 0)" class="ui-table-empty">
              <span v-if="searchTerm">
                No se encontraron registros para
                <strong>"{{ searchTerm }}"</strong>
              </span>

              <span v-else> No hay registros. </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AppPagination
      :links="pagination.links"
      :from="pagination.from"
      :to="pagination.to"
      :total="pagination.total"
    />
  </div>
</template>
