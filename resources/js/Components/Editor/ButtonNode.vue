<template>
  <node-view-wrapper class="button-node my-4" :style="{ textAlign: node.attrs.align || 'center' }">
    <div v-if="!editing" class="button-preview relative group inline-block" @dblclick="startEdit">
      <a
        :href="node.attrs.url"
        :style="{
          display: 'inline-block',
          padding: '0.75rem 1.5rem',
          backgroundColor: node.attrs.backgroundImage ? 'transparent' : node.attrs.color,
          backgroundImage: node.attrs.backgroundImage ? `url('${node.attrs.backgroundImage}')` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          color: node.attrs.textColor,
          textDecoration: 'none',
          borderRadius: '0.375rem',
          fontWeight: '500',
          textAlign: 'center',
          cursor: 'pointer',
        }"
        @click.prevent="startEdit"
      >
        {{ node.attrs.text }}
      </a>
      <div class="text-xs text-gray-500 mt-1 text-center">點擊或雙擊編輯按鈕</div>
      <!-- 編輯和刪除按鈕 -->
      <div class="absolute top-0 right-0 -mt-2 -mr-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button 
          @click="startEdit"
          class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:bg-blue-600"
          title="編輯按鈕"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
        </button>
        <button 
          @click="deleteButton"
          class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:bg-red-600"
          title="刪除按鈕"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
    
    <div v-else class="button-editor bg-white border rounded-lg p-4 shadow-lg">
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium mb-1">按鈕文字</label>
          <input
            v-model="localText"
            type="text"
            class="w-full px-3 py-2 border rounded"
            placeholder="按鈕"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">功能類型</label>
          <div class="flex gap-2">
            <label class="flex items-center">
              <input type="radio" value="link" v-model="buttonType" class="mr-2" />
              <span class="text-sm">連結</span>
            </label>
            <label class="flex items-center">
              <input type="radio" value="drawing" v-model="buttonType" class="mr-2" />
              <span class="text-sm">開啟塗鴉</span>
            </label>
          </div>
        </div>
        
        <div v-if="buttonType === 'link'">
          <label class="block text-sm font-medium mb-1">連結網址</label>
          <input
            v-model="localUrl"
            type="url"
            class="w-full px-3 py-2 border rounded"
            placeholder="https://example.com"
          />
        </div>
        
        <div v-if="buttonType === 'drawing'">
          <label class="block text-sm font-medium mb-1">塗鴉背景圖片</label>
          <div class="flex gap-2">
            <button
              @click="openDrawingImageSelector"
              type="button"
              class="flex-1 px-3 py-2 border rounded hover:bg-gray-50 text-sm"
            >
              {{ localDrawingBackgroundImage ? '更換背景圖片' : '選擇背景圖片' }}
            </button>
            <button
              v-if="localDrawingBackgroundImage"
              @click="removeDrawingBackgroundImage"
              type="button"
              class="px-3 py-2 border rounded hover:bg-red-50 text-red-600 text-sm"
            >
              移除
            </button>
          </div>
          <div v-if="localDrawingBackgroundImage" class="mt-2">
            <img :src="localDrawingBackgroundImage" class="w-full h-20 object-cover rounded border" />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">背景</label>
          <div class="flex gap-2">
            <button
              @click="openImageSelector"
              class="flex-1 px-3 py-2 border rounded hover:bg-gray-50 text-sm"
            >
              {{ localBackgroundImage ? '更換背景圖片' : '選擇背景圖片' }}
            </button>
            <button
              v-if="localBackgroundImage"
              @click="removeBackgroundImage"
              class="px-3 py-2 border rounded hover:bg-red-50 text-red-600 text-sm"
            >
              移除圖片
            </button>
          </div>
          <div v-if="localBackgroundImage" class="mt-2">
            <img :src="localBackgroundImage" class="w-full h-20 object-cover rounded border" />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">對齊方式</label>
          <div class="flex gap-2">
            <button
              type="button"
              @click="localAlign = 'left'"
              :class="[
                'flex-1 px-3 py-2 border rounded text-sm',
                localAlign === 'left' ? 'bg-blue-500 text-white' : 'hover:bg-gray-50'
              ]"
            >
              靠左
            </button>
            <button
              type="button"
              @click="localAlign = 'center'"
              :class="[
                'flex-1 px-3 py-2 border rounded text-sm',
                localAlign === 'center' ? 'bg-blue-500 text-white' : 'hover:bg-gray-50'
              ]"
            >
              置中
            </button>
            <button
              type="button"
              @click="localAlign = 'right'"
              :class="[
                'flex-1 px-3 py-2 border rounded text-sm',
                localAlign === 'right' ? 'bg-blue-500 text-white' : 'hover:bg-gray-50'
              ]"
            >
              靠右
            </button>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium mb-1">背景顏色</label>
            <input
              v-model="localColor"
              type="color"
              class="w-full h-10 border rounded cursor-pointer"
              :disabled="!!localBackgroundImage"
            />
            <p v-if="localBackgroundImage" class="text-xs text-gray-500 mt-1">使用圖片時無效</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">文字顏色</label>
            <input
              v-model="localTextColor"
              type="color"
              class="w-full h-10 border rounded cursor-pointer"
            />
          </div>
        </div>
        
        <div class="flex justify-between pt-2 border-t mt-3">
          <button
            @click="deleteButton"
            class="px-4 py-2 text-red-600 hover:text-red-700 border border-red-300 rounded hover:bg-red-50"
          >
            刪除按鈕
          </button>
          <div class="flex gap-2">
            <button
              @click="cancelEdit"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 border rounded"
            >
              取消
            </button>
            <button
              @click="saveEdit"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              確定
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Image Selector Modal -->
    <div v-if="showImageSelector" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showImageSelector = false">
      <div class="bg-white rounded-lg shadow-xl p-6 w-[800px] max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">選擇背景圖片</h3>
          <button @click="showImageSelector = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <input
            v-model="imageSearchQuery"
            @input="fetchImages"
            type="text"
            placeholder="搜尋圖片..."
            class="w-full px-3 py-2 border rounded"
          />
        </div>
        
        <div class="flex-1 overflow-y-auto">
          <div v-if="availableImages.length === 0" class="text-center text-gray-500 py-8">
            沒有找到圖片
          </div>
          <div v-else class="grid grid-cols-4 gap-4">
            <div
              v-for="image in availableImages"
              :key="image.id"
              @click="selectBackgroundImage(image.url)"
              class="cursor-pointer border rounded overflow-hidden hover:border-blue-500 hover:shadow-lg transition-all aspect-square"
            >
              <img :src="image.url" :alt="image.filename" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Drawing Background Image Selector Modal -->
    <div v-if="showDrawingImageSelector" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showDrawingImageSelector = false">
      <div class="bg-white rounded-lg shadow-xl p-6 w-[800px] max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">選擇塗鴉背景圖片</h3>
          <button @click="showDrawingImageSelector = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <input
            v-model="drawingImageSearchQuery"
            @input="fetchDrawingImages"
            type="text"
            placeholder="搜尋圖片..."
            class="w-full px-3 py-2 border rounded"
          />
        </div>
        
        <div class="flex-1 overflow-y-auto">
          <div v-if="drawingAvailableImages.length === 0" class="text-center text-gray-500 py-8">
            沒有找到圖片
          </div>
          <div v-else class="grid grid-cols-4 gap-4">
            <div
              v-for="image in drawingAvailableImages"
              :key="image.id"
              @click="selectDrawingBackgroundImage(image.url)"
              class="cursor-pointer border rounded overflow-hidden hover:border-blue-500 hover:shadow-lg transition-all aspect-square"
            >
              <img :src="image.url" :alt="image.filename" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup>
