<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, ShieldCheck } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';

const props = defineProps({
  roles: { type: Object, required: true },
  search: { type: String, default: '' },
});

const searchTerm = ref(props.search);
let debounce = null;

watch(searchTerm, (val) => {
  clearTimeout(debounce);
  debounce = setTimeout(() => {
    router.get('/roles', { search: val }, { preserveState: true, replace: true });
  }, 400);
});

const paginationLinks = computed(() =>
  props.roles.links.filter((l) => l.label !== '&laquo; Previous' && l.label !== 'Next &raquo;'),
);
</script>

<template>
  <Head title="Roles" />

  <AppShell>
    <CatalogHeader
      title="Roles"
      subtitle="Catalogo de roles del sistema"
      back-href="/"
      :count="roles.total"
    />

    <!-- Toolbar -->
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
          placeholder="Buscar por nombre o descripcion..."
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

      <Link
        href="/roles/create"
        class="btn btn-primary btn-sm gap-1.5"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14M5 12h14" />
        </svg>
        Nuevo
      </Link>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <table class="table w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            <th class="px-4 py-3 font-semibold">Nombre</th>
            <th class="px-4 py-3 font-semibold">Descripcion</th>
            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="role in roles.data"
            :key="role.id"
            class="border-b border-slate-100 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40"
          >
            <td class="px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100">
              {{ role.name }}
            </td>
            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
              {{ role.description ?? '' }}
            </td>
            <td class="px-4 py-3 text-right">
              <Link
                :href="`/roles/${role.id}/edit`"
                class="btn btn-ghost btn-xs text-sky-600 hover:bg-sky-50 hover:text-sky-700 dark:text-sky-400 dark:hover:bg-sky-950/40"
                title="Editar"
              >
                <Pencil class="h-3.5 w-3.5" />
              </Link>
            </td>
          </tr>

          <tr v-if="roles.data.length === 0">
            <td colspan="3" class="px-4 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
              <span v-if="searchTerm">
                No se encontraron roles para
                <strong class="text-slate-600 dark:text-slate-300">"{{ searchTerm }}"</strong>.
              </span>
              <span v-else>No hay roles registrados.</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="roles.last_page > 1" class="mt-4 flex items-center justify-between text-sm">
      <span class="text-slate-500 dark:text-slate-400">
        Mostrando {{ roles.from }}{{ roles.to }} de {{ roles.total }} registros
      </span>
      <div class="flex items-center gap-1">
        <Link
          v-for="link in roles.links"
          :key="link.label"
          :href="link.url ?? ''"
          class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm transition"
          :class="[
            link.active
              ? 'bg-sky-600 font-semibold text-white shadow-sm'
              : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
            !link.url ? 'cursor-not-allowed opacity-40' : '',
          ]"
          :aria-disabled="!link.url"
          v-html="link.label"
        />
      </div>
    </div>

    <div v-else-if="roles.total > 0" class="mt-2 text-right text-xs text-slate-400 dark:text-slate-500">
      {{ roles.total }} registro{{ roles.total !== 1 ? 's' : '' }}
    </div>
  </AppShell>
</template>
