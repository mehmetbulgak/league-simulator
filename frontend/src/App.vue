<script setup>
import { ref } from 'vue'

import FixturesView from './views/FixturesView.vue'
import SimulationView from './views/SimulationView.vue'
import TeamsView from './views/TeamsView.vue'
import UiLinkButton from './components/ui/UiLinkButton.vue'

const step = ref('teams')

function goToTeams() {
  step.value = 'teams'
}

function goToFixtures() {
  step.value = 'fixtures'
}

function goToSimulation() {
  step.value = 'simulation'
}
</script>

<template>
  <div class="app">
    <header class="app-header">
      <h1 class="app-title">League Simulator</h1>
      <nav class="app-nav">
        <UiLinkButton :disabled="step === 'teams'" @click="goToTeams">Teams</UiLinkButton>
        <span class="sep">/</span>
        <UiLinkButton :disabled="step === 'fixtures'" @click="goToFixtures">Fixtures</UiLinkButton>
        <span class="sep">/</span>
        <UiLinkButton :disabled="step === 'simulation'" @click="goToSimulation">
          Simulation
        </UiLinkButton>
      </nav>
    </header>

    <main class="app-main">
      <TeamsView v-if="step === 'teams'" @fixtures-generated="goToFixtures" />

      <FixturesView v-else-if="step === 'fixtures'" @start-simulation="goToSimulation" @back="goToTeams" />

      <SimulationView v-else @back-to-teams="goToTeams" @back-to-fixtures="goToFixtures" />
    </main>
  </div>
</template>
