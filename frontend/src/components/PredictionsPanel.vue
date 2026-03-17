<script setup>
import UiPanel from './ui/UiPanel.vue'

defineProps({
  predictions: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <UiPanel title="Championship Predictions">
    <div v-if="predictions.length === 0" class="muted">
      Predictions will appear from Week 4+ (last 3 weeks).
    </div>
    <template v-else>
      <div v-for="p in predictions" :key="p.teamId" class="prediction-line">
        <span class="team">{{ p.teamName }}</span>
        <span class="num">{{ p.percentage }}%</span>
      </div>
      <p class="muted small">Calculated from Week 4+ (last 3 weeks) using Monte Carlo.</p>
    </template>
  </UiPanel>
</template>
