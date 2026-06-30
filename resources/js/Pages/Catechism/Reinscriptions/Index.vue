<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeftRight, Filter, Search, UserRound, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppPagination from '../../../components/AppPagination.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import AppShell from '../../../components/layouts/AppShell.vue';

const props = defineProps({
  children: { type: Object, required: true },
  levels: { type: Array, default: () => [] },
  search: { type: String, default: '' },
});

const searchTerm = ref(props.search);
const selectedChildId = ref(null);
const debounce = ref(null);

const form = useForm({
  child_id: null,
  to_level_ids: [],
  notes: '',
});

const activeFilters = computed(() => !!searchTerm.value);

const reload = () => {
  router.get(
    '/reinscripciones',
    { search: searchTerm.value || undefined },
    { preserveState: true, replace: true },
  );
};

watch(searchTerm, () => {
  clearTimeout(debounce.value);
  debounce.value = setTimeout(reload, 400);
});

const clearFilters = () => {
  searchTerm.value = '';
};

const selectedChild = computed(() =>
  props.children.data.find((child) => child.id === selectedChildId.value),
);
const destinationLevels = computed(() => {
  if (!selectedChild.value?.diocese_id) return props.levels;

  return props.levels.filter((level) => level.diocese_id === selectedChild.value.diocese_id);
});

const selectChild = (child) => {
  selectedChildId.value = child.id;
  form.clearErrors();
  form.child_id = child.id;
  form.to_level_ids = [];
  form.notes = '';
};

const toggleDestinationLevel = (levelId) => {
  const normalizedId = Number(levelId);
  if (form.to_level_ids.includes(normalizedId)) {
    form.to_level_ids = form.to_level_ids.filter((id) => id !== normalizedId);
    return;
  }

  form.to_level_ids = [...form.to_level_ids, normalizedId];
};

const submit = () => {
  form.post('/reinscripciones', {
    preserveScroll: true,
    onSuccess: () => {
      selectedChildId.value = null;
      form.reset();
    },
  });
};
</script>

