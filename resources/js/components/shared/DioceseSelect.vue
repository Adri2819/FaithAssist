<script setup>
defineProps({
  modelValue: { type: [Number, String], default: null },
  dioceses: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Seleccionar diocesis...' },
  size: { type: String, default: 'sm' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const handleChange = (event) => {
  const val = event.target.value;
  emit('update:modelValue', val ? Number(val) : null);
};
</script>

<template>
  <select
    :value="modelValue ?? ''"
    :disabled="disabled"
    :class="[
      'select select-bordered w-full',
      size === 'sm' ? 'select-sm' : size === 'lg' ? 'select-lg' : ''
    ]"
    @change="handleChange"
  >
    <option value="">{{ placeholder }}</option>
    <option v-for="diocese in dioceses" :key="diocese.id" :value="diocese.id">
      {{ diocese.name }}
    </option>
  </select>
</template>
