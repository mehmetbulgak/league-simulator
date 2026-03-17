<script setup>
import MatchResultRow from './MatchResultRow.vue'

defineProps({
  matches: {
    type: Array,
    default: () => [],
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
</script>

<template>
  <MatchResultRow
    v-for="match in matches"
    :key="match.id"
    :match="match"
    :busy="busy"
    :max-goals="maxGoals"
    @save="(payload) => emit('save', payload)"
  />
</template>

