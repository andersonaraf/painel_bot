<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import MonacoEditor from '@guolao/vue-monaco-editor'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    bot: Object,
})

const form = useForm({
    nome: props.bot?.nome ?? '',
    codigo: props.bot?.codigo ?? '',
})

const isEdit = props.bot !== null

const salvar = () => {
    if (!props.bot) {
        form.post('/admin/bots')
    } else {
        form.put(`/admin/bots/${props.bot.id}`)
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ label: 'Bots', route: '/bots' }, { label: isEdit ? 'Editar' : 'Criar' }]">
        <form @submit.prevent="salvar" class="min-h-screen space-y-4 p-4">
            <h1 class="text-xl font-bold">{{ isEdit ? 'Editar Bot' : 'Criar Bot' }}</h1>

            <div>
                <label class="mb-1 block text-sm font-medium">Nome</label>
                <input v-model="form.nome" type="text" class="w-full rounded border p-2" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Código (Python)</label>
                <div class="h-[500px] w-full overflow-hidden rounded border">
                    <MonacoEditor
                        v-model:value="form.codigo"
                        language="python"
                        theme="vs-dark"
                        :options="{
              automaticLayout: true,
              fontSize: 14
            }"
                        class="h-full w-full"
                    />
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    {{ isEdit ? 'Salvar Alterações' : 'Criar Bot' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>
