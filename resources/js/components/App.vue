<script setup>
import Login from '../Pages/Auth/Login.vue';

const currentPath = window.location.pathname;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
</script>

<template>
  <Login v-if="currentPath === '/login'" />

  <main v-else class="min-h-screen bg-slate-100 px-4 py-10 sm:px-6 lg:px-8">
    <div
      class="mx-auto w-full max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-lg sm:p-8"
    >
      <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Area protegida</h1>
      <p class="mt-2 text-slate-600">Sesion iniciada correctamente.</p>

      <form class="mt-6" method="POST" action="/logout">
        <input type="hidden" name="_token" :value="csrfToken" />
        <button type="submit" class="btn border-none bg-slate-900 text-white hover:bg-slate-800">
          Cerrar sesion
        </button>
      </form>
    </div>
  </main>
</template>
