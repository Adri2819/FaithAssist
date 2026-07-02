<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LogIn, LogOut, QrCode } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import AppPagination from '../../../components/AppPagination.vue';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';

const props = defineProps({
  mass: { type: Object, required: true },
  attendances: { type: Object, required: true },
});

const rows = ref([...props.attendances.data]);
const childCode = ref('');
const loading = ref(false);
const errors = ref({});

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const scan = async (action) => {
  errors.value = {};
  loading.value = true;

  try {
    const response = await fetch(`/misas/${props.mass.id}/asistencias/scan`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        child_code: childCode.value,
        action,
      }),
    });
    const json = await response.json();

    if (!response.ok) {
      errors.value = json.errors ?? {};
      throw new Error(json.message ?? 'No se pudo registrar la asistencia.');
    }

    const index = rows.value.findIndex((row) => row.id === json.data.id);
    if (index === -1) {
      rows.value = [json.data, ...rows.value];
    } else {
      rows.value[index] = json.data;
    }

    childCode.value = '';
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: json.message, timer: 2500, showConfirmButton: false });
  } catch (error) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: error.message, timer: 3000, showConfirmButton: false });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <AppShell :page-title="'Asistencia a misa'">
    <CatalogHeader
      title="Asistencia a misa"
      :subtitle="`${mass.name} · ${mass.location} · ${mass.celebrated_at}`"
      back-href="/misas"
      :icon="QrCode"
    />

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <span class="badge badge-outline">{{ mass.weekend }}</span>
        <span class="badge" :class="mass.attendance_status === 'in_progress' ? 'badge-warning' : 'badge-ghost'">
          Captura: {{ mass.attendance_status }}
        </span>
      </div>

      <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
        Código único del niño
      </label>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input
          v-model="childCode"
          class="input input-bordered w-full font-mono"
          :class="{ 'input-error': errors.child_code }"
          placeholder="Escanea o escribe el código del QR"
          autocomplete="off"
          autofocus
          @keyup.enter="scan('check_in')"
        />
        <button class="btn btn-primary gap-1.5" :disabled="loading" @click="scan('check_in')">
          <LogIn class="h-4 w-4" />
          Entrada
        </button>
        <button class="btn btn-outline gap-1.5" :disabled="loading" @click="scan('check_out')">
          <LogOut class="h-4 w-4" />
          Salida
        </button>
      </div>
      <p v-if="errors.child_code" class="mt-2 text-xs text-red-500">{{ errors.child_code[0] }}</p>
      <p class="mt-2 text-xs text-slate-400">
        Una asistencia cuenta como válida cuando el niño tiene entrada y salida en esta misa.
      </p>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <table class="table w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-950">
            <th>Código</th>
            <th>Niño</th>
            <th>Ubicación</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="attendance in rows" :key="attendance.id">
            <td class="font-mono text-xs">{{ attendance.child_code }}</td>
            <td>{{ attendance.child_name }}</td>
            <td>{{ attendance.location }}</td>
            <td>{{ attendance.check_in_at ?? '—' }}</td>
            <td>{{ attendance.check_out_at ?? '—' }}</td>
            <td>
              <span class="badge badge-sm" :class="attendance.valid ? 'badge-success' : 'badge-warning'">
                {{ attendance.valid ? 'Válida' : 'Pendiente' }}
              </span>
            </td>
          </tr>
          <tr v-if="rows.length === 0">
            <td colspan="6" class="py-10 text-center text-sm text-slate-400">No hay asistencias registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <AppPagination
      :links="attendances.links"
      :from="attendances.from"
      :to="attendances.to"
      :total="attendances.total"
    />

    <div class="mt-6">
      <Link href="/misas" class="btn btn-ghost btn-sm">Volver a misas</Link>
    </div>
  </AppShell>
</template>
