<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { CalendarDays, Church, KeyRound, MapPinned, ShieldCheck, User, Users } from 'lucide-vue-next';
import AppShell from '../../../components/layouts/AppShell.vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import PermissionSelector from '../../../components/security/PermissionSelector.vue';

const props = defineProps({
  user:                { type: Object, default: null },
  roles:               { type: Array,  required: true },
  permissionGroups:    { type: Array,  required: true },
  municipalities:      { type: Array,  default: () => [] },
  churches:            { type: Array,  default: () => [] },
  selectedRole:        { type: Number, default: null },
  selectedPermissions: { type: Array,  default: () => [] },
  selectedMunicipalities: { type: Array,  default: () => [] },
  selectedChurches:    { type: Array,  default: () => [] },
  selectedCountryCode: { type: String, default: '521' },
  countryCodes:        { type: Array,  default: () => [] },
});

const isEditing = computed(() => !!props.user);
const pageTitle = computed(() => (isEditing.value ? `Editar Usuario` : 'Nuevo Usuario'));

const activeSection = ref('general');

const sections = [
  { key: 'general',    label: 'Datos Generales', icon: User },
  { key: 'alcance',    label: 'Alcance',         icon: MapPinned },
  { key: 'roles',      label: 'Roles',           icon: ShieldCheck },
  { key: 'permisos',   label: 'Permisos',        icon: KeyRound },
  { key: 'seguridad',  label: 'Seguridad',       icon: Users },
];

const form = useForm({
  name:                  props.user?.name    ?? '',
  paterno:               props.user?.paterno ?? '',
  materno:               props.user?.materno ?? '',
  email:                 props.user?.email   ?? '',
  whatsapp_country_code: props.user?.whatsapp_country_code ?? props.selectedCountryCode ?? '521',
  whatsapp_phone:        props.user?.whatsapp_phone ?? '',
  role_id:               props.selectedRole,
  municipality_ids:      [...props.selectedMunicipalities],
  church_ids:            [...props.selectedChurches],
  permissions:           [...props.selectedPermissions],
  password:              '',
  password_confirmation: '',
});

const selectedRoleObj = computed(() => props.roles.find((r) => r.id === form.role_id));

const totalPermissions = computed(() => form.permissions.length);
const totalMunicipalities = computed(() => form.municipality_ids.length);
const totalChurches = computed(() => form.church_ids.length);

const submit = () => {
  if (isEditing.value) {
    form.put(`/usuarios/${props.user.id}`, { preserveScroll: true });
  } else {
    form.post('/usuarios');
  }
};
</script>

