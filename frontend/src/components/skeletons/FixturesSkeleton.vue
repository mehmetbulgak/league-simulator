<script setup>
import UiSkeleton from '../ui/UiSkeleton.vue'

defineProps({
  weeks: {
    type: Number,
    default: 6,
  },
  matchesPerWeek: {
    type: Number,
    default: 2,
  },
})
</script>

<template>
  <div class="grid" aria-hidden="true">
    <div v-for="week in weeks" :key="week" class="panel">
      <div class="panel-title">
        <UiSkeleton width="90px" height="14px" />
      </div>
      <div class="panel-body">
        <div v-for="match in matchesPerWeek" :key="match" class="match-line">
          <UiSkeleton width="55%" height="16px" />
          <span class="vs">-</span>
          <UiSkeleton width="45%" height="16px" />
        </div>
      </div>
    </div>
  </div>
</template>
