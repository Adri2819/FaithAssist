<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
  status: { type: String, default: null },
  error: { type: String, default: null },
});

const page = usePage();

const form = useForm({
  email: computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('email') || '';
  }).value,
});

const submit = () => {
  form.post('/forgot-password/email', { preserveScroll: true });
};
</script>

<template>
  <Head title="Recuperar contraseña" />

  <main class="min-h-screen bg-linear-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-8">
    <section class="mx-auto w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-800">Recuperar contraseña</h1>
        <Link href="/login" class="text-sm font-medium text-slate-600 underline">Volver</Link>
      </div>

      <p class="mb-4 text-sm text-slate-600">
        Paso 1 de 4: ingresa el correo electrónico asociado a tu cuenta.
      </p>

      <p v-if="status" class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ error }}
      </p>

      <form class="space-y-3" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-slate-700">Correo electrónico</label>
          <input
            v-model="form.email"
            type="email"
            class="input input-bordered w-full"
            :class="{ 'input-error': form.errors.email }"
            autocomplete="email"
            placeholder="tu@email.com"
          />
          <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <button type="submit" class="btn mt-2 w-full bg-slate-800 text-white" :disabled="form.processing">
          {{ form.processing ? 'Verificando...' : 'Continuar' }}
        </button>
      </form>
    </section>
  </main>
</template>
