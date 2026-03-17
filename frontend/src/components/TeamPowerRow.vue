<script setup>
import { ref, watch } from 'vue'

import UiButton from './ui/UiButton.vue'

const props = defineProps({
  team: {
    type: Object,
    required: true,
  },
  busy: {
    type: Boolean,
    default: false,
  },
  locked: {
    type: Boolean,
    default: false,
  },
  minPower: {
    type: Number,
    default: 1,
  },
  maxPower: {
    type: Number,
    default: 100,
  },
})

const emit = defineEmits(['save'])

const isEditing = ref(false)
const power = ref(0)

function syncFromTeam() {
  power.value = props.team.power ?? 0
}

watch(
  () => [props.team.id, props.team.power],
  () => {
    if (!isEditing.value) {
      syncFromTeam()
    }
  },
  { immediate: true },
)

function startEdit() {
  if (props.locked) return
  isEditing.value = true
  syncFromTeam()
}

function cancelEdit() {
  isEditing.value = false
  syncFromTeam()
}

function save() {
  emit('save', { id: props.team.id, power: Number(power.value) })
  isEditing.value = false
}
</script>

<template>
  <tr>
    <td>{{ team.name }}</td>
    <td class="num">
      <template v-if="isEditing">
        <input
          v-model.number="power"
          class="power-input"
          type="number"
          :min="minPower"
          :max="maxPower"
          step="1"
          inputmode="numeric"
          :disabled="busy"
        />
      </template>
      <template v-else>
        {{ team.power }}
      </template>
    </td>
    <td class="num">
      <span class="row-actions">
        <template v-if="locked">
          <UiButton size="sm" :disabled="true" title="Reset the season to change team power.">Locked</UiButton>
        </template>
        <template v-else>
          <UiButton v-if="!isEditing" size="sm" :disabled="busy" @click="startEdit">Edit</UiButton>
          <template v-else>
            <UiButton size="sm" variant="primary" :disabled="busy" @click="save">Save</UiButton>
            <UiButton size="sm" :disabled="busy" @click="cancelEdit">Cancel</UiButton>
          </template>
        </template>
      </span>
    </td>
  </tr>
</template>
