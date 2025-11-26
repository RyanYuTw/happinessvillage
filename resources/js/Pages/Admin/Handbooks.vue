<template>
    <AdminLayout>
        <div class="bg-white rounded-lg shadow" style="min-height: calc(100vh - 200px);">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h2 class="text-base sm:text-lg font-medium text-gray-900">學習手冊管理</h2>
            </div>
            
            <div class="p-4 sm:p-6">
                <form @submit.prevent="search" class="mb-6 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                    <input v-model="form.year" type="number" placeholder="年度" class="border rounded px-2 py-1.5 text-sm">
                    <select v-model="form.grade" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">全部年級</option>
                        <option v-for="i in 6" :key="i" :value="i">{{ i }}年級</option>
                    </select>
                    <select v-model="form.semester" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">全部學期</option>
                        <option value="上">上學期</option>
                        <option value="下">下學期</option>
                    </select>
                    <input v-model="form.lesson" type="text" placeholder="課別" class="border rounded px-2 py-1.5 text-sm">
                    <select v-model="form.status" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">全部狀態</option>
                        <option value="0">未發布</option>
                        <option value="1">已發布</option>
                    </select>
                    <button type="submit" class="bg-green-400 hover:bg-green-500 text-white px-3 py-1.5 rounded text-sm font-medium">查詢</button>
                    <button type="button" @click="reset" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded text-sm font-medium">清除</button>
                </form>
                
                <div class="mb-4">
                    <Link :href="route('admin.handbooks.create')" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium w-full sm:w-auto text-center">
                        新增手冊
                    </Link>
                </div>
                
                <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg" style="min-height: 400px;">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">年度</th>
                                <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">年級</th>
                                <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">學期</th>
                                <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">課別</th>
                                <th class="hidden lg:table-cell px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">發布時間</th>
                                <th class="hidden md:table-cell px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">建立時間</th>
                                <th class="hidden xl:table-cell px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase">更新時間</th>
                                <th class="px-3 py-3 text-center text-sm font-medium text-gray-500 uppercase">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="handbook in handbooks" :key="handbook.id">
                                <td class="px-3 py-4 text-sm text-gray-900">{{ handbook.year }}</td>
                                <td class="px-3 py-4 text-sm text-gray-900">{{ handbook.grade }}</td>
                                <td class="px-3 py-4 text-sm text-gray-900">{{ handbook.semester }}</td>
                                <td class="px-3 py-4 text-sm text-gray-900">{{ handbook.lesson }}</td>
                                <td class="hidden lg:table-cell px-3 py-4 text-sm text-gray-500">{{ formatDate(handbook.published_at) }}</td>
                                <td class="hidden md:table-cell px-3 py-4 text-sm text-gray-500">{{ formatDate(handbook.created_at) }}</td>
                                <td class="hidden xl:table-cell px-3 py-4 text-sm text-gray-500">{{ formatDate(handbook.updated_at) }}</td>
                                <td class="px-3 py-4 text-center space-x-4">
                                    <a :href="route('admin.handbooks.preview', handbook.id)" target="_blank" class="text-blue-600 hover:text-blue-800 text-lg" title="查看"><i class="fas fa-eye"></i></a>
                                    <Link :href="route('admin.handbooks.edit', handbook.id)" class="text-yellow-600 hover:text-yellow-800 text-lg" title="編輯"><i class="fas fa-edit"></i></Link>
                                    <button @click="deleteHandbook(handbook.id)" class="text-red-600 hover:text-red-800 text-lg" title="刪除"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import { showConfirm } from '@/utils/sweetalert'

const props = defineProps({
    handbooks: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const form = reactive({
    year: props.filters?.year || '',
    grade: props.filters?.grade || '',
    semester: props.filters?.semester || '',
    lesson: props.filters?.lesson || '',
    status: props.filters?.status || ''
})

const search = () => {
    router.get(route('admin.handbooks'), form, { preserveState: true })
}

const reset = () => {
    form.year = ''
    form.grade = ''
    form.semester = ''
    form.lesson = ''
    form.status = ''
    search()
}

const formatDate = (timestamp) => {
    if (!timestamp) return '-'
    return new Date(timestamp * 1000).toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const deleteHandbook = async (id) => {
    const result = await showConfirm('確定要刪除這個手冊嗎？')
    if (result.isConfirmed) {
        router.delete(route('admin.handbooks.destroy', id))
    }
}
</script>