<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, KeyRound, MessageCircleMore } from 'lucide-vue-next';
import { useTheme } from '../../composables/useTheme';

const props = defineProps({
  status: {
    type: String,
    default: null,
  },
  maskedPhone: {
    type: String,
    default: null,
  },
  recoveryPhone: {
    type: String,
    default: '',
  },
  recoveryCountryCode: {
    type: String,
    default: '521',
  },
  countryCodes: {
    type: Array,
    default: () => [],
  },
});

const { isDark, toggleTheme } = useTheme();

const sendCodeForm = useForm({
  whatsapp_country_code: props.recoveryCountryCode,
  whatsapp_phone: props.recoveryPhone,
});

const resetForm = useForm({
  whatsapp_country_code: props.recoveryCountryCode,
  whatsapp_phone: props.recoveryPhone,
  code: '',
  password: '',
  password_confirmation: '',
});

const hasCodeStatus = computed(() => !!props.status);

const sendCode = () => {
  sendCodeForm.post('/forgot-password/send-code', {
    preserveScroll: true,
  });
};

const resetPassword = () => {
  resetForm.post('/forgot-password/reset', {
    preserveScroll: true,
    onError: () => {
      if (!resetForm.whatsapp_phone) {
        resetForm.whatsapp_phone = sendCodeForm.whatsapp_phone;
      }
    },
  });
};
</script>

<template>
  <Head title="Recuperar contrasena" />

  <main
    class="relative min-h-screen overflow-hidden bg-linear-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-6 transition-colors duration-300 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 sm:px-6 sm:py-10 lg:px-8"
  >
    <div class="absolute right-4 top-4 z-10 sm:right-6 sm:top-6">
      <button
        type="button"
        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-300 bg-white/90 text-slate-600 shadow-sm backdrop-blur transition hover:border-slate-400 hover:bg-white dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800"
        :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        @click="toggleTheme"
      >
        <span class="text-xs font-bold">{{ isDark ? 'CL' : 'OS' }}</span>
      </button>
    </div>

    <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-5xl items-center justify-center">
      <section
        class="w-full max-w-3xl rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur-xl transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/90 sm:p-8"
      >
        <div class="mb-6 flex items-center justify-between gap-4">
          <h1 class="text-xl font-black tracking-tight text-slate-800 dark:text-slate-100 sm:text-2xl">
            Recuperar contrasena por WhatsApp
          </h1>
          <Link
            href="/login"
            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:border-slate-400 hover:text-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-slate-100"
          >
            <ArrowLeft class="h-4 w-4" />
            Volver
          </Link>
        </div>

        <div
          v-if="status"
          class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
          <p>{{ status }}</p>
          <p v-if="maskedPhone" class="mt-1 font-semibold">Destino: {{ maskedPhone }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <form class="rounded-2xl border border-slate-200 p-5" @submit.prevent="sendCode">
            <div class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-slate-600">
              <MessageCircleMore class="h-4 w-4" />
              1) Solicitar codigo
            </div>

            <label class="mb-1.5 block text-sm font-medium text-slate-700">Numero de telefono</label>
            <div class="flex gap-2">
              <select v-model="sendCodeForm.whatsapp_country_code" class="select select-bordered w-28 shrink-0" autocomplete="tel-country-code">
                <option v-for="code in countryCodes" :key="code.value" :value="code.value">{{ code.label }}</option>
              </select>
              <input
                v-model="sendCodeForm.whatsapp_phone"
                type="text"
                class="input ui-input w-full"
                placeholder="5512345678"
                autocomplete="tel-national"
              />
            </div>
            <p class="mt-1 text-xs text-slate-400">Selecciona la lada y escribe solo el numero local.</p>
            <p v-if="sendCodeForm.errors.whatsapp_phone" class="mt-1 text-xs text-red-500">{{ sendCodeForm.errors.whatsapp_phone }}</p>
            <p v-if="sendCodeForm.errors.whatsapp_country_code" class="mt-1 text-xs text-red-500">{{ sendCodeForm.errors.whatsapp_country_code }}</p>

            <button
              type="submit"
              class="btn mt-4 h-11 w-full border-none bg-linear-to-r from-slate-700 to-blue-900 text-white"
              :disabled="sendCodeForm.processing"
            >
              {{ sendCodeForm.processing ? 'Enviando...' : 'Enviar codigo por WhatsApp' }}
            </button>
          </form>

          <form class="rounded-2xl border border-slate-200 p-5" @submit.prevent="resetPassword">
            <div class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-slate-600">
              <KeyRound class="h-4 w-4" />
              2) Validar y cambiar contrasena
            </div>

            <div class="space-y-3">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Numero de telefono</label>
                <div class="flex gap-2">
                  <select v-model="resetForm.whatsapp_country_code" class="select select-bordered w-28 shrink-0" autocomplete="tel-country-code">
                    <option v-for="code in countryCodes" :key="code.value" :value="code.value">{{ code.label }}</option>
                  </select>
                  <input v-model="resetForm.whatsapp_phone" type="text" class="input ui-input w-full" autocomplete="tel-national" placeholder="5512345678" />
                </div>
                <p class="mt-1 text-xs text-slate-400">Selecciona la lada y escribe solo el numero local.</p>
                <p v-if="resetForm.errors.whatsapp_phone" class="mt-1 text-xs text-red-500">{{ resetForm.errors.whatsapp_phone }}</p>
                <p v-if="resetForm.errors.whatsapp_country_code" class="mt-1 text-xs text-red-500">{{ resetForm.errors.whatsapp_country_code }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Codigo de 6 digitos</label>
                <input v-model="resetForm.code" type="text" maxlength="6" class="input ui-input w-full" placeholder="123456" autocomplete="one-time-code" />
                <p v-if="resetForm.errors.code" class="mt-1 text-xs text-red-500">{{ resetForm.errors.code }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nueva contrasena</label>
                <input v-model="resetForm.password" type="password" class="input ui-input w-full" autocomplete="new-password" />
                <p v-if="resetForm.errors.password" class="mt-1 text-xs text-red-500">{{ resetForm.errors.password }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirmar contrasena</label>
                <input v-model="resetForm.password_confirmation" type="password" class="input ui-input w-full" autocomplete="new-password" />
              </div>
            </div>

            <button
              type="submit"
              class="btn mt-4 h-11 w-full border-none bg-slate-800 text-white"
              :disabled="resetForm.processing || !hasCodeStatus"
            >
              {{ resetForm.processing ? 'Actualizando...' : 'Restablecer contrasena' }}
            </button>

            <p class="mt-2 text-xs text-slate-500">
              Primero solicita el codigo. El boton se habilita cuando el codigo fue enviado.
            </p>
          </form>
        </div>
      </section>
    </div>
  </main>
</template>
