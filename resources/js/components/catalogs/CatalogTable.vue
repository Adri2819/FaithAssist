<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Check, Download, Pencil, Plus, Search, Trash2, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import AppPagination from '../AppPagination.vue';

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

const rows = ref([...props.pagination.data]);
const searchTerm = ref(props.search);
const editingId = ref(null);
const editData = ref({});
const isAdding = ref(false);
const newRowData = ref({});
const loading = ref(false);
const addErrors = ref({});
const editErrors = ref({});
const addGeneralError = ref('');
const editGeneralError = ref('');
const page = usePage();

let debounce = null;

watch(searchTerm, (val) => {
  clearTimeout(debounce);
  debounce = setTimeout(() => {
    router.get(props.baseUrl, { search: val, page: 1 }, { preserveState: true, replace: true });
  }, 400);
});

watch(
  () => props.pagination.data,
  (newData) => {
    rows.value = [...newData];
    isAdding.value = false;
    editingId.value = null;
    editData.value = {};
    editErrors.value = {};
    editGeneralError.value = '';
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

const canCreate = computed(() =>
  hasPermission('create') && (!props.createRequiresFullScope || hasModuleFullScope.value)
);
const canUpdate = computed(() => hasPermission('update'));
const canDelete = computed(() => hasPermission('delete'));
const canExport = computed(() => {
  if (!props.exportUrl) return false;
  if (props.exportPermission) {
    return permissions.value.includes(props.exportPermission);
  }
  if (!props.permissionModule) return false;
  return permissions.value.includes(`${props.permissionModule}.export`);
});
const showActions = computed(() => canCreate.value || canUpdate.value || canDelete.value);

const getCsrf = () =>
  document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const exportTable = () => {
  if (!canExport.value || !props.exportUrl) return;

  const url = new URL(props.exportUrl, window.location.origin);
  if (searchTerm.value?.trim()) {
    url.searchParams.set('search', searchTerm.value.trim());
  }

  window.location.href = url.toString();
};

const apiFetch = async (url, method, data = null) => {
  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCsrf(),
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: data ? JSON.stringify(data) : undefined,
  });
  const json = await res.json();
  if (!res.ok) throw { status: res.status, errors: json.errors, message: json.message };
  return json;
};

const toast = (icon, title) =>
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });

const parseErrors = (err) => {
  const fieldErrors = err?.errors ?? {};
  const hasFieldErrors = Object.keys(fieldErrors).length > 0;

  return {
    fieldErrors,
    generalError: hasFieldErrors ? '' : (err?.message ?? 'Ocurrio un error inesperado.'),
  };
};

const getOptionLabel = (col, value) =>
  col.options?.find((o) => o.value === value)?.label ?? value;

const getBadgeClass = (col, value) =>
  col.badges?.[value] ?? 'badge-ghost';

const validateRow = (data) => {
  const errors = {};
  props.columns.forEach((col) => {
    if (col.required && (data[col.key] === "" || data[col.key] === null || data[col.key] === undefined)) {
      errors[col.key] = ["El campo " + col.label + " es obligatorio."];
    }
  });
  return errors;
};

const initNewRow = () => {
  const data = {};
  props.columns.forEach((col) => {
    data[col.key] = col.default !== undefined ? col.default : "";
  });
  return data;
};
const startAdd = () => {
  if (!canCreate.value) return;
  if (isAdding.value || editingId.value !== null) return;
  newRowData.value = initNewRow();
  addErrors.value = {};
  addGeneralError.value = '';
  isAdding.value = true;
};

const cancelAdd = () => {
  isAdding.value = false;
  newRowData.value = {};
  addErrors.value = {};
  addGeneralError.value = '';
};

const saveAdd = async () => {
  if (!canCreate.value) return;
  addErrors.value = {};
  addGeneralError.value = "";
  const clientErrors = validateRow(newRowData.value);
  if (Object.keys(clientErrors).length) {
    addErrors.value = clientErrors;
    return;
  }
  loading.value = true;
  try {
    const json = await apiFetch(props.storeUrl, 'POST', newRowData.value);
    rows.value.push(json.data);
    isAdding.value = false;
    newRowData.value = {};
    toast('success', json.message ?? 'Registro creado correctamente.');
    emit('row-added', json.data);
  } catch (err) {
    const { fieldErrors, generalError } = parseErrors(err);
    addErrors.value = fieldErrors;
    addGeneralError.value = generalError;
  } finally {
    loading.value = false;
  }
};

