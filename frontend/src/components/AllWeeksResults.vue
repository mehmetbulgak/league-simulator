<script setup>
import { computed, ref, watch } from 'vue'

import MatchResultsList from './MatchResultsList.vue'
import UiPanel from './ui/UiPanel.vue'

const props = defineProps({
  weeks: {
    type: Array,
    default: () => [],
  },
  currentWeek: {
    type: Number,
    default: undefined,
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

const selectedWeek = ref(null)
const userOverridden = ref(false)

const orderedWeeks = computed(() => {
  return [...(props.weeks || [])].sort((a, b) => Number(a.week) - Number(b.week))
})

watch(
  [orderedWeeks, () => props.currentWeek],
  ([weeks, currentWeek]) => {
    if (!weeks.length) {
      selectedWeek.value = null
      userOverridden.value = false
      return
    }

    const hasCurrentWeek = currentWeek !== undefined && weeks.some((w) => w.week === currentWeek)
    const initialWeek = hasCurrentWeek ? currentWeek : currentWeek === undefined ? weeks.at(-1).week : weeks[0].week

    if (selectedWeek.value === null) {
      userOverridden.value = false
      selectedWeek.value = initialWeek
      return
    }

    const exists = weeks.some((w) => w.week === selectedWeek.value)
    if (!exists) {
      userOverridden.value = false
      selectedWeek.value = initialWeek
      return
    }

    if (!userOverridden.value && hasCurrentWeek) {
      selectedWeek.value = currentWeek
    }
  },
  { immediate: true },
)

const activeWeek = computed(() => orderedWeeks.value.find((w) => w.week === selectedWeek.value) || null)

function selectWeek(weekNumber) {
  selectedWeek.value = weekNumber
  if (props.currentWeek === undefined) {
    userOverridden.value = true
    return
  }
  userOverridden.value = weekNumber !== props.currentWeek
}

function playedCount(week) {
  return (week?.matches || []).filter((m) => m.homeGoals !== null && m.awayGoals !== null).length
}
</script>

<template>
  <UiPanel title="All Weeks Results">
    <div v-if="!weeks || weeks.length === 0" class="muted">No fixtures yet. Generate fixtures to see weekly results.</div>
    <template v-else>
      <div class="weeks-tabs" role="tablist" aria-label="Weeks">
        <button
          v-for="week in orderedWeeks"
          :key="week.week"
          type="button"
          role="tab"
          :aria-selected="week.week === selectedWeek"
          :class="[
            'weeks-tab',
            week.week === selectedWeek ? 'is-active' : null,
            props.currentWeek !== undefined && week.week === props.currentWeek ? 'is-current' : null,
          ]"
          :disabled="busy"
          @click="selectWeek(week.week)"
        >
          <span>Week {{ week.week }}</span>
          <span class="weeks-pill">{{ playedCount(week) }}/{{ week.matches.length }}</span>
        </button>
      </div>

      <div v-if="activeWeek" class="weeks-content" role="tabpanel">
        <MatchResultsList
          :matches="activeWeek.matches"
          :busy="busy"
          :max-goals="maxGoals"
          @save="(payload) => emit('save', payload)"
        />
      </div>
    </template>
  </UiPanel>
</template>
