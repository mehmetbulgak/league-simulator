<script setup>
import { computed, onMounted, ref } from 'vue'

import UiAlert from '../components/ui/UiAlert.vue'
import UiButton from '../components/ui/UiButton.vue'
import UiCard from '../components/ui/UiCard.vue'
import FixturesSkeleton from '../components/skeletons/FixturesSkeleton.vue'
import { leagueApi } from '../services/leagueApi'

const emit = defineEmits(['start-simulation', 'back'])

const fixtures = ref([])
const loading = ref(false)
const error = ref('')

const anyMatchPlayed = computed(() =>
  fixtures.value.some((week) => week.matches?.some((match) => Boolean(match.playedAt))),
)

const allMatchesPlayed = computed(() => {
  if (fixtures.value.length === 0) return false
  return fixtures.value.every((week) => week.matches?.every((match) => Boolean(match.playedAt)))
})

const headerHint = computed(() => {
  if (fixtures.value.length === 0) return 'No fixtures yet. Go back to Teams and generate fixtures to start the season.'
  if (allMatchesPlayed.value) return 'Season finished. You can still review the final results in Simulation.'
  if (anyMatchPlayed.value) return 'Simulation in progress. Continue playing matches from the Simulation screen.'
  return 'Fixtures are grouped by week. Start the simulation when you’re ready.'
})

const simulationButtonLabel = computed(() => {
  if (fixtures.value.length === 0) return 'Start Simulation'
  if (allMatchesPlayed.value) return 'View Results'
  if (anyMatchPlayed.value) return 'Continue Simulation'
  return 'Start Simulation'
})

async function loadFixtures() {
  loading.value = true
  error.value = ''

  try {
    fixtures.value = await leagueApi.getFixtures()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not load fixtures. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(loadFixtures)
</script>

<template>
  <UiCard>
    <template #header>
      <h2>Generated Fixtures</h2>
      <p class="muted">{{ headerHint }}</p>
    </template>

    <UiAlert v-if="error">{{ error }}</UiAlert>

    <FixturesSkeleton v-if="loading && fixtures.length === 0" />

    <div v-else class="grid">
      <div v-for="week in fixtures" :key="week.week" class="panel">
        <div class="panel-title">Week {{ week.week }}</div>
        <div class="panel-body">
          <div v-for="match in week.matches" :key="match.id" class="match-line">
            <span class="team">{{ match.homeTeam.name }}</span>
            <span class="vs">-</span>
            <span class="team right">{{ match.awayTeam.name }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <UiButton @click="emit('back')">Back</UiButton>
      <UiButton
        variant="primary"
        :disabled="loading || fixtures.length === 0"
        @click="emit('start-simulation')"
      >
        {{ simulationButtonLabel }}
      </UiButton>
    </div>
  </UiCard>
</template>