import { ref } from 'vue'
import { NodeViewWrapper } from '@tiptap/vue-3'
import { showConfirm } from '@/utils/sweetalert'

const props = defineProps({
  node: {
    type: Object,
    required: true,
  },
  updateAttributes: {
    type: Function,
    required: true,
  },
})

const editing = ref(false)
const localText = ref(props.node.attrs.text)
const localUrl = ref(props.node.attrs.url)
const localColor = ref(props.node.attrs.color)
const localTextColor = ref(props.node.attrs.textColor)
const localBackgroundImage = ref(props.node.attrs.backgroundImage)
const localDrawingBackgroundImage = ref(props.node.attrs.drawingBackgroundImage)
const localAlign = ref(props.node.attrs.align || 'center')
const buttonType = ref(props.node.attrs.drawingBackgroundImage ? 'drawing' : 'link')
const showImageSelector = ref(false)
const showDrawingImageSelector = ref(false)
const availableImages = ref([])
const drawingAvailableImages = ref([])
const imageSearchQuery = ref('')
const drawingImageSearchQuery = ref('')

const startEdit = () => {
  editing.value = true
  localText.value = props.node.attrs.text
  localUrl.value = props.node.attrs.url
  localColor.value = props.node.attrs.color
  localTextColor.value = props.node.attrs.textColor
  localBackgroundImage.value = props.node.attrs.backgroundImage
  localDrawingBackgroundImage.value = props.node.attrs.drawingBackgroundImage
  localAlign.value = props.node.attrs.align || 'center'
  buttonType.value = props.node.attrs.drawingBackgroundImage ? 'drawing' : 'link'
}

