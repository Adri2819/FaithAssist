<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({});

const logout = () => {
  form.post('/logout');
};
</script>

<template>
  <Head title="Inicio" />

  <main class="min-h-screen bg-slate-100 px-4 py-10 sm:px-6 lg:px-8">
    <div
      class="mx-auto w-full max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-lg sm:p-8"
    >
      <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Area protegida</h1>
      <p class="mt-2 text-slate-600">Sesion iniciada correctamente.</p>

      <button
        type="button"
        class="btn mt-6 border-none bg-slate-900 text-white hover:bg-slate-800"
        :disabled="form.processing"
        @click="logout"
      >
        {{ form.processing ? 'Cerrando...' : 'Cerrar sesion' }}
      </button>
    </div>
  </main>
</template>
