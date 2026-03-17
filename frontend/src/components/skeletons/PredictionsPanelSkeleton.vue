<script setup>
import UiPanel from '../ui/UiPanel.vue'
import UiSkeleton from '../ui/UiSkeleton.vue'

defineProps({
  rows: {
    type: Number,
    default: 4,
  },
})
</script>

<template>
  <UiPanel title="Championship Predictions">
    <div aria-hidden="true">
      <div v-for="i in rows" :key="i" class="prediction-line">
        <UiSkeleton width="55%" height="16px" />
        <UiSkeleton width="34px" height="16px" />
      </div>
      <p class="muted small">
        <UiSkeleton width="220px" height="10px" />
      </p>
    </div>
  </UiPanel>
</template>
