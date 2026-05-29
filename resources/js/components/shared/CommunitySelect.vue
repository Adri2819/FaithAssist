<script setup>
defineProps({
  modelValue: { type: [Number, String], default: null },
  communities: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Seleccionar comunidad...' },
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
    <option v-for="community in communities" :key="community.id" :value="community.id">
      {{ community.name }}
    </option>
  </select>
</template>
