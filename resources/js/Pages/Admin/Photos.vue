<template>
    <AdminLayout>
        <div class="flex gap-4 h-full">
            <!-- 左側：圖片列表 -->
            <div class="flex-1 bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-base sm:text-lg font-medium text-gray-900">圖片管理</h2>
                        <button 
                            @click="showUploadModal = true"
                            class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded-md text-sm font-medium"
                        >
                            上傳圖片
                        </button>
                    </div>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="搜尋圖片（檔名或標籤）..."
                            class="w-full px-4 py-2 pl-10 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
                        />
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <div v-if="searchQuery" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <button @click="searchQuery = ''" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 overflow-auto p-4 sm:p-6">
                    <div v-if="filteredPhotos.length === 0" class="text-center text-gray-500 py-12">
                        <div v-if="searchQuery">找不到符合「{{ searchQuery }}」的圖片</div>
                        <div v-else>尚無圖片</div>
                    </div>
                    <div v-else>
                        <div class="text-sm text-gray-600 mb-3">
                            共 {{ filteredPhotos.length }} 張圖片
                            <span v-if="searchQuery" class="text-yellow-600">（搜尋結果）</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                            <div 
                                v-for="photo in filteredPhotos" 
                            :key="photo.id"
                            @click="selectPhoto(photo)"
                            :class="[
                                'relative group cursor-pointer transition-all duration-200',
                                selectedPhoto?.id === photo.id ? 'ring-2 ring-yellow-500 rounded-lg' : ''
                            ]"
                        >
                            <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden transition-transform duration-200 group-hover:scale-105 group-hover:shadow-lg">
                                <img 
                                    :src="photo.url" 
                                    :alt="photo.filename" 
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <!-- Hover 遮罩效果 -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg pointer-events-none"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 右側：圖片資訊 -->
            <div class="w-80 bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="px-4 py-4 border-b border-gray-200">
                    <h3 class="text-base font-medium text-gray-900">圖片資訊</h3>
                </div>
                
                <div v-if="!selectedPhoto" class="flex-1 flex items-center justify-center text-gray-500">
                    請選擇一張圖片
                </div>
                
                <div v-else class="flex-1 overflow-auto p-4 space-y-4">
                    <!-- 圖片預覽 -->
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        <img 
                            :src="selectedPhoto.url" 
                            :alt="selectedPhoto.filename"
                            class="w-full h-full object-contain"
                        >
                    </div>
                    
                    <!-- 基本資訊 -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">檔案名稱</label>
                            <input
                                v-model="editingFilename"
                                type="text"
                                class="w-full px-3 py-2 border rounded-md text-sm"
                                @blur="updateFilename"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">標籤</label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span 
                                    v-for="(tag, index) in selectedPhoto.tags" 
                                    :key="index"
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs"
                                >
                                    {{ tag }}
                                    <button @click="removeTag(index)" class="hover:text-yellow-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex gap-2">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        placeholder="新增標籤（空格分隔多個）"
                                        class="flex-1 px-3 py-2 border rounded-md text-sm"
                                        @keyup.enter="addTag"
                                    />
                                    <button 
                                        @click="addTag"
                                        class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-gray-900 rounded-md text-sm"
                                    >
                                        新增
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">提示：可用空格分隔多個標籤，例如「風景 自然 山水」</p>
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t">
                            <div class="text-sm space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">尺寸：</span>
                                    <span class="font-medium">{{ selectedPhoto.width }} × {{ selectedPhoto.height }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">檔案大小：</span>
                                    <span class="font-medium">{{ formatFileSize(selectedPhoto.size) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">格式：</span>
                                    <span class="font-medium">{{ selectedPhoto.mime_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">上傳時間：</span>
                                    <span class="font-medium">{{ formatDate(selectedPhoto.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t">
                            <label class="block text-sm font-medium text-gray-700 mb-2">圖片網址</label>
                            <div class="flex gap-2">
                                <input
                                    :value="selectedPhoto.url"
                                    type="text"
                                    readonly
                                    class="flex-1 px-3 py-2 border rounded-md text-sm bg-gray-50"
                                />
                                <button 
                                    @click="copyUrl"
                                    class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-sm"
                                >
                                    複製
                                </button>
                            </div>
                        </div>
                        
                        <div class="pt-3">
                            <button 
                                @click="deletePhoto"
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm"
                            >
                                刪除圖片
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 上傳模態框 -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-96">
                <h3 class="text-lg font-bold mb-4">上傳圖片</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">選擇圖片</label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            @change="handleFileSelect"
                            class="w-full px-3 py-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">檔案名稱（選填）</label>
                        <input
                            v-model="uploadFilename"
                            type="text"
                            class="w-full px-3 py-2 border rounded"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">標籤（以空格分隔）</label>
                        <input
                            v-model="uploadTags"
                            type="text"
                            placeholder="例：風景 自然 山水"
                            class="w-full px-3 py-2 border rounded"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button 
                        @click="showUploadModal = false; uploadFilename = ''; uploadTags = ''"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 border rounded"
                    >
                        取消
                    </button>
                    <button 
                        @click="uploadImage"
                        :disabled="!selectedFile"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-gray-900 rounded disabled:opacity-50"
                    >
                        上傳
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { showAlert, showError, showSuccess, showConfirm } from '@/utils/sweetalert'

const props = defineProps({
    photos: Array
})

// 為每個圖片添加 URL
const photos = computed(() => {
    return props.photos.map(photo => ({
        ...photo,
        url: `/storage/${photo.path}`
    }))
})

// 過濾後的圖片列表
const filteredPhotos = computed(() => {
    if (!searchQuery.value.trim()) {
        return photos.value
    }
    
    const query = searchQuery.value.toLowerCase().trim()
    
    return photos.value.filter(photo => {
        // 搜尋檔名
        if (photo.filename.toLowerCase().includes(query)) {
            return true
        }
        
        // 搜尋標籤
        if (photo.tags && Array.isArray(photo.tags)) {
            return photo.tags.some(tag => tag.toLowerCase().includes(query))
        }
        
        return false
    })
})

const selectedPhoto = ref(null)
const editingFilename = ref('')
const newTag = ref('')
const showUploadModal = ref(false)
const uploadFilename = ref('')
const uploadTags = ref('')
const selectedFile = ref(null)
const fileInput = ref(null)
const searchQuery = ref('')

const selectPhoto = (photo) => {
    selectedPhoto.value = photo
    editingFilename.value = photo.filename
}

const updateFilename = async () => {
    if (!selectedPhoto.value || editingFilename.value === selectedPhoto.value.filename) return
    
    try {
        await axios.put(`/admin/photos/${selectedPhoto.value.id}`, {
            filename: editingFilename.value
        })
        selectedPhoto.value.filename = editingFilename.value
        router.reload({ only: ['photos'] })
    } catch (error) {
        console.error('更新檔名失敗:', error)
        showError('更新檔名失敗')
    }
}

const addTag = async () => {
    if (!selectedPhoto.value || !newTag.value.trim()) return
    
    const existingTags = selectedPhoto.value.tags || []
    
    // 用空格分隔多個標籤
    const newTags = newTag.value.trim().split(/\s+/).filter(tag => tag.length > 0)
    
    // 過濾掉已存在的標籤
    const tagsToAdd = newTags.filter(tag => !existingTags.includes(tag))
    
    if (tagsToAdd.length === 0) {
        newTag.value = ''
        return
    }
    
    try {
        const updatedTags = [...existingTags, ...tagsToAdd]
        await axios.put(`/admin/photos/${selectedPhoto.value.id}`, {
            tags: updatedTags
        })
        selectedPhoto.value.tags = updatedTags
        newTag.value = ''
        router.reload({ only: ['photos'] })
    } catch (error) {
        console.error('新增標籤失敗:', error)
        showError('新增標籤失敗')
    }
}

const removeTag = async (index) => {
    if (!selectedPhoto.value) return
    
    const tags = [...(selectedPhoto.value.tags || [])]
    tags.splice(index, 1)
    
    try {
        await axios.put(`/admin/photos/${selectedPhoto.value.id}`, {
            tags
        })
        selectedPhoto.value.tags = tags
        router.reload({ only: ['photos'] })
    } catch (error) {
        console.error('移除標籤失敗:', error)
        showError('移除標籤失敗')
    }
}

const copyUrl = () => {
    if (!selectedPhoto.value) return
    navigator.clipboard.writeText(selectedPhoto.value.url)
    showSuccess('已複製網址')
}

const deletePhoto = async () => {
    if (!selectedPhoto.value) return
    
    const result = await showConfirm('確定要刪除這張圖片嗎？')
    if (!result.isConfirmed) return
    
    try {
        await axios.delete(`/admin/photos/${selectedPhoto.value.id}`)
        selectedPhoto.value = null
        router.reload({ only: ['photos'] })
    } catch (error) {
        console.error('刪除圖片失敗:', error)
        showError('刪除圖片失敗')
    }
}

const handleFileSelect = (e) => {
    selectedFile.value = e.target.files[0]
}

const uploadImage = async () => {
    if (!selectedFile.value) return
    
    const formData = new FormData()
    formData.append('image', selectedFile.value)
    
    if (uploadFilename.value.trim()) {
        formData.append('filename', uploadFilename.value.trim())
    }
    
    if (uploadTags.value.trim()) {
        const tags = uploadTags.value.trim().split(/\s+/)
        formData.append('tags', JSON.stringify(tags))
    }
    
    try {
        await axios.post('/admin/photos/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        
        showUploadModal.value = false
        uploadFilename.value = ''
        uploadTags.value = ''
        selectedFile.value = null
        if (fileInput.value) {
            fileInput.value.value = ''
        }
        
        router.reload({ only: ['photos'] })
    } catch (error) {
        console.error('上傳圖片失敗:', error)
        showError('上傳圖片失敗')
    }
}

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

const formatDate = (timestamp) => {
    const date = new Date(timestamp * 1000)
    return date.toLocaleString('zh-TW')
}
</script>