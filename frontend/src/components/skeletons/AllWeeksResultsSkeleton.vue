<script setup>
import UiPanel from '../ui/UiPanel.vue'
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
  <UiPanel title="All Weeks Results">
    <div class="weeks-tabs" aria-hidden="true">
      <div v-for="i in weeks" :key="i" class="weeks-tab">
        <UiSkeleton width="62px" height="18px" />
        <UiSkeleton width="38px" height="23px" radius="999px" />
      </div>
    </div>

    <div class="weeks-content" aria-hidden="true">
      <div v-for="i in matchesPerWeek" :key="i" class="match-row">
        <span class="team home"><UiSkeleton width="70%" height="16px" /></span>
        <span class="score"><UiSkeleton width="48px" height="24px" /></span>
        <span class="team away right"><UiSkeleton width="65%" height="16px" /></span>
        <span class="row-actions">
          <UiSkeleton width="54px" height="32px" radius="9px" />
        </span>
      </div>
    </div>
  </UiPanel>
</template>
