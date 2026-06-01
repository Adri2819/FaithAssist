<script setup>
defineProps({
  modelValue: { type: String, default: null },
  modules: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Seleccionar modulo...' },
  size: { type: String, default: 'sm' },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const handleChange = (event) => {
  const val = event.target.value;
  emit('update:modelValue', val || null);
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
    <option v-for="mod in modules" :key="mod.key" :value="mod.key">
      {{ mod.name }}
    </option>
  </select>
</template>
