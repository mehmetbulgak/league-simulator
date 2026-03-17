<script setup>
import UiPanel from './ui/UiPanel.vue'

defineProps({
  standings: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <UiPanel title="League Table">
    <div class="table-wrap">
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
          <tr v-for="row in standings" :key="row.teamId">
            <td>{{ row.teamName }}</td>
            <td class="num">{{ row.played }}</td>
            <td class="num">{{ row.wins }}</td>
            <td class="num">{{ row.draws }}</td>
            <td class="num">{{ row.losses }}</td>
            <td class="num">{{ row.goalDifference }}</td>
            <td class="num">
              <strong>{{ row.points }}</strong>
            </td>
          </tr>
          <tr v-if="standings.length === 0">
            <td colspan="7" class="muted">No standings yet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </UiPanel>
</template>
