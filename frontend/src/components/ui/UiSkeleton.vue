<script setup>
defineProps({
  width: {
    type: String,
    default: '100%',
  },
  height: {
    type: String,
    default: '1em',
  },
  radius: {
    type: String,
    default: '10px',
  },
  inline: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <span
    class="skeleton"
    :class="inline ? 'skeleton-inline' : 'skeleton-block'"
    aria-hidden="true"
    :style="{ width, height, borderRadius: radius }"
  />
</template>
