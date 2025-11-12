<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-secondary-300 bg-white px-5 py-2.5 text-sm font-medium text-secondary-700 shadow-sm transition-all duration-200 hover:bg-secondary-50 hover:border-secondary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <slot />
    </button>
</template>
