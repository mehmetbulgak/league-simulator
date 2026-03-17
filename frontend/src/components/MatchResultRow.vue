<script setup>
import { computed, ref, watch } from 'vue'

import UiButton from './ui/UiButton.vue'

const props = defineProps({
  match: {
    type: Object,
    required: true,
  },
  busy: {
    type: Boolean,
    default: false,
  },
  maxGoals: {
    type: Number,
    default: 20,
  },
})

const emit = defineEmits(['save'])

const isEditing = ref(false)
const homeGoals = ref(0)
const awayGoals = ref(0)

const isPlayed = computed(() => props.match.homeGoals !== null && props.match.awayGoals !== null)

function syncFromMatch() {
  homeGoals.value = props.match.homeGoals ?? 0
  awayGoals.value = props.match.awayGoals ?? 0
}

watch(
  () => [props.match.id, props.match.homeGoals, props.match.awayGoals],
  () => {
    if (!isEditing.value) {
      syncFromMatch()
    }
  },
  { immediate: true },
)

function startEdit() {
  isEditing.value = true
  syncFromMatch()
}

function cancelEdit() {
  isEditing.value = false
  syncFromMatch()
}

function save() {
  emit('save', {
    id: props.match.id,
    homeGoals: Number(homeGoals.value),
    awayGoals: Number(awayGoals.value),
  })
  isEditing.value = false
}
</script>

<template>
  <div class="match-row">
    <span class="team home">{{ match.homeTeam.name }}</span>

    <span class="score">
      <template v-if="isEditing">
        <input
          v-model.number="homeGoals"
          class="score-input"
          type="number"
          min="0"
          :max="maxGoals"
          step="1"
          inputmode="numeric"
          :disabled="busy"
        />
        <span class="score-sep">-</span>
        <input
          v-model.number="awayGoals"
          class="score-input"
          type="number"
          min="0"
          :max="maxGoals"
          step="1"
          inputmode="numeric"
          :disabled="busy"
        />
      </template>
      <template v-else>
        <span v-if="isPlayed">{{ match.homeGoals }} - {{ match.awayGoals }}</span>
        <span v-else>-</span>
      </template>
    </span>

    <span class="team away right">{{ match.awayTeam.name }}</span>

    <span class="row-actions">
      <UiButton v-if="!isEditing" size="sm" :disabled="busy" @click="startEdit">
        {{ isPlayed ? 'Edit' : 'Set' }}
      </UiButton>
      <template v-else>
        <UiButton size="sm" variant="primary" :disabled="busy" @click="save">Save</UiButton>
        <UiButton size="sm" :disabled="busy" @click="cancelEdit">Cancel</UiButton>
      </template>
    </span>
  </div>
</template>
