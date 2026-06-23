<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  status: { type: String, default: null },
  error: { type: String, default: null },
  maskedPhone: { type: String, default: '' },
});

const form = useForm({
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/forgot-password/reset', { preserveScroll: true });
};
</script>

<template>
  <Head title="Restablecer contrasena" />

  <main class="min-h-screen bg-linear-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-8">
    <section class="mx-auto w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-800">Restablecer contrasena</h1>
        <Link href="/forgot-password/code" class="text-sm font-medium text-slate-600 underline">Volver</Link>
      </div>

      <p class="mb-4 text-sm text-slate-600">
        Paso 4 de 4: define una nueva contraseña para la cuenta asociada a {{ maskedPhone || 'tu telefono' }}.
      </p>

      <p v-if="status" class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ error }}
      </p>

      <form class="space-y-3" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-slate-700">Nueva contrasena</label>
          <input
            v-model="form.password"
            type="password"
            class="input input-bordered w-full"
            :class="{ 'input-error': form.errors.password }"
            autocomplete="new-password"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Confirmar contrasena</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="input input-bordered w-full"
            autocomplete="new-password"
          />
        </div>

        <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>

        <button type="submit" class="btn mt-2 w-full bg-slate-800 text-white" :disabled="form.processing">
          {{ form.processing ? 'Guardando...' : 'Restablecer contrasena' }}
        </button>
      </form>
    </section>
  </main>
</template>