<template>
  <AppShell :page-title="'Reinscripciones'">
    <CatalogHeader
      title="Reinscripciones"
      subtitle="Movimiento de niños de un nivel a otro durante el periodo activo"
      back-href="/"
      :count="children.total"
      :icon="ArrowLeftRight"
    />

    <section
      class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2
          class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300"
        >
          <Filter class="h-4 w-4 text-rose-700 dark:text-rose-400" />
          Filtros
        </h2>
        <button
          v-if="activeFilters"
          type="button"
          class="btn btn-ghost btn-xs gap-1"
          @click="clearFilters"
        >
          <X class="h-3.5 w-3.5" />
          Limpiar
        </button>
      </div>

      <label
        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100 dark:border-slate-700 dark:bg-slate-950 dark:focus-within:border-sky-600 dark:focus-within:ring-sky-900/40"
      >
        <Search class="h-4 w-4 shrink-0 text-slate-400" />
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar por código, nombre o parroquia..."
          class="w-full bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-slate-200"
        />
      </label>
    </section>

    <section
      v-if="selectedChild"
      class="mb-4 rounded-2xl border border-rose-200 bg-rose-50/70 p-5 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/20"
    >
      <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
          <p
            class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-rose-700 dark:text-rose-300"
          >
            <UserRound class="h-4 w-4" />
            Niño seleccionado
          </p>
          <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
            {{ selectedChild.full_name }}
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ selectedChild.code }} · {{ selectedChild.church }} · {{ selectedChild.community }}
          </p>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" @click="selectedChildId = null">
          Cambiar niño
        </button>
      </div>

      <div class="grid gap-4 lg:grid-cols-[1fr_1.3fr]">
        <div class="rounded-2xl border border-white/70 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">
            Información del niño
          </h3>
          <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div>
              <dt class="font-semibold text-slate-400">Nacimiento</dt>
              <dd class="font-bold text-slate-700 dark:text-slate-200">{{ selectedChild.birthdate }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-400">Correo</dt>
              <dd class="font-bold text-slate-700 dark:text-slate-200">{{ selectedChild.email || 'Sin correo' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-400">Teléfono</dt>
              <dd class="font-bold text-slate-700 dark:text-slate-200">{{ selectedChild.phone || 'Sin teléfono' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-slate-400">Emergencia</dt>
              <dd class="font-bold text-slate-700 dark:text-slate-200">
                {{ selectedChild.emergency_phone || 'Sin teléfono' }}
              </dd>
            </div>
          </dl>

          <div class="mt-5">
            <h3 class="mb-2 text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">
              Niveles actuales
            </h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="level in selectedChild.levels"
                :key="level.assignment_id"
                class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-sm font-bold text-sky-700 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-300"
              >
                {{ level.name }}
              </span>
            </div>
          </div>
        </div>

        <form
          class="rounded-2xl border border-white/70 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
          @submit.prevent="submit"
        >
          <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">
              Niveles a asignar
            </h3>
            <span class="text-xs font-semibold text-slate-400">
              {{ form.to_level_ids.length }} seleccionado(s)
            </span>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <button
              v-for="level in destinationLevels"
              :key="level.id"
              type="button"
              class="rounded-2xl border p-4 text-left transition"
              :class="
                form.to_level_ids.includes(level.id)
                  ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm dark:border-rose-400 dark:bg-rose-950/30 dark:text-rose-200'
                  : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:bg-rose-50/60 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-rose-700'
              "
              @click="toggleDestinationLevel(level.id)"
            >
              <span class="flex items-center gap-3">
                <span
                  class="flex h-5 w-5 items-center justify-center rounded border text-xs font-bold"
                  :class="
                    form.to_level_ids.includes(level.id)
                      ? 'border-rose-500 bg-rose-600 text-white'
                      : 'border-slate-300 text-transparent dark:border-slate-600'
                  "
                >
                  ✓
                </span>
                <span class="font-semibold">{{ level.name }}</span>
              </span>
            </button>
          </div>

          <textarea
            v-model="form.notes"
            class="textarea textarea-bordered mt-4 w-full"
            rows="3"
            placeholder="Observaciones de reinscripción..."
          />

          <p v-if="form.hasErrors" class="mt-2 text-xs font-semibold text-red-500">
            {{ form.errors.child_id || form.errors.to_level_ids || form.errors.notes }}
          </p>

          <div class="mt-4 flex justify-end gap-2">
            <button type="button" class="btn btn-ghost btn-sm" @click="selectedChildId = null">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              Registrar reinscripción
            </button>
          </div>
        </form>
      </div>
    </section>

    <div
      class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
      <table class="table w-full">
        <thead>
          <tr
            class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
          >
            <th class="px-4 py-3 font-semibold">Código</th>
            <th class="px-4 py-3 font-semibold">Niño</th>
            <th class="px-4 py-3 font-semibold">Parroquia</th>
            <th class="px-4 py-3 font-semibold">Niveles actuales</th>
            <th class="px-4 py-3 text-right font-semibold">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="child in children.data"
            :key="child.id"
            class="border-b border-slate-100 align-top transition-colors hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40"
          >
            <td class="px-4 py-3">
              <span
                class="rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
              >
                {{ child.code }}
              </span>
            </td>
            <td class="px-4 py-3">
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                {{ child.full_name }}
              </p>
              <p class="text-xs text-slate-400">{{ child.community }}</p>
            </td>
            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ child.church }}</td>
            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
              {{ child.levels.map((level) => level.name).join(', ') }}
            </td>
            <td class="px-4 py-3 text-right">
              <button
                type="button"
                class="btn btn-primary btn-xs"
                :class="{ 'btn-outline': selectedChildId === child.id }"
                @click="selectChild(child)"
              >
                {{ selectedChildId === child.id ? 'Seleccionado' : 'Seleccionar' }}
              </button>
            </td>
          </tr>

          <tr v-if="children.data.length === 0">
            <td
              colspan="5"
              class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500"
            >
              <span v-if="activeFilters">No se encontraron niños con los filtros seleccionados.</span>
              <span v-else>No hay niños con niveles activos para reinscribir.</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AppPagination
      :links="children.links"
      :from="children.from"
      :to="children.to"
      :total="children.total"
    />
  </AppShell>
</template>
