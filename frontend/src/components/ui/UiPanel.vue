<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="panel">
    <div v-if="title || $slots.title" class="panel-title">
      <slot name="title">{{ title }}</slot>
    </div>
    <div class="panel-body">
      <slot />
    </div>
  </div>
</template>

