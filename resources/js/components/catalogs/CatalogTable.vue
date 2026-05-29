<script setup>
import { computed, ref } from 'vue';
import { Check, Pencil, Trash2, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
  columns: { type: Array, required: true },
  initialRows: { type: Array, default: () => [] },
  storeUrl: { type: String, required: true },
  baseUrl: { type: String, required: true },
  searchKey: { type: String, default: null },
  searchPlaceholder: { type: String, default: 'Buscar...' },
});

const emit = defineEmits(['row-added', 'row-updated', 'row-deleted']);

const rows = ref([...props.initialRows]);
const searchTerm = ref('');
const editingId = ref(null);
const editData = ref({});
const isAdding = ref(false);
const newRowData = ref({});
const loading = ref(false);
const addErrors = ref({});
const editErrors = ref({});
const addGeneralError = ref('');
const editGeneralError = ref('');

const filteredRows = computed(() => {
  if (!searchTerm.value || !props.searchKey) return rows.value;
  const q = searchTerm.value.toLowerCase();
  return rows.value.filter(r =>
    String(r[props.searchKey] ?? '').toLowerCase().includes(q),
  );
});

const getCsrf = () =>
  document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-4 w-4 shrink-0 text-slate-400"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.35-4.35" />
        </svg>
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
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>
      </label>

      <button
        class="btn btn-primary btn-sm gap-1.5"
        :disabled="isAdding || editingId !== null || loading"
        @click="startAdd"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14M5 12h14" />
        </svg>
        Agregar registro
      </button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <table class="table w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            <th v-for="col in columns" :key="col.key" class="px-4 py-3 font-semibold">
              {{ col.label }}
            </th>
            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
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
                class="input input-bordered input-sm w-full uppercase"
                :placeholder="col.label"
                @input="newRowData[col.key] = $event.target.value.toUpperCase()"
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
            <td class="px-4 py-2 text-right">
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

          <template v-for="row in filteredRows" :key="row.id">
            <tr
              v-if="editingId === row.id"
              class="border-b border-amber-100 bg-amber-50/60 dark:border-amber-900/20 dark:bg-amber-950/10"
            >
              <td v-for="col in columns" :key="col.key" class="px-4 py-2">
                <input
                  v-if="col.type === 'text'"
                  :value="editData[col.key]"
                  type="text"
                  class="input input-bordered input-sm w-full uppercase"
                  :placeholder="col.label"
                  @input="editData[col.key] = $event.target.value.toUpperCase()"
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
              <td class="px-4 py-2 text-right">
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
              <td class="px-4 py-3 text-right">
                <button
                  class="btn btn-ghost btn-xs mr-1 text-sky-600 hover:bg-sky-50 hover:text-sky-700 dark:text-sky-400 dark:hover:bg-sky-950/40"
                  :disabled="isAdding || editingId !== null || loading"
                  @click="startEdit(row)"
                  aria-label="Editar registro"
                  title="Editar"
                >
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button
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

          <tr v-if="filteredRows.length === 0 && !isAdding">
            <td
              :colspan="columns.length + 1"
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

    <div v-if="rows.length > 0" class="mt-2 text-right text-xs text-slate-400 dark:text-slate-500">
      {{ filteredRows.length }} de {{ rows.length }} registro{{ rows.length !== 1 ? 's' : '' }}
    </div>
  </div>
</template>
