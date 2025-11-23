<template>
    <AdminLayout>
        <div class="flex flex-col lg:flex-row gap-4 h-full">
            <!-- 中間編輯器 -->
            <div class="flex-1 bg-white rounded-lg shadow flex flex-col">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-base sm:text-lg font-medium text-gray-900">內容編輯</h2>
                    <span v-if="lastSaved" class="text-xs text-gray-500">最後儲存: {{ lastSaved }}</span>
                </div>
                <div class="p-4 flex-1 overflow-hidden">
                    <Editor v-model="form.content" class="h-full" />
                </div>
            </div>

            <!-- 右側設定欄 -->
            <div class="w-full lg:w-80 bg-yellow-50 rounded-lg shadow">
                <div class="px-4 py-4 border-b border-yellow-200">
                    <h2 class="text-base font-medium text-gray-900">手冊設定</h2>
                </div>
                <div class="p-4 space-y-4 bg-white rounded-b-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">年度</label>
                        <input v-model="form.year" type="number" class="w-full border rounded px-3 py-2 text-sm" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">年級</label>
                        <select v-model="form.grade" class="w-full border rounded px-3 py-2 text-sm" required>
                            <option value="">請選擇</option>
                            <option v-for="i in 6" :key="i" :value="i">{{ i }}年級</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">學期</label>
                        <select v-model="form.semester" class="w-full border rounded px-3 py-2 text-sm" required>
                            <option value="">請選擇</option>
                            <option value="上">上學期</option>
                            <option value="下">下學期</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">課別</label>
                        <input v-model="form.lesson" type="text" class="w-full border rounded px-3 py-2 text-sm" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">狀態</label>
                        <select v-model="form.status" class="w-full border rounded px-3 py-2 text-sm">
                            <option :value="0">未發布</option>
                            <option :value="1">已發布</option>
                        </select>
                    </div>
                    
                    <div class="pt-4 space-y-2">
                        <button @click="save" class="w-full bg-green-400 hover:bg-green-500 text-white px-4 py-2 rounded text-sm font-medium">
                            儲存
                        </button>
                        <Link :href="route('admin.handbooks')" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium text-center">
                            取消
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Editor from '@/Components/Editor.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive, watch, ref } from 'vue'

const props = defineProps({
    handbook: Object
})

const decodeHtml = (html) => {
    if (!html) return ''
    let decoded = html
    let maxIterations = 5
    while (maxIterations-- > 0 && (decoded.includes('&lt;') || decoded.includes('&gt;') || decoded.includes('&quot;'))) {
        const txt = document.createElement('textarea')
        txt.innerHTML = decoded
        const newDecoded = txt.value
        if (newDecoded === decoded) break
        decoded = newDecoded
    }
    return decoded
}

const form = reactive({
    year: props.handbook.year,
    grade: props.handbook.grade,
    semester: props.handbook.semester,
    lesson: props.handbook.lesson,
    content: decodeHtml(props.handbook.content || ''),
    status: props.handbook.status
})

const lastSaved = ref(null)
const isSaving = ref(false)
let autoSaveTimer = null

const save = () => {
    router.put(route('admin.handbooks.update', props.handbook.id), form, {
        preserveScroll: true,
        onSuccess: () => {
            localStorage.removeItem(`handbook_draft_${props.handbook.id}`)
        }
    })
}

const autoSave = () => {
    if (isSaving.value) return
    const key = `handbook_draft_${props.handbook.id}`
    localStorage.setItem(key, JSON.stringify(form))
    lastSaved.value = new Date().toLocaleTimeString('zh-TW')
}

watch(form, () => {
    clearTimeout(autoSaveTimer)
    autoSaveTimer = setTimeout(autoSave, 30000)
}, { deep: true })

const loadDraft = () => {
    const key = `handbook_draft_${props.handbook.id}`
    const draft = localStorage.getItem(key)
    if (draft) {
        const data = JSON.parse(draft)
        if (data.content && data.content !== '<p></p>' && data.content.length > form.content.length) {
            Object.assign(form, data)
        }
    }
}

loadDraft()
</script>
