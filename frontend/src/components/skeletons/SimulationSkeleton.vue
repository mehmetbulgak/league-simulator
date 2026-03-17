<script setup>
import AllWeeksResultsSkeleton from './AllWeeksResultsSkeleton.vue'
import LeagueTableSkeleton from './LeagueTableSkeleton.vue'
import PredictionsPanelSkeleton from './PredictionsPanelSkeleton.vue'
</script>

<template>
  <div class="stack" aria-busy="true">
    <div class="three-col">
      <LeagueTableSkeleton />
      <PredictionsPanelSkeleton />
    </div>

    <AllWeeksResultsSkeleton />
  </div>
</template>

