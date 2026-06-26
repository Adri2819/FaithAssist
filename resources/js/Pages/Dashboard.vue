<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
  ArrowLeftRight,
  BookOpen,
  Building2,
  CalendarDays,
  Home,
  KeyRound,
  Landmark,
  LayoutGrid,
  MapPinned,
  MessageCircle,
  ShieldCheck,
  Tags,
  Users,
} from 'lucide-vue-next';
import AppShell from '../components/layouts/AppShell.vue';

const page = usePage();

const permissions = computed(() => page.props.auth?.permissions ?? []);

const hasPermission = (item) => {
  if (item.permission) {
    return permissions.value.includes(item.permission);
  }

  const moduleKey = item.moduleKey;

  if (!moduleKey) {
    return true;
  }

  return permissions.value.includes(`${moduleKey}.show`);
};

const modules = computed(() =>
  [
    {
      name: 'Regiones',
      accent: 'from-slate-400 via-slate-300 to-slate-200',
      titleClass: 'text-sky-700',
      items: [
        {
          label: 'Estados',
          icon: MapPinned,
          href: '/estados',
          moduleKey: 'estados',
        },
        {
          label: 'Municipios',
          icon: Building2,
          href: '/municipios',
          moduleKey: 'municipios',
        },
        {
          label: 'Comunidades',
          icon: Users,
          href: '/comunidades',
          moduleKey: 'comunidades',
        },
      ],
    },
    {
      name: 'Eclesiasticos',
      accent: 'from-blue-200 via-sky-100 to-slate-100',
      titleClass: 'text-sky-700',
      items: [
        {
          label: 'Diocesis',
          icon: Landmark,
          href: '/diocesis',
          moduleKey: 'diocesis',
        },
        {
          label: 'Decanatos',
          icon: BookOpen,
          href: '/decanatos',
          moduleKey: 'decanato',
        },
        {
          label: 'Parroquias',
          icon: Home,
          href: '/parroquias',
          moduleKey: 'parroquias',
        },
        {
          label: 'Iglesias',
          icon: Landmark,
          href: '/capillas',
          moduleKey: 'capillas',
        },
      ],
    },
    {
      name: 'Operación',
      accent: 'from-blue-200 via-sky-100 to-slate-100',
      titleClass: 'text-sky-700',
      items: [
        { label: 'Periodos', icon: CalendarDays, href: '/periodos', moduleKey: 'periodos' },
        {
          label: 'Tipos de Movimiento del Periodo',
          icon: Tags,
          href: '/tipos-movimientos-periodo',
          moduleKey: 'tipos_movimientos_periodo',
        },
        {
          label: 'Movimientos del Periodo',
          icon: ArrowLeftRight,
          href: '/periodo-movimientos',
          moduleKey: 'periodo_movimientos',
        },
        { label: 'Niveles', icon: LayoutGrid, href: '/niveles', moduleKey: 'niveles' },
      ],
    },
    {
      name: 'Seguridad',
      accent: 'from-blue-200 via-sky-100 to-slate-100',
      titleClass: 'text-sky-700',
      items: [
        {
          label: 'Modulos',
          icon: LayoutGrid,
          href: '/modulos',
          moduleKey: 'modulos',
        },
        {
          label: 'Permisos',
          icon: KeyRound,
          href: '/permisos',
          moduleKey: 'permisos',
        },
        {
          label: 'Roles',
          icon: ShieldCheck,
          href: '/roles',
          moduleKey: 'roles',
        },
        {
          label: 'Usuarios',
          icon: Users,
          href: '/usuarios',
          moduleKey: 'usuarios',
        },
      ],
    },
    {
      name: 'Comunicación',
      accent: 'from-green-200 via-emerald-100 to-slate-100',
      titleClass: 'text-sky-700',
      items: [
        {
          label: 'WhatsApp',
          icon: MessageCircle,
          href: '/whatsapp',
          moduleKey: 'whatsapp',
          permission: 'whatsapp.send',
        },
      ],
    },
  ]
    .map((module) => ({
      ...module,
      items: module.items.filter((item) => hasPermission(item)),
    }))
    .filter((module) => module.items.length > 0),
);
</script>

<template>
  <AppShell :page-title="'Inicio'">
    <div class="mx-auto grid w-full max-w-7xl gap-6 xl:grid-cols-2">
      <article v-for="module in modules" :key="module.name" class="ui-dashboard-card">
        <header class="ui-dashboard-card-header" :class="module.accent">
          <h2
            class="text-2xl font-black tracking-tight dark:text-slate-50"
            :class="module.titleClass"
          >
            {{ module.name }}
          </h2>
        </header>

        <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-4">
          <component
            :is="item.href ? Link : 'button'"
            v-for="item in module.items"
            :key="item.label"
            v-bind="item.href ? { href: item.href } : { type: 'button' }"
            class="ui-module-tile group"
            :class="{
              'opacity-50 cursor-not-allowed hover:translate-y-0 hover:shadow-none': !item.href,
            }"
          >
            <span class="ui-module-icon">
              <component :is="item.icon" class="h-5 w-5" />
            </span>

            <span
              class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200"
            >
              {{ item.label }}
            </span>
          </component>
        </div>
      </article>
    </div>
  </AppShell>
</template>
