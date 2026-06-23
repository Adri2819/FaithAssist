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

  <main class="min-h-screen bg-linear-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-8">
    <section class="mx-auto w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-800">Validar codigo</h1>
        <Link href="/forgot-password" class="text-sm font-medium text-slate-600 underline">Volver</Link>
      </div>

      <p class="mb-4 text-sm text-slate-600">
        Paso 3 de 4: ingresa el codigo enviado a {{ maskedPhone || 'tu telefono' }}.
      </p>

      <p v-if="status" class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ error }}
      </p>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Codigo de 6 digitos</label>
          <div class="flex justify-center gap-2">
            <input
              v-for="(digit, index) in digits"
              :key="index"
              :value="digit"
              :data-digit="index"
              type="text"
              inputmode="numeric"
              maxlength="1"
              class="h-14 w-14 text-center text-xl font-bold border-2 border-slate-300 rounded-lg focus:border-slate-800 focus:outline-none transition"
              :class="{ 'border-red-400': form.errors.code }"
              @input="handleInput(index, $event.target.value)"
              @keydown="handleKeyDown(index, $event)"
            />
          </div>
          <p v-if="form.errors.code" class="text-xs text-red-600 mt-2">{{ form.errors.code }}</p>
        </div>

        <button type="submit" class="btn mt-2 w-full bg-slate-800 text-white" :disabled="form.processing">
          {{ form.processing ? 'Validando...' : 'Validar codigo' }}
        </button>
      </form>
    </section>
  </main>
</template>
