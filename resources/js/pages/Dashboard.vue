<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import RadialProgress from '@/components/RadialProgress.vue'

import { type BreadcrumbItem } from '@/types';
import { useSystemInfo } from '@/composables/useSystemInfo';
import { computed } from 'vue';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const { info, loading } = useSystemInfo();

const ramPercent = computed(() => {
    if (!info.value || !info.value.ram_total) return 0
    return Number(((info.value.ram_current / info.value.ram_total) * 100).toFixed(1))
})


const diskPercentUsed = computed(() => {
    if (!info.value || !info.value.disk_total) return 0

    const used = info.value.disk_total - info.value.disk_free
    return Number(((used / info.value.disk_total) * 100).toFixed(1))
})


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Cards principais -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Sistema -->
                <div  v-if="info" class="p-4 flex flex-col justify-center items-start bg-white dark:bg-gray-900 rounded-xl border border-sidebar-border/70">
                    <h2 class="font-semibold text-sm text-gray-600 dark:text-gray-300 mb-1">Sistema</h2>
                    <p class="text-sm text-gray-700 dark:text-white">{{ info.system }}</p>
                </div>

                <!-- RAM -->
                <div v-if="info" class="p-4 flex flex-col justify-center items-center bg-white dark:bg-gray-900 rounded-xl border border-sidebar-border/70">
                    <h2 class="font-semibold text-sm text-gray-600 dark:text-gray-300 mb-2">RAM</h2>
                    <RadialProgress :percent="ramPercent" />
                    <p class="mt-1 text-xs text-gray-500">
                        Uso: {{ info.ram_current.toFixed(2) }} GB de {{ info.ram_total.toFixed(2) }} GB
                    </p>
                </div>

                <!-- Disco -->
                <div v-if="info" class="p-4 flex flex-col justify-center items-center bg-white dark:bg-gray-900 rounded-xl border border-sidebar-border/70">
                    <h2 class="font-semibold text-sm text-gray-600 dark:text-gray-300 mb-2">Disco</h2>
                    <RadialProgress :percent="diskPercentUsed" />
                    <p class="mt-1 text-xs text-gray-500">
                        Usado: {{ (info.disk_total - info.disk_free).toFixed(2) }} GB de {{ info.disk_total.toFixed(2) }} GB
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
