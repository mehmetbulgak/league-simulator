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
  <UiPanel title="League Table">
    <div class="table-wrap" aria-hidden="true">
      <table class="table compact">
        <thead>
          <tr>
            <th>Team</th>
            <th class="num">P</th>
            <th class="num">W</th>
            <th class="num">D</th>
            <th class="num">L</th>
            <th class="num">GD</th>
            <th class="num">PTS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in rows" :key="i">
            <td><UiSkeleton width="60%" height="14px" /></td>
            <td class="num"><UiSkeleton width="16px" height="14px" /></td>
            <td class="num"><UiSkeleton width="16px" height="14px" /></td>
            <td class="num"><UiSkeleton width="16px" height="14px" /></td>
            <td class="num"><UiSkeleton width="16px" height="14px" /></td>
            <td class="num"><UiSkeleton width="24px" height="14px" /></td>
            <td class="num"><UiSkeleton width="18px" height="14px" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </UiPanel>
</template>
