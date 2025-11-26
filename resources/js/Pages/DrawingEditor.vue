<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">塗鴉編輯器</h1>
      <div class="flex gap-2">
        <button 
          @click="saveAsImage"
          class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded text-white"
        >
          儲存並返回
        </button>
        <button 
          @click="closeEditor"
          class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white"
        >
          取消
        </button>
      </div>
    </div>
    
    <div class="flex-1 overflow-auto p-6">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6">
        <!-- 工具列 -->
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
          
          <span class="text-sm text-gray-700 font-medium">顏色:</span>
          <input 
            type="color" 
            v-model="drawColor"
            @input="updateBrushSettings"
            class="w-10 h-8 border rounded cursor-pointer"
          />
          
          <span class="text-sm text-gray-700 font-medium">粗細:</span>
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
          
          <div class="flex-1"></div>
          
          <button 
            @click="clear" 
            class="text-sm text-red-600 hover:text-red-700 px-3 py-2 border border-red-300 rounded hover:bg-red-50"
          >
            清除
          </button>
        </div>
        
        <!-- 畫布 -->
        <div class="flex justify-center">
          <canvas ref="canvasEl" class="border shadow-lg"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import * as fabric from 'fabric'

const props = defineProps({
  backgroundImage: String,
  returnUrl: String,
})

const canvasEl = ref(null)
let canvas = null
const drawMode = ref('free')
const drawColor = ref('#000000')
const strokeWidth = ref(2)
const strokeDashArray = ref([])
let isDrawing = false
let startX, startY
let currentShape = null

onMounted(() => {
  if (canvasEl.value) {
    canvas = new fabric.Canvas(canvasEl.value, {
      isDrawingMode: true,
      width: 800,
      height: 600,
      backgroundColor: '#f3f4f6',
    })
    
    canvas.freeDrawingBrush = new fabric.PencilBrush(canvas)
    canvas.freeDrawingBrush.color = drawColor.value
    canvas.freeDrawingBrush.width = strokeWidth.value
    
    // 如果有背景圖片，載入它
    if (props.backgroundImage) {
      fabric.FabricImage.fromURL(props.backgroundImage).then((img) => {
        img.scaleToWidth(800)
        const scaledHeight = img.height * img.scaleX
        canvas.setHeight(scaledHeight)
        canvas.backgroundImage = img
        canvas.renderAll()
      })
    }
    
    canvas.on('path:created', () => {})
    canvas.on('object:modified', () => {})
    canvas.on('object:added', () => {})
    canvas.on('mouse:down', handleMouseDown)
    canvas.on('mouse:move', handleMouseMove)
    canvas.on('mouse:up', handleMouseUp)
    
    canvas.renderAll()
  }
})

const updateBrushSettings = () => {
  if (canvas) {
    canvas.freeDrawingBrush = new fabric.PencilBrush(canvas)
    canvas.freeDrawingBrush.color = drawColor.value
    canvas.freeDrawingBrush.width = strokeWidth.value
    canvas.freeDrawingBrush.strokeDashArray = strokeDashArray.value
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

const setDrawMode = (mode) => {
  drawMode.value = mode
  canvas.isDrawingMode = mode === 'free'
}

const handleMouseDown = (e) => {
  if (drawMode.value === 'free') return
  
  isDrawing = true
  const pointer = canvas.getPointer(e.e)
  startX = pointer.x
  startY = pointer.y

  switch (drawMode.value) {
    case 'line':
      currentShape = new fabric.Line([startX, startY, startX, startY], {
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
      })
      break
    case 'rect':
      currentShape = new fabric.Rect({
        left: startX,
        top: startY,
        width: 0,
        height: 0,
        fill: 'transparent',
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
      })
      break
    case 'circle':
      currentShape = new fabric.Circle({
        left: startX,
        top: startY,
        radius: 0,
        fill: 'transparent',
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
      })
      break
    case 'triangle':
      currentShape = new fabric.Triangle({
        left: startX,
        top: startY,
        width: 0,
        height: 0,
        fill: 'transparent',
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
        strokeDashArray: strokeDashArray.value,
      })
      break
  }

  if (currentShape) {
    canvas.add(currentShape)
  }
}

const handleMouseMove = (e) => {
  if (!isDrawing || drawMode.value === 'free' || !currentShape) return

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
  if (drawMode.value === 'free') return
  isDrawing = false
  currentShape = null
}

const clear = () => {
  if (canvas) {
    canvas.clear()
    canvas.backgroundColor = '#f3f4f6'
    
    // 重新載入背景圖片
    if (props.backgroundImage) {
      fabric.FabricImage.fromURL(props.backgroundImage).then((img) => {
        img.scaleToWidth(800)
        const scaledHeight = img.height * img.scaleX
        canvas.setHeight(scaledHeight)
        canvas.backgroundImage = img
        canvas.renderAll()
      })
    } else {
      canvas.renderAll()
    }
  }
}

const saveAsImage = () => {
  if (!canvas) return
  
  const dataUrl = canvas.toDataURL({
    format: 'png',
    quality: 1,
  })
  
  // 將圖片資料存到 sessionStorage
  sessionStorage.setItem('drawingResult', dataUrl)
  
  // 返回編輯器
  if (props.returnUrl) {
    window.location.href = props.returnUrl
  } else {
    window.close()
  }
}

const closeEditor = () => {
  if (props.returnUrl) {
    window.location.href = props.returnUrl
  } else {
    window.close()
  }
}

onBeforeUnmount(() => {
  if (canvas) {
    canvas.dispose()
  }
})
</script>