const startEdit = (row) => {
  if (!canUpdate.value) return;
  if (isAdding.value) return;
  editingId.value = row.id;
  editData.value = { ...row };
  editErrors.value = {};
  editGeneralError.value = '';
};

const cancelEdit = () => {
  editingId.value = null;
  editData.value = {};
  editErrors.value = {};
  editGeneralError.value = '';
};

const saveEdit = async (row) => {
  if (!canUpdate.value) return;
  editErrors.value = {};
  editGeneralError.value = "";
  const clientErrors = validateRow(editData.value);
  if (Object.keys(clientErrors).length) {
    editErrors.value = clientErrors;
    return;
  }
  loading.value = true;
  try {
    const json = await apiFetch(`${props.baseUrl}/${row.id}`, 'PUT', editData.value);
    const idx = rows.value.findIndex((r) => r.id === row.id);
    if (idx !== -1) rows.value[idx] = json.data;
    editingId.value = null;
    editData.value = {};
    toast('success', json.message ?? 'Registro actualizado correctamente.');
    emit('row-updated', json.data);
  } catch (err) {
    const { fieldErrors, generalError } = parseErrors(err);
    editErrors.value = fieldErrors;
    editGeneralError.value = generalError;
  } finally {
    loading.value = false;
  }
};

const confirmDelete = async (row) => {
  if (!canDelete.value) return;
  const result = await Swal.fire({
    title: 'Eliminar registro',
    text: 'Esta accion no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Si, eliminar',
    cancelButtonText: 'Cancelar',
  });
  if (!result.isConfirmed) return;
  loading.value = true;
  try {
    const json = await apiFetch(`${props.baseUrl}/${row.id}`, 'DELETE');
    rows.value = rows.value.filter((r) => r.id !== row.id);
    toast('success', json.message ?? 'Registro eliminado correctamente.');
    emit('row-deleted', row.id);
  } catch (err) {
    toast('error', err?.message ?? 'Ocurrio un error inesperado.');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <label
        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100 sm:w-80 dark:border-slate-700 dark:bg-slate-900 dark:focus-within:border-sky-600 dark:focus-within:ring-sky-900/40"
      >
        <Search class="h-4 w-4 shrink-0 text-slate-400" />
        <input
          v-model="searchTerm"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-slate-200"
        />
        <button
          v-if="searchTerm"
          type="button"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          @click="searchTerm = ''"
        >
          <X class="h-3.5 w-3.5" />
        </button>
      </label>

      <div class="flex items-center gap-2">
        <button
          v-if="canExport"
          class="btn btn-outline btn-sm gap-1.5"
          :disabled="loading"
          @click="exportTable"
        >
          <Download class="h-4 w-4" />
          {{ exportLabel }}
        </button>

        <button
          v-if="canCreate"
          class="btn btn-primary btn-sm gap-1.5"
          :disabled="isAdding || editingId !== null || loading"
          @click="startAdd"
        >
          <Plus class="h-4 w-4" />
          Agregar registro
        </button>
      </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <table class="table w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            <th v-for="col in columns" :key="col.key" class="px-4 py-3 font-semibold">
              {{ col.label }}
            </th>
            <th v-if="showActions" class="px-4 py-3 text-right font-semibold">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="isAdding"
            class="border-b border-sky-100 bg-sky-50/60 dark:border-sky-900/30 dark:bg-sky-950/20"
          >
            <td v-for="col in columns" :key="col.key" class="px-4 py-2">
              <input
                v-if="col.type === 'text'"
                :value="newRowData[col.key]"
                type="text"
                :class="['input input-bordered input-sm w-full', col.uppercase !== false ? 'uppercase' : '']"
                :placeholder="col.label"
                @input="newRowData[col.key] = col.uppercase !== false ? $event.target.value.toUpperCase() : $event.target.value"
              />
              <select
                v-else-if="col.type === 'select'"
                v-model="newRowData[col.key]"
                class="select select-bordered select-sm w-full"
              >
                <option v-if="col.default === undefined" value="" disabled>Elige una opcion</option>
                <option v-for="opt in col.options" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
              <p v-if="addErrors[col.key]?.length" class="mt-1 text-xs text-red-600 dark:text-red-400">
                {{ addErrors[col.key][0] }}
              </p>
            </td>
            <td v-if="showActions" class="px-4 py-2 text-right">
              <p v-if="addGeneralError" class="mb-1 text-left text-xs text-red-600 dark:text-red-400">
                {{ addGeneralError }}
              </p>
              <button
                class="btn btn-success btn-xs mr-1"
                :disabled="loading"
                @click="saveAdd"
                aria-label="Guardar nuevo registro"
                title="Guardar"
              >
                <Check class="h-3.5 w-3.5" />
              </button>
              <button
                class="btn btn-ghost btn-xs"
                @click="cancelAdd"
                aria-label="Cancelar nuevo registro"
                title="Cancelar"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </td>
          </tr>

          <template v-for="row in rows" :key="row.id">
            <tr
              v-if="editingId === row.id"
              class="border-b border-amber-100 bg-amber-50/60 dark:border-amber-900/20 dark:bg-amber-950/10"
            >
              <td v-for="col in columns" :key="col.key" class="px-4 py-2">
                <input
                  v-if="col.type === 'text'"
                  :value="editData[col.key]"
                  type="text"
                  :class="['input input-bordered input-sm w-full', col.uppercase !== false ? 'uppercase' : '']"
                  :placeholder="col.label"
                  @input="editData[col.key] = col.uppercase !== false ? $event.target.value.toUpperCase() : $event.target.value"
                />
                <select
                  v-else-if="col.type === 'select'"
                  v-model="editData[col.key]"
                  class="select select-bordered select-sm w-full"
                >
                  <option v-if="col.default === undefined" value="" disabled>Elige una opcion</option>
                  <option v-for="opt in col.options" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <p v-if="editErrors[col.key]?.length" class="mt-1 text-xs text-red-600 dark:text-red-400">
                  {{ editErrors[col.key][0] }}
                </p>
              </td>
              <td v-if="showActions" class="px-4 py-2 text-right">
                <p v-if="editGeneralError" class="mb-1 text-left text-xs text-red-600 dark:text-red-400">
                  {{ editGeneralError }}
                </p>
                <button
                  class="btn btn-warning btn-xs mr-1"
                  :disabled="loading"
                  @click="saveEdit(row)"
                  aria-label="Guardar cambios"
                  title="Guardar"
                >
                  <Check class="h-3.5 w-3.5" />
                </button>
                <button
                  class="btn btn-ghost btn-xs"
                  @click="cancelEdit"
                  aria-label="Cancelar edicion"
                  title="Cancelar"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </td>
            </tr>

            <tr
              v-else
              class="border-b border-slate-100 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40"
            >
              <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                <span
                  v-if="col.badges"
                  :class="['badge badge-sm font-medium', getBadgeClass(col, row[col.key])]"
                >
                  {{ getOptionLabel(col, row[col.key]) }}
                </span>
                <span v-else-if="col.type === 'select'">
                  {{ getOptionLabel(col, row[col.key]) }}
                </span>
                <span v-else>{{ row[col.key] ?? '' }}</span>
              </td>
              <td v-if="showActions" class="px-4 py-3 text-right">
                <button
                  v-if="canUpdate"
                  class="btn btn-ghost btn-xs mr-1 text-sky-600 hover:bg-sky-50 hover:text-sky-700 dark:text-sky-400 dark:hover:bg-sky-950/40"
                  :disabled="isAdding || editingId !== null || loading"
                  @click="startEdit(row)"
                  aria-label="Editar registro"
                  title="Editar"
                >
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button
                  v-if="canDelete"
                  class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-950/40"
                  :disabled="loading"
                  @click="confirmDelete(row)"
                  aria-label="Eliminar registro"
                  title="Eliminar"
                >
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </td>
            </tr>
          </template>

          <tr v-if="rows.length === 0 && !isAdding">
            <td
              :colspan="columns.length + (showActions ? 1 : 0)"
              class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500"
            >
              <span v-if="searchTerm">
                No se encontraron registros para
                <strong class="text-slate-600 dark:text-slate-300">"{{ searchTerm }}"</strong>.
              </span>
              <span v-else>No hay registros. Haz clic en <strong>Agregar registro</strong> para comenzar.</span>
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
