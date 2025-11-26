<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { onMounted, ref, onBeforeUnmount } from 'vue'
import * as fabric from 'fabric'
import axios from 'axios'

const props = defineProps(nodeViewProps)

const canvasEl = ref(null)
let canvas = null
const drawMode = ref('free')
const drawColor = ref('#000000')
const fillColor = ref('#000000')
const enableFill = ref(false)
const fillOpacity = ref(100)
const strokeWidth = ref(2)
const strokeDashArray = ref([])
const imageBorderWidth = ref(0)
const imageBorderColor = ref('#000000')
const imageBorderStyle = ref('solid')
const bgImageInput = ref(null)
const showImageSelector = ref(false)
const availableImages = ref([])
const imageSearchQuery = ref('')
const editMode = ref('inline') // 'inline' or 'fullscreen'
let isDrawing = false
let startX, startY
let currentShape = null

const hexToRgba = (hex, opacity) => {
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  return `rgba(${r}, ${g}, ${b}, ${opacity / 100})`
}

onMounted(() => {
  if (canvasEl.value) {
    canvas = new fabric.Canvas(canvasEl.value, {
      isDrawingMode: true,
      width: 600,
      height: 600,
      backgroundColor: '#f3f4f6',
    })
    
    // Explicitly create and configure free drawing brush
    canvas.freeDrawingBrush = new fabric.PencilBrush(canvas)
    canvas.freeDrawingBrush.color = drawColor.value
    canvas.freeDrawingBrush.width = strokeWidth.value

    // Load existing data if present
    if (props.node.attrs.lines) {
      canvas.loadFromJSON(props.node.attrs.lines, () => {
        canvas.renderAll()
      })
    } else {
      // Force initial render
      canvas.renderAll()
    }

    // Save on drawing end
    canvas.on('path:created', () => {
      save()
    })
    
    canvas.on('object:modified', () => {
      save()
    })

    canvas.on('object:added', () => {
      save()
    })

    // Shape drawing events
    canvas.on('mouse:down', handleMouseDown)
    canvas.on('mouse:move', handleMouseMove)
    canvas.on('mouse:up', handleMouseUp)
    
    window.addEventListener('keydown', handleKeyDown)
  }
})

const handleKeyDown = (e) => {
  if (!canvas) return
  
  if (e.key === 'Delete' || e.key === 'Backspace') {
    // Avoid deleting if user is typing in an input
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return
    
    const activeObjects = canvas.getActiveObjects()
    if (activeObjects.length) {
      canvas.discardActiveObject()
      activeObjects.forEach((obj) => {
        canvas.remove(obj)
      })
      save()
    }
  }
}

const updateBrushSettings = () => {
  if (canvas) {
    canvas.freeDrawingBrush = new fabric.PencilBrush(canvas)
    canvas.freeDrawingBrush.color = drawColor.value
    canvas.freeDrawingBrush.width = strokeWidth.value
    canvas.freeDrawingBrush.strokeDashArray = strokeDashArray.value
    
    // 如果有選中的物件，也更新它們的樣式
    const activeObject = canvas.getActiveObject()
    if (activeObject) {
      const fill = enableFill.value ? hexToRgba(fillColor.value, fillOpacity.value) : 'transparent'
      activeObject.set({
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
        fill: fill
      })
      canvas.requestRenderAll()
      save()
    }
  }
}

const setStrokeStyle = (style) => {
  switch(style) {
    case 'solid':
      strokeDashArray.value = []
      break
    case 'dashed':
      strokeDashArray.value = [10, 5]
      break
    case 'dotted':
      strokeDashArray.value = [2, 5]
      break
  }
  updateBrushSettings()
}

const handleMouseDown = (e) => {
  if (drawMode.value === 'free' || drawMode.value === 'select') return
  
  isDrawing = true
  const pointer = canvas.getPointer(e.e)
  startX = pointer.x
  startY = pointer.y
  
  const fill = enableFill.value ? hexToRgba(fillColor.value, fillOpacity.value) : 'transparent'

  // Create initial shape
  switch (drawMode.value) {
    case 'line':
      currentShape = new fabric.Line([startX, startY, startX, startY], {
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
        selectable: false
      })
      break
    case 'rect':
      currentShape = new fabric.Rect({
        left: startX,
        top: startY,
        width: 0,
        height: 0,
        fill: fill,
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
        selectable: false
      })
      break
    case 'circle':
      currentShape = new fabric.Circle({
        left: startX,
        top: startY,
        radius: 0,
        fill: fill,
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
        selectable: false
      })
      break
    case 'triangle':
      currentShape = new fabric.Triangle({
        left: startX,
        top: startY,
        width: 0,
        height: 0,
        fill: fill,
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
        selectable: false
      })
      break
  }

  if (currentShape) {
    canvas.add(currentShape)
  }
}

