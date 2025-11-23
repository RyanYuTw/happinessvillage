<template>
    <AdminLayout>
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h2 class="text-base sm:text-lg font-medium text-gray-900">圖片管理</h2>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="mb-4">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded-md text-sm font-medium w-full sm:w-auto">
                        上傳圖片
                    </button>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                    <div class="relative group">
                        <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden">
                            <img src="https://via.placeholder.com/200" alt="範例圖片" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity rounded-lg flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity space-x-2">
                                <button class="bg-white text-gray-900 px-3 py-1 rounded text-sm">編輯</button>
                                <button class="bg-red-600 text-white px-3 py-1 rounded text-sm">刪除</button>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 truncate">範例圖片.jpg</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>