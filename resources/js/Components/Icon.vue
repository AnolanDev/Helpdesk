<template>
  <svg v-bind="$attrs" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path :d="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
    <path v-if="path2" :d="path2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
  </svg>
</template>

<script setup>
defineProps({
  path: {
    type: String,
    required: true
  },
  path2: {
    type: String,
    default: null
  }
});
</script>