const handleMouseMove = (e) => {
  if (!isDrawing || drawMode.value === 'free' || drawMode.value === 'select' || !currentShape) return

  const pointer = canvas.getPointer(e.e)
  
  switch (drawMode.value) {
    case 'line':
      currentShape.set({ x2: pointer.x, y2: pointer.y })
      break
    case 'rect':
      const width = pointer.x - startX
      const height = pointer.y - startY
      currentShape.set({
        width: Math.abs(width),
        height: Math.abs(height),
        left: width < 0 ? pointer.x : startX,
        top: height < 0 ? pointer.y : startY,
      })
      break
    case 'circle':
      const radius = Math.sqrt(Math.pow(pointer.x - startX, 2) + Math.pow(pointer.y - startY, 2)) / 2
      currentShape.set({ radius })
      break
    case 'triangle':
      const triWidth = pointer.x - startX
      const triHeight = pointer.y - startY
      currentShape.set({
        width: Math.abs(triWidth),
        height: Math.abs(triHeight),
        left: triWidth < 0 ? pointer.x : startX,
        top: triHeight < 0 ? pointer.y : startY,
      })
      break
  }

  canvas.renderAll()
}

const handleMouseUp = () => {
  if (drawMode.value === 'free' || drawMode.value === 'select') return
  
  isDrawing = false
  if (currentShape) {
    currentShape.setCoords()
    currentShape = null
  }
  save()
}

const setDrawMode = (mode) => {
  drawMode.value = mode
  canvas.isDrawingMode = mode === 'free'
  
  // Update selectability
  canvas.forEachObject(obj => {
    obj.selectable = mode === 'select'
    obj.evented = mode === 'select'
  })
  
  if (mode !== 'select') {
    canvas.discardActiveObject()
  }
  canvas.requestRenderAll()
}


const save = () => {
  if (canvas) {
    const json = canvas.toJSON()
    props.updateAttributes({ lines: json })
  }
}

const clear = () => {
  if (canvas) {
    canvas.clear()
    canvas.backgroundColor = '#f3f4f6'
    canvas.renderAll()
    save()
  }
}

const uploadBackground = () => {
  bgImageInput.value?.click()
}

const handleBackgroundUpload = (e) => {
  const file = e.target.files?.[0]
  if (!file || !canvas) return
  
  const reader = new FileReader()
  reader.onload = (event) => {
    fabric.FabricImage.fromURL(event.target.result).then((img) => {
      // 根據當前模式使用不同的寬度
      const targetWidth = editMode.value === 'fullscreen' ? 800 : canvas.width
      img.scaleToWidth(targetWidth)
      const scaledHeight = img.height * img.scaleX
      
      // 如果在全螢幕模式，確保畫布寬度是 800px
      if (editMode.value === 'fullscreen') {
        canvas.setWidth(800)
      }
      
      canvas.setHeight(scaledHeight)
      canvas.backgroundImage = img
      canvas.renderAll()
      save()
    })
  }
  reader.readAsDataURL(file)
  e.target.value = ''
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
    const response = await axios.get('/admin/photos', { params })
    availableImages.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch images:', error)
    availableImages.value = []
  }
}

const selectBackgroundImage = (imageUrl) => {
  if (!canvas) return
  
  fabric.FabricImage.fromURL(imageUrl).then((img) => {
    // 根據當前模式使用不同的寬度
    const targetWidth = editMode.value === 'fullscreen' ? 800 : canvas.width
    img.scaleToWidth(targetWidth)
    const scaledHeight = img.height * img.scaleX
    
    // 如果在全螢幕模式，確保畫布寬度是 800px
    if (editMode.value === 'fullscreen') {
      canvas.setWidth(800)
    }
    
    canvas.setHeight(scaledHeight)
    canvas.backgroundImage = img
    canvas.renderAll()
    save()
    showImageSelector.value = false
  })
}

