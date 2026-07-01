<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { Home } from 'lucide-vue-next';
import { computed } from 'vue';
import CatalogHeader from '../../../components/catalogs/CatalogHeader.vue';
import UnderlineField from '../../../components/forms/UnderlineField.vue';
import UnderlineSection from '../../../components/forms/UnderlineSection.vue';
import AppShell from '../../../components/layouts/AppShell.vue';

const props = defineProps({
  church: { type: Object, default: null },
  municipalities: { type: Array, default: () => [] },
  deaneries: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
});

const isEditing = computed(() => !!props.church);
const pageTitle = computed(() => (isEditing.value ? 'Editar parroquia' : 'Nueva parroquia'));
const defaultMunicipalityId = computed(
  () =>
    props.church?.municipality_id ??
    (props.municipalities.length === 1 ? props.municipalities[0].id : null),
);

const form = useForm({
  municipality_id: defaultMunicipalityId.value,
  deanery_id: props.church?.deanery_id ?? null,
  name: props.church?.name ?? '',
  alias: props.church?.alias ?? '',
  email: props.church?.email ?? '',
  phone: props.church?.phone ?? '',
  address: props.church?.address ?? '',
  status: props.church?.status ?? 'active',
});

const municipalityOptions = computed(() =>
  props.municipalities.map((municipality) => ({
    value: municipality.id,
    label: municipality.name,
  })),
);

const deaneryOptions = computed(() =>
  props.deaneries.map((deanery) => ({
    value: deanery.id,
    label: deanery.name,
  })),
);

const submit = () => {
  if (isEditing.value) {
    form.put(`/parroquias/${props.church.id}`, { preserveScroll: true });
  } else {
    form.post('/parroquias');
  }
};
</script>

<template>
  <AppShell :page-title="pageTitle">
    <CatalogHeader
      :title="pageTitle"
      subtitle="Datos generales y ubicación eclesiástica de la parroquia"
      back-href="/parroquias"
      :icon="Home"
    />

    <form @submit.prevent="submit">
      <div
        class="mb-6 rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-slate-700 dark:bg-slate-900/70 sm:p-8"
      >
        <div class="space-y-9">
          <UnderlineSection title="Ubicación">
            <div class="grid gap-x-9 gap-y-7 md:grid-cols-2">
              <UnderlineField
                v-model="form.municipality_id"
                label="Municipio"
                as="select"
                placeholder="Selecciona un municipio..."
                :options="municipalityOptions"
                :error="form.errors.municipality_id"
                number-value
                required
              />
              <UnderlineField
                v-model="form.deanery_id"
                label="Decanato"
                as="select"
                placeholder="Sin decanato"
                :options="deaneryOptions"
                :error="form.errors.deanery_id"
                number-value
              />
            </div>
          </UnderlineSection>

          <UnderlineSection title="Datos generales">
            <div class="grid gap-x-9 gap-y-7 md:grid-cols-2">
              <UnderlineField
                v-model="form.name"
                label="Nombre"
                :error="form.errors.name"
                required
              />
              <UnderlineField v-model="form.alias" label="Alias" :error="form.errors.alias" />
              <UnderlineField
                v-model="form.status"
                label="Estado"
                as="select"
                :options="statuses"
                :error="form.errors.status"
                required
              />
            </div>
          </UnderlineSection>

          <UnderlineSection title="Contacto">
            <div class="grid gap-x-9 gap-y-7 md:grid-cols-2">
              <UnderlineField
                v-model="form.email"
                label="Correo electrónico"
                type="email"
                placeholder="nombre@correo.com"
                :error="form.errors.email"
              />
              <UnderlineField
                v-model="form.phone"
                label="Teléfono"
                placeholder="Ej. 5512345678"
                :error="form.errors.phone"
              />
              <div class="md:col-span-2">
                <UnderlineField
                  v-model="form.address"
                  label="Dirección"
                  as="textarea"
                  placeholder="Calle, número, colonia o referencias"
                  :error="form.errors.address"
                />
              </div>
            </div>
          </UnderlineSection>
        </div>
      </div>

      <div class="mx-auto mt-6 flex max-w-6xl items-center justify-end gap-3">
        <Link href="/parroquias" class="btn btn-ghost btn-sm">Cancelar</Link>
        <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
          {{
            form.processing
              ? 'Guardando...'
              : isEditing
                ? 'Actualizar parroquia'
                : 'Crear parroquia'
          }}
        </button>
      </div>
    </form>
  </AppShell>
</template>
