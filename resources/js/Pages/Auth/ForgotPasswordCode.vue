<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
  status: { type: String, default: null },
  error: { type: String, default: null },
  maskedPhone: { type: String, default: '' },
});

const digits = ref(['', '', '', '', '', '']);
const form = useForm({ code: '' });

const handleInput = (index, value) => {
  if (!/^\d*$/.test(value)) {
    digits.value[index] = '';
    return;
  }

  digits.value[index] = value.slice(-1);

  if (value && index < 5) {
    const nextInput = document.querySelector(`input[data-digit="${index + 1}"]`);
    nextInput?.focus();
  }
};

const handleKeyDown = (index, event) => {
  if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
    const prevInput = document.querySelector(`input[data-digit="${index - 1}"]`);
    prevInput?.focus();
  }
};

const submit = () => {
  form.code = digits.value.join('');
  form.post('/forgot-password/code', { preserveScroll: true });
};
</script>

<template>
  <Head title="Validar codigo" />

  <main class="relative min-h-screen overflow-hidden bg-slate-100 px-4 py-10 transition-colors duration-300 dark:bg-slate-950">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-0 h-72 w-72 rounded-full bg-cyan-500/15 blur-3xl dark:bg-cyan-500/20" />
      <div class="absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-blue-500/20" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(15,23,42,0.08),transparent_55%)] dark:bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_55%)]" />
    </div>

    <section class="relative mx-auto w-full max-w-xl rounded-3xl border border-slate-200 bg-white/95 p-7 shadow-2xl backdrop-blur dark:border-white/15 dark:bg-slate-900/85 sm:p-8">
      <div class="mb-6 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">FaithAssist</p>
        <Link href="/forgot-password" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Volver</Link>
      </div>

      <div class="mb-6">
        <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">3</span>
          Paso 3 de 4
        </div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">Validar código</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Ingresa el código de seis dígitos enviado a {{ maskedPhone || 'tu teléfono' }}.</p>
      </div>

      <div class="mb-6 grid grid-cols-4 gap-2">
        <div class="h-1.5 rounded-full bg-slate-900 dark:bg-slate-100" />
        <div class="h-1.5 rounded-full bg-slate-900 dark:bg-slate-100" />
        <div class="h-1.5 rounded-full bg-slate-900 dark:bg-slate-100" />
        <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-700" />
      </div>

      <p v-if="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-medium text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-medium text-red-700">
        {{ error }}
      </p>

      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Código de 6 dígitos</label>
          <div class="flex justify-center gap-2 sm:gap-3">
            <input
              v-for="(digit, index) in digits"
              :key="index"
              :value="digit"
              :data-digit="index"
              type="text"
              inputmode="numeric"
              maxlength="1"
              class="h-14 w-12 rounded-xl border-2 border-slate-300 bg-white text-center text-xl font-black text-slate-900 shadow-sm transition focus:border-slate-800 focus:outline-none dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 sm:w-14"
              :class="{ 'border-red-400': form.errors.code }"
              @input="handleInput(index, $event.target.value)"
              @keydown="handleKeyDown(index, $event)"
            />
          </div>
          <p v-if="form.errors.code" class="mt-2 text-center text-xs font-medium text-red-600">{{ form.errors.code }}</p>
        </div>

        <button type="submit" class="btn h-12 w-full rounded-xl border-none bg-slate-900 text-sm font-semibold tracking-wide text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200" :disabled="form.processing">
          {{ form.processing ? 'Validando...' : 'Validar código' }}
        </button>
      </form>
    </section>
  </main>
</template>
