<script setup>
import BotConsole from '@/components/BotConsole.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const props = defineProps({
    bots: Array,
});
const botSelecionado = ref(null);
const botLogId = ref(null)

const botStatus = ref({});
const toggleConsole = (botId) => {
    botLogId.value = botLogId.value === botId ? null : botId
}

const deleteBot = (id) => {
    if (confirm('Deseja excluir este bot?')) {
        router.delete(`/admin/bots/${id}`);
    }
};

const checkBotStatus = async (bot) => {
    try {
        const { data } = await axios.get(`/admin/bots/${bot.id}/status`);
        botStatus.value[bot.id] = data.running;
    } catch (e) {
        botStatus.value[bot.id] = false;
    }
};

const executarBot = async (bot) => {
    botSelecionado.value = bot;

    try {
        await axios.post(`/admin/bots/${bot.id}/execute`);
    } catch (err) {
        alert('Erro ao executar bot: ' + (err.response?.data?.error || err.message));
    }
};

const stopBot = async (bot) => {
    try {
        await axios.post(`/admin/bots/${bot.id}/stop`);
        botStatus.value[bot.id] = false;
    } catch (e) {
        alert('Erro ao parar o bot.');
    }
};

onMounted(() => {
    setInterval(() => {
        props.bots.forEach((bot) => {
            checkBotStatus(bot);
        });
    }, 3000);
});
</script>

<template>
    <AppLayout :breadcrumbs="[{ label: 'Bots', route: '/bots' }]">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold">Bots</h1>
                <button @click="router.visit('/admin/bots/create')" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Novo Bot
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="bot in bots" :key="bot.id" class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold">{{ bot.nome }}</h2>
                    <p class="text-sm text-gray-500">
                        Status:
                        <span :class="botStatus[bot.id] ? 'text-green-600' : 'text-red-500'">
                            {{ botStatus[bot.id] ? 'Executando' : 'Inativo' }}
                        </span>
                    </p>
                    <p class="text-xs text-gray-400">Última execução: {{ bot.ultima_execucao ?? 'nunca' }}</p>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="router.visit(`/admin/bots/${bot.id}/edit`)"
                            class="rounded bg-gray-200 px-3 py-1 text-sm hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                        >
                            Editar
                        </button>
                        <button
                            @click="toggleConsole(bot.id)"
                            class="text-sm px-3 py-1 rounded bg-black text-white hover:bg-gray-800"
                        >
                            {{ botLogId === bot.id ? 'Ocultar Log' : 'Ver Log' }}
                        </button>
                        <button @click="executarBot(bot)" class="rounded bg-green-500 px-3 py-1 text-sm text-white hover:bg-green-600">
                            Executar
                        </button>
                        <button
                            @click="stopBot(bot)"
                            class="rounded bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600"
                            v-if="botStatus[bot.id]"
                        >
                            Parar
                        </button>

                        <button @click="deleteBot(bot.id)" class="rounded bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600">Excluir</button>
                    </div>
                </div>
            </div>

            <BotConsole v-if="botSelecionado" :bot-id="botSelecionado.id" />
            <BotConsole v-if="botLogId" :bot-id="botLogId" class="mt-4" />

        </div>
    </AppLayout>
</template>
