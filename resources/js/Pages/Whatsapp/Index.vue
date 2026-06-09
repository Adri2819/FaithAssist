<template>
    <AppShell>
        <div class="max-w-7xl mx-auto w-full px-4 py-8">
            <div class="mb-8 flex flex-col gap-2">
                <h1 class="text-3xl font-black text-[#0066a6]">
                    Envío por WhatsApp
                </h1>

                <p class="text-slate-500">
                    Envía gafetes PDF directamente al WhatsApp del padre o tutor.
                </p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1.4fr_0.8fr] gap-8">
                <section class="bg-white rounded-[28px] border border-slate-200 shadow-xl shadow-slate-200/70 overflow-hidden">
                    <div class="px-8 py-7 border-b border-slate-200 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black text-[#0066a6]">
                                Enviar gafete PDF
                            </h2>

                            <p class="text-slate-500 mt-1">
                                Selecciona el archivo PDF y escribe el número autorizado.
                            </p>
                        </div>

                        <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shadow-sm border border-green-100">
                            <i class="pi pi-whatsapp text-3xl"></i>
                        </div>
                    </div>

                    <form class="p-8 space-y-6" @submit.prevent="sendWhatsapp">
                        <div>
                            <label class="block text-sm font-extrabold text-slate-700 mb-2">
                                Teléfono del destinatario
                            </label>

                            <input
                                v-model="form.to_phone"
                                type="text"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 outline-none transition focus:bg-white focus:border-[#0066a6] focus:ring-4 focus:ring-blue-100"
                                placeholder="Ejemplo: 527224978399"
                                required
                            >

                            <p class="text-xs text-slate-500 mt-2">
                                Usa formato internacional sin +, espacios ni guiones.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-extrabold text-slate-700 mb-2">
                                Mensaje
                            </label>

                            <textarea
                                v-model="form.caption"
                                rows="4"
                                class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 outline-none transition resize-none focus:bg-white focus:border-[#0066a6] focus:ring-4 focus:ring-blue-100"
                                placeholder="Te compartimos el gafete en PDF."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-extrabold text-slate-700 mb-2">
                                Archivo PDF
                            </label>

                            <label class="flex items-center gap-4 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-5 py-5 cursor-pointer transition hover:border-[#0066a6] hover:bg-blue-50/40">
                                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 shadow-sm">
                                    <i class="pi pi-file-pdf text-xl"></i>
                                </div>

                                <div>
                                    <p class="font-extrabold text-slate-700">
                                        {{ fileName || 'Seleccionar archivo PDF' }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        Tamaño máximo configurado: 20 MB
                                    </p>
                                </div>

                                <input
                                    type="file"
                                    accept="application/pdf"
                                    class="hidden"
                                    required
                                    @change="handleFile"
                                >
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full rounded-2xl bg-[#0066a6] px-5 py-4 text-white font-black transition hover:bg-[#00558b] disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {{ loading ? 'Enviando PDF...' : 'Enviar PDF por WhatsApp' }}
                        </button>

                        <div
                            v-if="successMessage"
                            class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-700 font-bold"
                        >
                            {{ successMessage }}
                        </div>

                        <div
                            v-if="errorMessage"
                            class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 font-bold"
                        >
                            {{ errorMessage }}
                        </div>
                    </form>
                </section>

                <aside class="space-y-8">
                    <section class="bg-white rounded-[28px] border border-slate-200 shadow-xl shadow-slate-200/70 overflow-hidden">
                        <div class="px-7 py-6 border-b border-slate-200">
                            <h3 class="text-xl font-black text-[#0066a6]">
                                Estado de conexión
                            </h3>
                        </div>

                        <div class="p-7">
                            <div class="flex items-center gap-3 rounded-2xl bg-green-50 border border-green-100 px-4 py-4">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>

                                <div>
                                    <p class="font-black text-green-700">
                                        API conectada
                                    </p>

                                    <p class="text-sm text-green-700/80">
                                        Meta WhatsApp Cloud API disponible.
                                    </p>
                                </div>
                            </div>

                            <ul class="mt-6 space-y-3 text-sm text-slate-600">
                                <li class="flex gap-2">
                                    <span class="font-black text-[#0066a6]">•</span>
                                    El número debe estar autorizado en Meta si usas número de prueba.
                                </li>

                                <li class="flex gap-2">
                                    <span class="font-black text-[#0066a6]">•</span>
                                    El PDF debe ser válido y menor al límite configurado.
                                </li>

                                <li class="flex gap-2">
                                    <span class="font-black text-[#0066a6]">•</span>
                                    Si no llega, responde “Hola” al chat de prueba y vuelve a enviar.
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section class="bg-white rounded-[28px] border border-slate-200 shadow-xl shadow-slate-200/70 overflow-hidden">
                        <div class="px-7 py-6 border-b border-slate-200 flex items-center justify-between gap-4">
                            <h3 class="text-xl font-black text-[#0066a6]">
                                Últimos envíos
                            </h3>

                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-black text-[#0066a6] hover:bg-blue-50"
                                @click="loadHistory"
                            >
                                Actualizar
                            </button>
                        </div>

                        <div class="p-7">
                            <div
                                v-if="history.length === 0"
                                class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-5 text-center text-slate-500"
                            >
                                No hay envíos registrados.
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="item in history"
                                    :key="item.id"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <p class="font-black text-slate-700">
                                            {{ item.to_phone }}
                                        </p>

                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ formatDate(item.created_at) }}
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-black uppercase"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AppShell>
</template>

<script>
import AppShell from '@/components/layouts/AppShell.vue';

export default {
    name: 'WhatsappIndex',

    components: {
        AppShell,
    },

    data() {
        return {
            loading: false,
            successMessage: '',
            errorMessage: '',
            fileName: '',
            history: [],
            form: {
                to_phone: '',
                caption: 'Te compartimos el gafete en PDF.',
                pdf: null,
            },
        };
    },

    mounted() {
        this.loadHistory();
    },

    methods: {
        handleFile(event) {
            const file = event.target.files[0];

            this.form.pdf = file;
            this.fileName = file ? file.name : '';
        },

        async sendWhatsapp() {
            this.loading = true;
            this.successMessage = '';
            this.errorMessage = '';

            if (!this.form.pdf) {
                this.errorMessage = 'Selecciona un archivo PDF antes de enviar.';
                this.loading = false;
                return;
            }

            const formData = new FormData();
            formData.append('to_phone', this.form.to_phone);
            formData.append('caption', this.form.caption);
            formData.append('pdf', this.form.pdf);

            try {
                const response = await fetch('/whatsapp/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                this.successMessage = data.message || 'PDF enviado correctamente por WhatsApp.';

                this.form.pdf = null;
                this.fileName = '';

                await this.loadHistory();
            } catch (error) {
                console.error('Error al enviar WhatsApp:', error);

                this.errorMessage =
                    error.error ||
                    error.message ||
                    'No se pudo enviar el PDF por WhatsApp.';
            } finally {
                this.loading = false;
            }
        },

        async loadHistory() {
            try {
                const response = await fetch('/whatsapp/history-json', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar el historial.');
                }

                this.history = await response.json();
            } catch (error) {
                console.error('Error al cargar historial:', error);

                this.history = [];
            }
        },

        formatDate(date) {
            if (!date) {
                return '';
            }

            return new Date(date).toLocaleString('es-MX', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        statusClass(status) {
            if (status === 'sent') {
                return 'bg-green-100 text-green-700';
            }

            if (status === 'failed') {
                return 'bg-red-100 text-red-700';
            }

            return 'bg-yellow-100 text-yellow-700';
        },
    },
};
</script>
