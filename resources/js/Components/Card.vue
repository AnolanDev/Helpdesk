<template>
  <div :class="cardClasses">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'elevated'].includes(value),
  },
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'small', 'normal', 'large'].includes(value),
  },
  hoverable: {
    type: Boolean,
    default: false,
  },
});

const cardClasses = computed(() => {
  const classes = ['rounded-xl transition-all duration-200'];

  // Variant styles
  if (props.variant === 'default') {
    classes.push('bg-white border border-secondary-200');
  } else if (props.variant === 'bordered') {
    classes.push('bg-white border-2 border-secondary-300');
  } else if (props.variant === 'elevated') {
    classes.push('bg-white shadow-soft');
  }

  // Padding
  if (props.padding === 'none') {
    classes.push('p-0');
  } else if (props.padding === 'small') {
    classes.push('p-4');
  } else if (props.padding === 'normal') {
    classes.push('p-6');
  } else if (props.padding === 'large') {
    classes.push('p-8');
  }

  // Hoverable
  if (props.hoverable) {
    classes.push('hover:shadow-soft-lg hover:-translate-y-0.5 cursor-pointer');
  }

  return classes.join(' ');
});
</script>
