<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CircleHelp, Eye, EyeOff, Lock, Mail, ShieldCheck, Sparkles } from 'lucide-vue-next';

const showPassword = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Iniciar sesion" />

  <main
    class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-100 via-slate-200 to-blue-100 px-4 py-6 sm:px-6 sm:py-10 lg:px-8"
  >
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 -top-20 h-72 w-72 rounded-full bg-slate-400/35 blur-3xl"></div>
      <div class="absolute -right-16 top-1/4 h-72 w-72 rounded-full bg-blue-300/35 blur-3xl"></div>
      <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-slate-300/30 blur-3xl"></div>
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.04),_transparent_55%)]"
      ></div>
    </div>

    <div
      class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-6xl items-center justify-center"
    >
      <section
        class="card w-full max-w-xl border border-slate-200/80 bg-white/90 shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] backdrop-blur-xl"
      >
        <div class="card-body p-6 sm:p-8">
          <div class="flex flex-col items-center text-center">
            <div
              class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-blue-900 text-white shadow-lg shadow-slate-400/30"
            >
              <ShieldCheck class="h-7 w-7" />
            </div>
            <p
              class="inline-flex items-center gap-2 rounded-full bg-slate-800 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-200"
            >
              <Sparkles class="h-3.5 w-3.5" />
              FaithAssist QR
            </p>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
              <label for="email" class="text-sm font-medium text-slate-700"
                >Correo institucional</label
              >
              <div class="relative">
                <Mail
                  class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                />
                <input
                  id="email"
                  name="email"
                  type="email"
                  v-model="form.email"
                  class="input ui-input w-full pl-10"
                  :class="{ 'input-error': form.errors.email }"
                  placeholder="nombre@organizacion.org"
                  autocomplete="email"
                />
              </div>
              <p v-if="form.errors.email" class="text-sm font-medium text-red-600">
                {{ form.errors.email }}
              </p>
            </div>

            <div class="space-y-2">
              <label for="password" class="text-sm font-medium text-slate-700">Contrasena</label>
              <div class="relative">
                <Lock
                  class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                />
                <input
                  id="password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  class="input ui-input w-full pl-10 pr-12"
                  :class="{ 'input-error': form.errors.password }"
                  placeholder="Escribe tu contrasena"
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="absolute right-1.5 top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                  :aria-label="showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena'"
                  @click="showPassword = !showPassword"
                >
                  <EyeOff v-if="showPassword" class="h-4 w-4" />
                  <Eye v-else class="h-4 w-4" />
                </button>
              </div>
              <p v-if="form.errors.password" class="text-sm font-medium text-red-600">
                {{ form.errors.password }}
              </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
              <label class="label cursor-pointer justify-start gap-2 p-0">
                <input
                  type="checkbox"
                  name="remember"
                  value="1"
                  v-model="form.remember"
                  class="checkbox checkbox-sm border-slate-300"
                />
                <span class="label-text text-slate-600">Recordar mi sesion</span>
              </label>
              <a
                href="#"
                class="text-sm font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-blue-900"
              >
                Olvide mi contrasena
              </a>
            </div>

            <button
              type="submit"
              class="btn h-12 w-full border-none bg-gradient-to-r from-slate-700 to-blue-900 text-white shadow-lg shadow-slate-400/40 transition hover:from-slate-800 hover:to-blue-950"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Ingresando...' : 'Entrar al sistema' }}
            </button>
          </form>

          <div class="divider my-6 text-xs uppercase tracking-[0.2em] text-slate-400">soporte</div>

          <p class="flex items-center justify-center gap-2 text-center text-sm text-slate-600">
            <CircleHelp class="h-4 w-4 text-slate-500" />
            Si no puedes ingresar, contacta a mesa de ayuda.
          </p>
        </div>
      </section>
    </div>
  </main>
</template>
