<!-- resources/js/components/RadialProgress.vue -->
<template>
    <div class="relative w-32 h-32">
        <svg viewBox="0 0 36 36" class="w-full h-full">
            <path
                class="text-gray-300"
                d="M18 2.0845
           a 15.9155 15.9155 0 0 1 0 31.831
           a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke-width="3.8"
            />
            <path
                class="text-blue-600"
                :d="arcPath"
                fill="none"
                stroke-width="3.8"
                stroke-dasharray="100, 100"
                stroke-linecap="round"
            />
            <text x="18" y="20.35" class="text-center text-sm fill-current text-gray-700 dark:text-white" text-anchor="middle">
                {{ percent }}%
            </text>
        </svg>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

defineProps<{
    percent: number
}>()

const arcPath = computed(() => {
    return `
    M18 2.0845
    a 15.9155 15.9155 0 0 1 0 31.831
  `
})
</script>

<style scoped>
path {
    stroke: currentColor;
    transition: stroke-dasharray 0.6s ease;
}
</style>