const saveEdit = () => {
  props.updateAttributes({
    text: localText.value,
    url: buttonType.value === 'link' ? localUrl.value : '#',
    color: localColor.value,
    textColor: localTextColor.value,
    backgroundImage: localBackgroundImage.value,
    drawingBackgroundImage: buttonType.value === 'drawing' ? localDrawingBackgroundImage.value : null,
    align: localAlign.value,
  })
  editing.value = false
}

const cancelEdit = () => {
  editing.value = false
}

const openImageSelector = async () => {
  showImageSelector.value = true
  await fetchImages()
}

const fetchImages = async () => {
  try {
    const params = {}
    if (imageSearchQuery.value) {
      params.search = imageSearchQuery.value
    }
    const response = await fetch(`/admin/photos?${new URLSearchParams(params)}`)
    const data = await response.json()
    availableImages.value = data.data || []
  } catch (error) {
    console.error('Failed to fetch images:', error)
    availableImages.value = []
  }
}

const selectBackgroundImage = (imageUrl) => {
  localBackgroundImage.value = imageUrl
  showImageSelector.value = false
}

const removeBackgroundImage = () => {
  localBackgroundImage.value = null
}

const openDrawingImageSelector = async () => {
  showDrawingImageSelector.value = true
  await fetchDrawingImages()
}

const fetchDrawingImages = async () => {
  try {
    const params = {}
    if (drawingImageSearchQuery.value) {
      params.search = drawingImageSearchQuery.value
    }
    const response = await fetch(`/admin/photos?${new URLSearchParams(params)}`)
    const data = await response.json()
    drawingAvailableImages.value = data.data || []
  } catch (error) {
    console.error('Failed to fetch images:', error)
    drawingAvailableImages.value = []
  }
}

const selectDrawingBackgroundImage = (imageUrl) => {
  localDrawingBackgroundImage.value = imageUrl
  showDrawingImageSelector.value = false
}

const removeDrawingBackgroundImage = () => {
  localDrawingBackgroundImage.value = null
}

const deleteButton = async () => {
  const result = await showConfirm('確定要刪除這個按鈕嗎？')
  if (result.isConfirmed) {
    const { editor, getPos } = props
    if (editor && typeof getPos === 'function') {
      const pos = getPos()
      editor.chain().focus().deleteRange({ from: pos, to: pos + props.node.nodeSize }).run()
    }
  }
}
</script>

<style scoped>
.button-node {
  user-select: none;
}

.button-preview {
  text-align: center;
}

.button-editor {
  max-width: 400px;
}
</style>
