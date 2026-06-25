<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  status: { type: String, default: null },
  error: { type: String, default: null },
  countryCodes: { type: Array, default: () => [] },
  countryCode: { type: String, default: '521' },
  phone: { type: String, default: '' },
  email: { type: String, default: '' },
  registeredPhoneLast4: { type: String, default: '' },
});

const form = useForm({
  whatsapp_country_code: props.countryCode,
  whatsapp_phone: props.phone,
});

const submit = () => {
  form.post('/forgot-password', { preserveScroll: true });
};
</script>

<template>
  <Head title="Verificación de teléfono" />

  <main class="relative min-h-screen overflow-hidden bg-slate-100 px-4 py-10 transition-colors duration-300 dark:bg-slate-950">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-0 h-72 w-72 rounded-full bg-cyan-500/15 blur-3xl dark:bg-cyan-500/20" />
      <div class="absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-blue-500/20" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(15,23,42,0.08),transparent_55%)] dark:bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_55%)]" />
    </div>

    <section class="relative mx-auto w-full max-w-xl rounded-3xl border border-slate-200 bg-white/95 p-7 shadow-2xl backdrop-blur dark:border-white/15 dark:bg-slate-900/85 sm:p-8">
      <div class="mb-6 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">FaithAssist</p>
        <Link href="/forgot-password/email" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Volver</Link>
      </div>

      <div class="mb-6">
        <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">2</span>
          Paso 2 de 4
        </div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">Verificación de teléfono</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Confirma el número para enviarte el código de seguridad.</p>
      </div>

      <div class="mb-6 grid grid-cols-4 gap-2">
        <div class="h-1.5 rounded-full bg-slate-900 dark:bg-slate-100" />
        <div class="h-1.5 rounded-full bg-slate-900 dark:bg-slate-100" />
        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-700" />
        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-700" />
      </div>

      <p v-if="email" class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2.5 text-sm text-slate-700">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cuenta</span>
        <span class="font-semibold">{{ email }}</span>
      </p>

      <p v-if="registeredPhoneLast4" class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
        Ingresa el número registrado con terminación <span class="font-semibold">{{ registeredPhoneLast4 }}</span>.
      </p>

      <p v-if="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-medium text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-medium text-red-700">
        {{ error }}
      </p>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono de contacto</label>
          <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">Asegúrate de que sea correcto para recibir el código de verificación.</p>

          <div class="flex gap-2">
            <select
              v-model="form.whatsapp_country_code"
              class="select select-bordered h-12 w-32 shrink-0 rounded-xl border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              :class="{ 'select-error': form.errors.whatsapp_country_code }"
            >
              <option v-for="code in countryCodes" :key="code.value" :value="code.value">
                {{ code.label }}
              </option>
            </select>
            <input
              v-model="form.whatsapp_phone"
              type="text"
              class="input input-bordered h-12 w-full rounded-xl border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              :class="{ 'input-error': form.errors.whatsapp_phone }"
              placeholder="5512345678"
              autocomplete="tel-national"
            />
          </div>
          <p v-if="form.errors.whatsapp_country_code" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.whatsapp_country_code }}</p>
          <p v-if="form.errors.whatsapp_phone" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.whatsapp_phone }}</p>
        </div>

        <button type="submit" class="btn h-12 w-full rounded-xl border-none bg-slate-900 text-sm font-semibold tracking-wide text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200" :disabled="form.processing">
          {{ form.processing ? 'Enviando código...' : 'Enviar código' }}
        </button>
      </form>
    </section>
  </main>
</template>
