<script setup>
import { onMounted, ref } from 'vue'

import UiAlert from '../components/ui/UiAlert.vue'
import UiButton from '../components/ui/UiButton.vue'
import UiCard from '../components/ui/UiCard.vue'
import UiPanel from '../components/ui/UiPanel.vue'
import UiSpinner from '../components/ui/UiSpinner.vue'
import TeamPowerRow from '../components/TeamPowerRow.vue'
import TeamsTableSkeletonRows from '../components/skeletons/TeamsTableSkeletonRows.vue'
import { leagueApi } from '../services/leagueApi'

const emit = defineEmits(['fixtures-generated'])

const teams = ref([])
const fixturesGenerated = ref(false)
const loading = ref(false)
const error = ref('')
const savingTeamId = ref(null)

async function loadTeams() {
  loading.value = true
  error.value = ''

  try {
    const state = await leagueApi.getSimulationState()
    teams.value = state.teams ?? []
    fixturesGenerated.value = Boolean(state.fixturesGenerated)
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not load teams. Please try again.'
  } finally {
    loading.value = false
  }
}

async function generateFixtures() {
  loading.value = true
  error.value = ''

  try {
    await leagueApi.generateFixtures()
    emit('fixtures-generated')
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not generate fixtures. Please try again.'
  } finally {
    loading.value = false
  }
}

async function saveTeamPower({ id, power }) {
  savingTeamId.value = id
  error.value = ''

  try {
    const updated = await leagueApi.updateTeamPower(id, power)
    teams.value = teams.value.map((t) => (t.id === id ? updated : t))
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Could not update team power. Please try again.'
  } finally {
    savingTeamId.value = null
  }
}

onMounted(loadTeams)
</script>

<template>
  <UiCard>
    <template #header>
      <h2>Tournament Teams</h2>
      <p class="muted">
        Adjust team power before generating fixtures. Once fixtures exist, power is locked to keep the simulation
        consistent.
      </p>
    </template>

    <UiAlert v-if="error">{{ error }}</UiAlert>

    <UiPanel v-if="fixturesGenerated" title="Team power locked">
      <p class="muted small">
        Fixtures are already generated. To change team power, reset the season from the Simulation screen.
      </p>
    </UiPanel>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Team Name</th>
            <th class="num">Power</th>
            <th class="num">Actions</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="loading && teams.length === 0">
            <TeamsTableSkeletonRows :rows="4" />
          </template>
          <template v-else>
            <TeamPowerRow
              v-for="team in teams"
              :key="team.id"
              :team="team"
              :busy="loading || savingTeamId === team.id"
              :locked="fixturesGenerated"
              @save="saveTeamPower"
            />
          </template>
          <tr v-if="!loading && teams.length === 0">
            <td colspan="3" class="muted">No teams found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="actions">
      <UiButton v-if="fixturesGenerated" variant="primary" :disabled="loading" @click="emit('fixtures-generated')">
        View Fixtures
      </UiButton>
      <UiButton v-else variant="primary" :disabled="loading || teams.length < 2" @click="generateFixtures">
        <span v-if="loading" class="loading-inline">
          <UiSpinner size="sm" />
          Generating...
        </span>
        <span v-else>Generate Fixtures</span>
      </UiButton>
    </div>
  </UiCard>
</template>