<template>
  <AppShell :page-title="pageTitle">
    <CatalogHeader
      :title="pageTitle"
      subtitle="Configuracion de cuenta y permisos del usuario"
      back-href="/usuarios"
    />

    <!-- User identity header (edit mode) -->
    <div
      v-if="isEditing"
      class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="flex items-center gap-4">
        <span
          v-if="user.photo_url"
          class="h-16 w-16 overflow-hidden rounded-full border-2 border-rose-200 shadow"
        >
          <img :src="user.photo_url" :alt="user.full_name" class="h-full w-full object-cover" />
        </span>
        <span
          v-else
          class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-rose-700 text-xl font-black uppercase text-white shadow"
        >
          {{ user.initials }}
        </span>
        <div>
          <p class="text-xl font-black uppercase tracking-wide text-slate-800 dark:text-slate-100">
            {{ user.full_name }}
          </p>
          <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>
        </div>
      </div>
      <div
        v-if="user.created_at"
        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
      >
        <CalendarDays class="h-3.5 w-3.5" />
        Alta: {{ user.created_at }}
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start">

        <!-- Sidebar nav -->
        <aside class="w-full shrink-0 lg:w-52">
          <nav class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
              <li v-for="s in sections" :key="s.key">
                <button
                  type="button"
                  class="flex w-full items-center justify-between px-4 py-3 text-sm transition-colors"
                  :class="
                    activeSection === s.key
                      ? 'bg-slate-100 font-semibold text-slate-900 dark:bg-slate-800 dark:text-slate-100'
                      : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50'
                  "
                  @click="activeSection = s.key"
                >
                  <span class="flex items-center gap-2.5">
                    <component :is="s.icon" class="h-4 w-4 shrink-0" />
                    {{ s.label }}
                  </span>
                  <span
                    v-if="s.key === 'alcance' && (totalMunicipalities > 0 || totalChurches > 0)"
                    class="rounded-full bg-rose-700 px-1.5 py-0.5 text-xs font-bold text-white"
                  >
                    {{ totalMunicipalities + totalChurches }}
                  </span>
                  <span
                    v-if="s.key === 'roles' && selectedRoleObj"
                    class="rounded-full bg-rose-700 px-1.5 py-0.5 text-xs font-bold text-white"
                  >
                    1
                  </span>
                  <span
                    v-if="s.key === 'permisos' && totalPermissions > 0"
                    class="rounded-full bg-rose-700 px-1.5 py-0.5 text-xs font-bold text-white"
                  >
                    {{ totalPermissions }}
                  </span>
                </button>
              </li>
            </ul>
          </nav>
        </aside>

        <!-- Main content -->
        <div class="min-w-0 flex-1">

          <!-- Datos Generales -->
          <div v-show="activeSection === 'general'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <h2 class="mb-5 text-sm font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">
              Informacion basica de la cuenta
            </h2>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Nombre <span class="text-red-500">*</span>
                </label>
                <input v-model="form.name" type="text" placeholder="Nombre(s)" class="input input-bordered w-full" :class="{ 'input-error': form.errors.name }" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Apellido paterno <span class="text-red-500">*</span>
                </label>
                <input v-model="form.paterno" type="text" placeholder="Apellido paterno" class="input input-bordered w-full" :class="{ 'input-error': form.errors.paterno }" />
                <p v-if="form.errors.paterno" class="mt-1 text-xs text-red-500">{{ form.errors.paterno }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Apellido materno
                </label>
                <input v-model="form.materno" type="text" placeholder="Apellido materno" class="input input-bordered w-full" :class="{ 'input-error': form.errors.materno }" />
                <p v-if="form.errors.materno" class="mt-1 text-xs text-red-500">{{ form.errors.materno }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Correo electronico <span class="text-red-500">*</span>
                </label>
                <input v-model="form.email" type="email" placeholder="correo@ejemplo.com" class="input input-bordered w-full" :class="{ 'input-error': form.errors.email }" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Numero de telefono
                </label>
                <div class="flex gap-2">
                  <select
                    v-model="form.whatsapp_country_code"
                    class="select select-bordered w-28 shrink-0"
                    :class="{ 'select-error': form.errors.whatsapp_country_code }"
                  >
                    <option v-for="code in countryCodes" :key="code.value" :value="code.value">
                      {{ code.label }}
                    </option>
                  </select>
                  <input v-model="form.whatsapp_phone" type="text" placeholder="5512345678" class="input input-bordered w-full" :class="{ 'input-error': form.errors.whatsapp_phone }" />
                </div>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Este numero se usa para recuperar tu contrasena.</p>
                <p v-if="form.errors.whatsapp_country_code" class="mt-1 text-xs text-red-500">{{ form.errors.whatsapp_country_code }}</p>
                <p v-if="form.errors.whatsapp_phone" class="mt-1 text-xs text-red-500">{{ form.errors.whatsapp_phone }}</p>
              </div>
            </div>
          </div>

          <!-- Alcance -->
          <div v-show="activeSection === 'alcance'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">
              Alcance de datos
            </h2>
            <p class="mb-5 text-xs text-slate-400 dark:text-slate-500">
              Define los municipios y parroquias visibles para el usuario. Las comunidades se mostraran segun el municipio asignado.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                  <MapPinned class="h-4 w-4 text-rose-700 dark:text-rose-400" />
                  Municipios asignados
                </label>
                <select
                  v-model="form.municipality_ids"
                  multiple
                  class="select select-bordered h-48 w-full"
                  :class="{ 'select-error': form.errors.municipality_ids }"
                >
                  <option v-for="municipality in municipalities" :key="municipality.id" :value="municipality.id">
                    {{ municipality.name }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                  Usa Ctrl/Cmd + clic para seleccionar varios municipios.
                </p>
                <p v-if="form.errors.municipality_ids" class="mt-1 text-xs text-red-500">{{ form.errors.municipality_ids }}</p>
              </div>

              <div>
                <label class="mb-1.5 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                  <Church class="h-4 w-4 text-rose-700 dark:text-rose-400" />
                  Parroquias asignadas
                </label>
                <select
                  v-model="form.church_ids"
                  multiple
                  class="select select-bordered h-48 w-full"
                  :class="{ 'select-error': form.errors.church_ids }"
                >
                  <option v-for="church in churches" :key="church.id" :value="church.id">
                    {{ church.name }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                  Asigna una o varias parroquias para restringir la informacion visible.
                </p>
                <p v-if="form.errors.church_ids" class="mt-1 text-xs text-red-500">{{ form.errors.church_ids }}</p>
              </div>
            </div>
          </div>

          <!-- Roles -->
          <div v-show="activeSection === 'roles'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">
              Rol del usuario
            </h2>
            <p class="mb-5 text-xs text-slate-400 dark:text-slate-500">Solo se puede asignar un rol por usuario.</p>

            <div class="grid gap-2 sm:grid-cols-2">
              <!-- None option -->
              <button
                type="button"
                class="flex items-start gap-3 rounded-xl border px-4 py-3 text-left transition-all"
                :class="
                  form.role_id === null
                    ? 'border-rose-300 bg-rose-50 dark:border-rose-700 dark:bg-rose-900/20'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40'
                "
                @click="form.role_id = null"
              >
                <span
                  class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2"
                  :class="form.role_id === null ? 'border-rose-600' : 'border-slate-300 dark:border-slate-600'"
                >
                  <span v-if="form.role_id === null" class="h-2 w-2 rounded-full bg-rose-600" />
                </span>
                <span>
                  <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Sin rol</span>
                  <span class="block text-xs text-slate-400">No asignar ningun rol</span>
                </span>
              </button>

              <button
                v-for="role in roles"
                :key="role.id"
                type="button"
                class="flex items-start gap-3 rounded-xl border px-4 py-3 text-left transition-all"
                :class="
                  form.role_id === role.id
                    ? 'border-rose-300 bg-rose-50 dark:border-rose-700 dark:bg-rose-900/20'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40'
                "
                @click="form.role_id = role.id"
              >
                <span
                  class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2"
                  :class="form.role_id === role.id ? 'border-rose-600' : 'border-slate-300 dark:border-slate-600'"
                >
                  <span v-if="form.role_id === role.id" class="h-2 w-2 rounded-full bg-rose-600" />
                </span>
                <span>
                  <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">{{ role.name }}</span>
                  <span v-if="role.description" class="block text-xs text-slate-400 dark:text-slate-500">{{ role.description }}</span>
                </span>
              </button>
            </div>

            <p v-if="form.errors.role_id" class="mt-3 text-xs text-red-500">{{ form.errors.role_id }}</p>
          </div>

          <!-- Permisos -->
          <div v-show="activeSection === 'permisos'">
            <PermissionSelector
              v-model="form.permissions"
              :groups="permissionGroups"
            />
            <p v-if="form.errors.permissions" class="mt-2 text-xs text-red-500">{{ form.errors.permissions }}</p>
          </div>

          <!-- Seguridad -->
          <div v-show="activeSection === 'seguridad'" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">
              Seguridad
            </h2>
            <p class="mb-5 text-xs text-slate-400 dark:text-slate-500">
              {{ isEditing ? 'Deja los campos vacios para mantener la contrasena actual.' : 'Define la contrasena de acceso.' }}
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  {{ isEditing ? 'Nueva contrasena' : 'Contrasena' }}
                  <span v-if="!isEditing" class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="?"
                  autocomplete="new-password"
                  class="input input-bordered w-full"
                  :class="{ 'input-error': form.errors.password }"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Confirmar contrasena
                  <span v-if="!isEditing" class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder=""
                  autocomplete="new-password"
                  class="input input-bordered w-full"
                  :class="{ 'input-error': form.errors.password_confirmation }"
                />
                <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Form Actions -->
      <div class="mt-6 flex items-center justify-end gap-3">
        <Link href="/usuarios" class="btn btn-ghost btn-sm">
          Cancelar
        </Link>
        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
          {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar usuario' : 'Crear usuario') }}
        </button>
      </div>
    </form>
  </AppShell>
</template>
