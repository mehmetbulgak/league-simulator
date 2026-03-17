<script setup>
defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  label: {
    type: String,
    default: 'Loading',
  },
})
</script>

<template>
  <span class="spinner" :class="size" role="status" :aria-label="label" />
</template>
