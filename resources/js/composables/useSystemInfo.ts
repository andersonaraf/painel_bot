import { ref, onMounted } from 'vue'
import axios from 'axios'

export function useSystemInfo() {
    const info = ref(null)
    const loading = ref(true)

    const fetchInfo = async () => {
        const { data } = await axios.get('/admin/system-info')
        info.value = data
        loading.value = false
    }

    onMounted(fetchInfo)

    return { info, loading }
}
