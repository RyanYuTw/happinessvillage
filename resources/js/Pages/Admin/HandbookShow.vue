<template>
    <AdminLayout>
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-base sm:text-lg font-medium text-gray-900">手冊預覽</h2>
                <Link :href="route('admin.handbooks')" class="text-sm text-gray-600 hover:text-gray-900">
                    返回列表
                </Link>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">年度：</span>
                        <span>{{ handbook.year }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">年級：</span>
                        <span>{{ handbook.grade }}年級</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">學期：</span>
                        <span>{{ handbook.semester }}學期</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">課別：</span>
                        <span>{{ handbook.lesson }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">狀態：</span>
                        <span :class="handbook.status ? 'text-green-600' : 'text-gray-600'">
                            {{ handbook.status ? '已發布' : '未發布' }}
                        </span>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">內容</h3>
                    <div class="prose max-w-none" v-html="decodedContent"></div>
                </div>
                
                <div class="mt-6 flex gap-2">
                    <Link :href="route('admin.handbooks.edit', handbook.id)" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded text-sm font-medium">
                        編輯
                    </Link>
                    <Link :href="route('admin.handbooks')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium">
                        返回
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    handbook: Object
})

const decodedContent = computed(() => {
    if (!props.handbook.content) return '無內容'
    console.log('Raw content:', props.handbook.content.substring(0, 100))
    const txt = document.createElement('textarea')
    txt.innerHTML = props.handbook.content
    const decoded = txt.value
    console.log('Decoded content:', decoded.substring(0, 100))
    return decoded
})
</script>
