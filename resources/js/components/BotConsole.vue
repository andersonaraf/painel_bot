<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    botId: {
        type: Number,
        required: true,
    },
    pollingInterval: {
        type: Number,
        default: 2000,
    },
})

const log = ref('')
const container = ref(null)
let interval = null


watch(log, () => {
    if (container.value) {
        container.value.scrollTop = container.value.scrollHeight
    }
})

const fetchLog = async () => {
    try {
        const timestamp = new Date().getTime()

        const { data } = await axios.get(`/admin/bots/${props.botId}/log?t=${timestamp}`, {
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache',
            },
        })

        // Força re-render mesmo se conteúdo for igual
        log.value = '' + data
    } catch (e) {
        log.value = 'Erro ao carregar log.'
    }
}



onMounted(() => {
    fetchLog()
    interval = setInterval(fetchLog, props.pollingInterval)
})

onUnmounted(() => {
    clearInterval(interval)
})
</script>

<template>
    <div ref="container" class="bg-black text-green-400 p-4 rounded font-mono text-sm whitespace-pre-wrap h-80 overflow-y-auto">
        <p class="text-white font-bold mb-2">Console do Bot #{{ botId }}</p>
        <pre>{{ log }}</pre>
    </div>
</template>

