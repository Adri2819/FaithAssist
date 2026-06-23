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

  <main class="min-h-screen bg-linear-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-8">
    <section class="mx-auto w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-slate-800">Verificación de teléfono</h1>
        <Link href="/forgot-password/email" class="text-sm font-medium text-slate-600 underline">Volver</Link>
      </div>

      <p class="mb-4 text-sm text-slate-600">
        Paso 2 de 4: confirma el teléfono asociado a tu cuenta.
      </p>

      <p v-if="email" class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-slate-700">
        <span class="block text-xs font-semibold text-slate-600 mb-1">Cuenta:</span>
        <span class="font-semibold">{{ email }}</span>
      </p>

      <p v-if="registeredPhoneLast4" class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
        Para recuperar tu contraseña, ingresa el número registrado con terminación <span class="font-semibold">{{ registeredPhoneLast4 }}</span>.
      </p>

      <p v-if="status" class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        {{ status }}
      </p>
      <p v-if="error" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ error }}
      </p>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-3">Teléfono de contacto</label>
          <p class="text-xs text-slate-600 mb-3">Asegúrate de que sea correcto para recibir el código de verificación.</p>

          <div class="flex gap-2">
            <select
              v-model="form.whatsapp_country_code"
              class="select select-bordered w-32 shrink-0"
              :class="{ 'select-error': form.errors.whatsapp_country_code }"
            >
              <option v-for="code in countryCodes" :key="code.value" :value="code.value">
                {{ code.label }}
              </option>
            </select>
            <input
              v-model="form.whatsapp_phone"
              type="text"
              class="input input-bordered w-full"
              :class="{ 'input-error': form.errors.whatsapp_phone }"
              placeholder="5512345678"
              autocomplete="tel-national"
            />
          </div>
          <p v-if="form.errors.whatsapp_country_code" class="text-xs text-red-600 mt-2">{{ form.errors.whatsapp_country_code }}</p>
          <p v-if="form.errors.whatsapp_phone" class="text-xs text-red-600 mt-2">{{ form.errors.whatsapp_phone }}</p>
        </div>

        <button type="submit" class="btn mt-4 w-full bg-slate-800 text-white" :disabled="form.processing">
          {{ form.processing ? 'Enviando código...' : 'Enviar código' }}
        </button>
      </form>
    </section>
  </main>
</template>
