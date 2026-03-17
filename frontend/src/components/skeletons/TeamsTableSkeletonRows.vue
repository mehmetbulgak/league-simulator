<script setup>
import UiSkeleton from '../ui/UiSkeleton.vue'

defineProps({
  rows: {
    type: Number,
    default: 4,
  },
})
</script>

<template>
  <tr v-for="i in rows" :key="i">
    <td><UiSkeleton width="55%" height="14px" /></td>
    <td class="num"><UiSkeleton width="36px" height="14px" /></td>
    <td class="num">
      <UiSkeleton width="60px" height="32px" radius="9px" />
    </td>
  </tr>
</template>