const openFullscreenEditor = () => {
  editMode.value = 'fullscreen'
  
  // 切換到全螢幕時調整畫布大小為 800px
  if (canvas) {
    // 保存當前內容
    const currentData = canvas.toJSON()
    
    // 計算縮放比例
    const oldWidth = canvas.width
    const newWidth = 800
    const scale = newWidth / oldWidth
    
    // 設定新的畫布大小
    canvas.setWidth(newWidth)
    canvas.setHeight(canvas.height * scale)
    
    // 縮放所有物件
    canvas.getObjects().forEach(obj => {
      obj.scaleX = obj.scaleX * scale
      obj.scaleY = obj.scaleY * scale
      obj.left = obj.left * scale
      obj.top = obj.top * scale
      obj.setCoords()
    })
    
    // 如果有背景圖片，也要縮放
    if (canvas.backgroundImage) {
      canvas.backgroundImage.scaleX = canvas.backgroundImage.scaleX * scale
      canvas.backgroundImage.scaleY = canvas.backgroundImage.scaleY * scale
    }
    
    canvas.renderAll()
    save()
  }
}

const closeFullscreenEditor = () => {
  editMode.value = 'inline'
  
  // 返回內嵌模式時調整回 600px
  if (canvas) {
    const oldWidth = canvas.width
    const newWidth = 600
    const scale = newWidth / oldWidth
    
    canvas.setWidth(newWidth)
    canvas.setHeight(canvas.height * scale)
    
    canvas.getObjects().forEach(obj => {
      obj.scaleX = obj.scaleX * scale
      obj.scaleY = obj.scaleY * scale
      obj.left = obj.left * scale
      obj.top = obj.top * scale
      obj.setCoords()
    })
    
    if (canvas.backgroundImage) {
      canvas.backgroundImage.scaleX = canvas.backgroundImage.scaleX * scale
      canvas.backgroundImage.scaleY = canvas.backgroundImage.scaleY * scale
    }
    
    canvas.renderAll()
    save()
  }
}

const saveAsImage = () => {
  if (!canvas) return
  
  const dataUrl = canvas.toDataURL({
    format: 'png',
    quality: 1,
  })
  
  const { editor, getPos } = props
  
  if (editor && typeof getPos === 'function') {
    const pos = getPos()
    
    // 建立圖片屬性
    const imageAttrs = {
      src: dataUrl,
      'data-align': 'left',
      'data-from-drawing': 'true',
    }
    
    // 如果有框線設定，添加框線屬性
    if (imageBorderWidth.value > 0) {
      imageAttrs['data-border-width'] = imageBorderWidth.value
      imageAttrs['data-border-color'] = imageBorderColor.value
      imageAttrs['data-border-style'] = imageBorderStyle.value
    }
    
    editor.chain()
      .focus()
      .deleteRange({ from: pos, to: pos + props.node.nodeSize })
      .insertContentAt(pos, {
        type: 'image',
        attrs: imageAttrs,
      })
      .run()
    
    // 如果在全螢幕模式，關閉它
    if (editMode.value === 'fullscreen') {
      editMode.value = 'inline'
    }
  }
}

const deleteAttributes = () => {
  const { editor, getPos } = props
  if (editor && typeof getPos === 'function') {
    const pos = getPos()
    editor.chain().focus().deleteRange({ from: pos, to: pos + props.node.nodeSize }).run()
  }
}

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown)
  if (canvas) {
    canvas.dispose()
  }
})
</script>

