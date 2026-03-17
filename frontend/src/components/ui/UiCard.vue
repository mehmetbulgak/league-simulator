<script setup>
defineProps({
  as: {
    type: String,
    default: 'section',
  },
})
</script>

<template>
  <component :is="as" class="card">
    <div v-if="$slots.header" class="card-header">
      <slot name="header" />
    </div>
    <slot />
  </component>
</template>

