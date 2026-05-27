<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppShell from '../../components/layouts/AppShell.vue';

const page = usePage();

const profile = computed(() => page.props.auth?.user?.profile ?? null);
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
  <Head title="Perfil" />

  <AppShell>
    <section
      class="mx-auto w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-lg transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900 sm:p-8"
    >
      <h1 class="text-2xl font-bold text-slate-900">Perfil</h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
        Informacion base para encabezado y sesion.
      </p>

      <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        <div
          class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
        >
          <dt
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Nombre
          </dt>
          <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ profile?.name || '-' }}
          </dd>
        </div>
        <div
          class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
        >
          <dt
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Apellido paterno
          </dt>
          <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ profile?.paterno || '-' }}
          </dd>
        </div>
        <div
          class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
        >
          <dt
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Apellido materno
          </dt>
          <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ profile?.materno || '-' }}
          </dd>
        </div>
        <div
          class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
        >
          <dt
            class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
          >
            Correo
          </dt>
          <dd class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ user?.email || '-' }}
          </dd>
        </div>
      </dl>
    </section>
  </AppShell>
</template>