<template>
  <node-view-wrapper class="drawing-node my-4 border rounded shadow-sm block bg-white p-2" style="width: fit-content; max-width: 100%;">
    <!-- Inline Mode Preview -->
    <div v-if="editMode === 'inline'" class="inline-mode">
      <div class="controls mb-2 flex gap-2 items-center flex-wrap">
      <span class="text-xs text-gray-600 font-medium">工具:</span>
      <button 
        @click="setDrawMode('select')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'select' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        選取
      </button>
      <button 
        @click="setDrawMode('free')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'free' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        自由繪畫
      </button>
      <button 
        @click="setDrawMode('line')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'line' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        直線
      </button>
      <button 
        @click="setDrawMode('rect')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'rect' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        矩形
      </button>
      <button 
        @click="setDrawMode('circle')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'circle' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        圓形
      </button>
      <button 
        @click="setDrawMode('triangle')" 
        :class="{ 'bg-blue-500 text-white': drawMode === 'triangle' }"
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
      >
        三角形
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <span class="text-xs text-gray-600 font-medium">邊框:</span>
      <input 
        type="color" 
        v-model="drawColor"
        @input="updateBrushSettings"
        class="w-8 h-6 border rounded cursor-pointer"
        title="選擇邊框顏色"
      />
      
      <div class="flex items-center gap-1 ml-2">
        <input 
          type="checkbox" 
          id="enableFill"
          v-model="enableFill"
          class="w-3 h-3"
        />
        <label for="enableFill" class="text-xs text-gray-600 font-medium cursor-pointer">填滿:</label>
        <input 
          type="color" 
          v-model="fillColor"
          :disabled="!enableFill"
          @input="updateBrushSettings"
          class="w-8 h-6 border rounded cursor-pointer disabled:opacity-50"
          title="選擇填滿顏色"
        />
        <input 
          type="range" 
          v-model.number="fillOpacity"
          :disabled="!enableFill"
          @input="updateBrushSettings"
          min="0"
          max="100"
          class="w-16 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer disabled:opacity-50"
          title="填滿透明度"
        />
        <span class="text-xs text-gray-500 w-6">{{ fillOpacity }}%</span>
      </div>
      
      <span class="text-xs text-gray-600 font-medium ml-2">粗細:</span>
      <input 
        type="range" 
        v-model.number="strokeWidth"
        @input="updateBrushSettings"
        min="1"
        max="20"
        class="w-20 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer"
      />
      <span class="text-xs text-gray-500 w-6">{{ strokeWidth }}</span>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <span class="text-xs text-gray-600 font-medium">線條:</span>
      <button 
        @click="setStrokeStyle('solid')" 
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
        title="實線"
      >
        ──
      </button>
      <button 
        @click="setStrokeStyle('dashed')" 
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
        title="虛線"
      >
        ━ ━
      </button>
      <button 
        @click="setStrokeStyle('dotted')" 
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
        title="點線"
      >
        ・・・
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <input 
        ref="bgImageInput" 
        type="file" 
        accept="image/*" 
        @change="handleBackgroundUpload"
        class="hidden"
      />
      <button 
        @click="uploadBackground" 
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
        title="上傳背景圖片"
      >
        上傳背景
      </button>
      <button 
        @click="openImageSelector" 
        class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
        title="選擇背景圖片"
      >
        選擇背景
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <span class="text-xs text-gray-600 font-medium">圖片框線:</span>
      <input 
        type="number" 
        v-model.number="imageBorderWidth"
        min="0"
        max="20"
        class="w-12 text-xs px-1 py-1 border rounded"
        placeholder="0"
      />
      <span class="text-xs text-gray-500">px</span>
      <input 
        type="color" 
        v-model="imageBorderColor"
        :disabled="imageBorderWidth === 0"
        class="w-8 h-6 border rounded cursor-pointer"
        title="框線顏色"
      />
      <select 
        v-model="imageBorderStyle"
        :disabled="imageBorderWidth === 0"
        class="text-xs px-1 py-1 border rounded"
      >
        <option value="solid">實線</option>
        <option value="dashed">虛線</option>
        <option value="dotted">點線</option>
        <option value="double">雙線</option>
      </select>
      
      <div class="flex-1"></div>
      <button @click="openFullscreenEditor" class="text-xs text-purple-500 hover:text-purple-700 px-2 py-1 border rounded bg-purple-50 hover:bg-purple-100">
        全螢幕編輯
      </button>
      <button @click="saveAsImage" class="text-xs text-blue-500 hover:text-blue-700 px-2 py-1 border rounded bg-blue-50 hover:bg-blue-100">
        儲存為圖片
      </button>
      <button @click="clear" class="text-xs text-red-500 hover:text-red-700 px-2 py-1 border rounded">
        清除
      </button>
      <button @click="deleteAttributes" class="text-xs text-gray-600 hover:text-gray-800 px-2 py-1 border rounded">
        取消
      </button>
    </div>
    <div class="canvas-container w-full overflow-auto">
      <canvas ref="canvasEl" class="w-full"></canvas>
    </div>
    </div>
    
    <!-- Fullscreen Mode -->
    <div v-else class="fixed inset-0 z-50 bg-gray-900 flex flex-col">
      <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <h2 class="text-xl font-bold">塗鴉編輯器</h2>
        <div class="flex gap-2">
          <button @click="saveAsImage" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded text-white">
            儲存為圖片
          </button>
          <button @click="closeFullscreenEditor" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white">
            返回編輯器
          </button>
        </div>
      </div>
      
      <div class="flex-1 overflow-auto bg-gray-700 p-4">
        <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-xl p-4">
          <div class="controls mb-4 flex gap-2 items-center flex-wrap bg-gray-50 p-3 rounded">
            <span class="text-sm text-gray-700 font-medium">工具:</span>
            <button 
              @click="setDrawMode('select')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'select' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              選取
            </button>
            <button 
              @click="setDrawMode('free')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'free' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              自由繪畫
            </button>
            <button 
              @click="setDrawMode('line')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'line' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              直線
            </button>
            <button 
              @click="setDrawMode('rect')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'rect' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              矩形
            </button>
            <button 
              @click="setDrawMode('circle')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'circle' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              圓形
            </button>
            <button 
              @click="setDrawMode('triangle')" 
              :class="{ 'bg-blue-500 text-white': drawMode === 'triangle' }"
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              三角形
            </button>
            
            <div class="w-px h-8 bg-gray-300 mx-2"></div>
            
            <span class="text-sm text-gray-700 font-medium">邊框:</span>
            <input 
              type="color" 
              v-model="drawColor"
              @input="updateBrushSettings"
              class="w-10 h-8 border rounded cursor-pointer"
            />
            
            <div class="flex items-center gap-2 ml-2">
              <input 
                type="checkbox" 
                id="enableFillFs"
                v-model="enableFill"
                class="w-4 h-4"
              />
              <label for="enableFillFs" class="text-sm text-gray-700 font-medium cursor-pointer">填滿:</label>
              <input 
                type="color" 
                v-model="fillColor"
                :disabled="!enableFill"
                @input="updateBrushSettings"
                class="w-10 h-8 border rounded cursor-pointer disabled:opacity-50"
              />
              <input 
                type="range" 
                v-model.number="fillOpacity"
                :disabled="!enableFill"
                @input="updateBrushSettings"
                min="0"
                max="100"
                class="w-24 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer disabled:opacity-50"
              />
              <span class="text-sm text-gray-600 w-8">{{ fillOpacity }}%</span>
            </div>
            
            <span class="text-sm text-gray-700 font-medium ml-2">粗細:</span>
            <input 
              type="range" 
              v-model.number="strokeWidth"
              @input="updateBrushSettings"
              min="1"
              max="20"
              class="w-32 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
            />
            <span class="text-sm text-gray-600 w-8">{{ strokeWidth }}</span>
            
            <div class="w-px h-8 bg-gray-300 mx-2"></div>
            
            <span class="text-sm text-gray-700 font-medium">線條:</span>
            <button 
              @click="setStrokeStyle('solid')" 
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              ──
            </button>
            <button 
              @click="setStrokeStyle('dashed')" 
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              ━ ━
            </button>
            <button 
              @click="setStrokeStyle('dotted')" 
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              ・・・
            </button>
            
            <div class="w-px h-8 bg-gray-300 mx-2"></div>
            
            <button 
              @click="uploadBackground" 
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              上傳背景
            </button>
            <button 
              @click="openImageSelector" 
              class="text-sm px-3 py-2 border rounded hover:bg-gray-100"
            >
              選擇背景
            </button>
            
            <div class="w-px h-8 bg-gray-300 mx-2"></div>
            
            <span class="text-sm text-gray-700 font-medium">圖片框線:</span>
            <input 
              type="number" 
              v-model.number="imageBorderWidth"
              min="0"
              max="20"
              class="w-16 text-sm px-2 py-2 border rounded"
            />
            <span class="text-sm text-gray-600">px</span>
            <input 
              type="color" 
              v-model="imageBorderColor"
              :disabled="imageBorderWidth === 0"
              class="w-10 h-8 border rounded cursor-pointer"
            />
            <select 
              v-model="imageBorderStyle"
              :disabled="imageBorderWidth === 0"
              class="text-sm px-2 py-2 border rounded"
            >
              <option value="solid">實線</option>
              <option value="dashed">虛線</option>
              <option value="dotted">點線</option>
              <option value="double">雙線</option>
            </select>
            
            <div class="flex-1"></div>
            
            <button @click="clear" class="text-sm text-red-600 hover:text-red-700 px-3 py-2 border border-red-300 rounded hover:bg-red-50">
              清除
            </button>
          </div>
          
          <div class="canvas-container-fullscreen flex justify-center">
            <canvas ref="canvasEl" class="border shadow-lg"></canvas>
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
  </node-view-wrapper>
</template>

<style scoped>
.drawing-node {
  width: fit-content;
  max-width: 100%;
}

.canvas-container {
  width: fit-content;
  max-width: 100%;
  overflow: visible;
}

.canvas-container-fullscreen {
  width: 100%;
  overflow: auto;
}
</style>
