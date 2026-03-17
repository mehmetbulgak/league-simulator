<script setup>
import { computed, onMounted, ref } from 'vue'

import AllWeeksResults from '../components/AllWeeksResults.vue'
import LeagueTable from '../components/LeagueTable.vue'
import PredictionsPanel from '../components/PredictionsPanel.vue'
import { MAX_GOALS_PER_TEAM } from '../config'
import { leagueApi } from '../services/leagueApi'
import UiAlert from '../components/ui/UiAlert.vue'
import UiButton from '../components/ui/UiButton.vue'
import UiCard from '../components/ui/UiCard.vue'
import SimulationSkeleton from '../components/skeletons/SimulationSkeleton.vue'

const emit = defineEmits(['back-to-teams', 'back-to-fixtures'])

const state = ref(null)
const loading = ref(false)
const error = ref('')

const maxGoals = MAX_GOALS_PER_TEAM

const playAllWeeksDisabled = computed(() => loading.value || Boolean(state.value?.isFinished))
const playNextWeekDisabled = computed(() => loading.value || Boolean(state.value?.isFinished))

const playAllWeeksDisabledReason = computed(() => {
  if (!playAllWeeksDisabled.value) return ''
  if (state.value?.isFinished) return 'All matches are already played. Reset the season to play again.'
  return ''
})

const playNextWeekDisabledReason = computed(() => {
  if (!playNextWeekDisabled.value) return ''
  if (state.value?.isFinished) return 'All matches are already played. Reset the season to play again.'
  return ''
})

async function loadState() {
  loading.value = true
  error.value = ''

  try {
    state.value = await leagueApi.getSimulationState()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not load simulation state. Please try again.'
  } finally {
    loading.value = false
  }
}

async function playNextWeek() {
  loading.value = true
  error.value = ''

  try {
    state.value = await leagueApi.playNextWeek()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not play next week. Please try again.'
  } finally {
    loading.value = false
  }
}

async function playAllWeeks() {
  loading.value = true
  error.value = ''

  try {
    state.value = await leagueApi.playAllWeeks()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not play all weeks. Please try again.'
  } finally {
    loading.value = false
  }
}

async function resetData() {
  loading.value = true
  error.value = ''

  try {
    state.value = await leagueApi.resetSimulation()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not reset the simulation. Please try again.'
  } finally {
    loading.value = false
  }
}

async function saveMatchResult({ id, homeGoals, awayGoals }) {
  loading.value = true
  error.value = ''

  try {
    state.value = await leagueApi.updateMatchResult(id, { homeGoals, awayGoals })
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not update match result. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(loadState)
</script>

<template>
  <UiCard>
    <template #header>
      <h2>Simulation</h2>
    </template>

    <UiAlert v-if="error">{{ error }}</UiAlert>

    <SimulationSkeleton v-if="!state" />

    <div v-else-if="!state.fixturesGenerated" class="empty">
      <p class="muted">Fixtures are not generated yet. Generate fixtures first to start the simulation.</p>
      <div class="actions">
        <UiButton :disabled="loading" @click="emit('back-to-teams')">Go to Teams</UiButton>
      </div>
    </div>

    <div v-else class="stack">
      <div class="three-col">
        <LeagueTable :standings="state.standings" />

        <PredictionsPanel :predictions="state.predictions" />
      </div>

      <AllWeeksResults
        :weeks="state.weeks"
        :current-week="state.currentWeek ?? undefined"
        :busy="loading"
        :max-goals="maxGoals"
        @save="saveMatchResult"
      />
    </div>

    <div v-if="state?.fixturesGenerated" class="actions">
      <UiButton :disabled="loading" @click="emit('back-to-fixtures')">Fixtures</UiButton>
      <span
        class="tooltip-wrap"
        :tabindex="playAllWeeksDisabledReason ? 0 : undefined"
        :aria-label="playAllWeeksDisabled && playAllWeeksDisabledReason ? playAllWeeksDisabledReason : undefined"
      >
        <UiButton :disabled="playAllWeeksDisabled" @click="playAllWeeks">Play All Weeks</UiButton>
        <span v-if="playAllWeeksDisabled && playAllWeeksDisabledReason" class="tooltip-bubble">
          {{ playAllWeeksDisabledReason }}
        </span>
      </span>
      <span
        class="tooltip-wrap"
        :tabindex="playNextWeekDisabledReason ? 0 : undefined"
        :aria-label="playNextWeekDisabled && playNextWeekDisabledReason ? playNextWeekDisabledReason : undefined"
      >
        <UiButton variant="primary" :disabled="playNextWeekDisabled" @click="playNextWeek">
          Play Next Week
        </UiButton>
        <span v-if="playNextWeekDisabled && playNextWeekDisabledReason" class="tooltip-bubble">
          {{ playNextWeekDisabledReason }}
        </span>
      </span>
      <UiButton variant="danger" :disabled="loading" @click="resetData">Reset Data</UiButton>
    </div>
  </UiCard>
</template>
