<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { onMounted, ref, onBeforeUnmount } from 'vue'
import * as fabric from 'fabric'

const props = defineProps(nodeViewProps)

const canvasEl = ref(null)
let canvas = null
const drawMode = ref('free')
const drawColor = ref('#000000')
const strokeWidth = ref(2)
const imageBorderWidth = ref(0)
const imageBorderColor = ref('#000000')
const bgImageInput = ref(null)
let isDrawing = false
let startX, startY
let currentShape = null

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
  }
})

const updateBrushSettings = () => {
  if (canvas) {
    canvas.freeDrawingBrush = new fabric.PencilBrush(canvas)
    canvas.freeDrawingBrush.color = drawColor.value
    canvas.freeDrawingBrush.width = strokeWidth.value
  }
}

const handleMouseDown = (e) => {
  if (drawMode.value === 'free') return
  
  isDrawing = true
  const pointer = canvas.getPointer(e.e)
  startX = pointer.x
  startY = pointer.y

  // Create initial shape
  switch (drawMode.value) {
    case 'line':
      currentShape = new fabric.Line([startX, startY, startX, startY], {
        stroke: drawColor.value,
        strokeWidth: strokeWidth.value,
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
  save()
}

const setDrawMode = (mode) => {
  drawMode.value = mode
  canvas.isDrawingMode = mode === 'free'
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
      img.scaleToWidth(canvas.width)
      img.scaleToHeight(canvas.height)
      canvas.backgroundImage = img
      canvas.renderAll()
      save()
    })
  }
  reader.readAsDataURL(file)
  e.target.value = ''
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
    
    // 建立框線樣式
    const borderStyle = imageBorderWidth.value > 0 
      ? `border: ${imageBorderWidth.value}px solid ${imageBorderColor.value};` 
      : ''
    
    editor.chain()
      .focus()
      .deleteRange({ from: pos, to: pos + props.node.nodeSize })
      .insertContentAt(pos, {
        type: 'image',
        attrs: {
          src: dataUrl,
          style: `max-width: 100%; height: auto; ${borderStyle}`,
          'data-border-width': imageBorderWidth.value,
          'data-border-color': imageBorderColor.value
        },
      })
      .run()
  }
}

onBeforeUnmount(() => {
  if (canvas) {
    canvas.dispose()
  }
})
</script>

<template>
  <node-view-wrapper class="drawing-node my-4 border rounded shadow-sm block bg-white p-2" style="width: fit-content; max-width: 100%;">
    <div class="controls mb-2 flex gap-2 items-center flex-wrap">
      <span class="text-xs text-gray-600 font-medium">工具:</span>
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
      
      <span class="text-xs text-gray-600 font-medium">顏色:</span>
      <input 
        type="color" 
        v-model="drawColor"
        @input="updateBrushSettings"
        class="w-8 h-6 border rounded cursor-pointer"
        title="選擇繪圖顏色"
      />
      
      <span class="text-xs text-gray-600 font-medium">粗細:</span>
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
        背景圖
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
      
      <div class="flex-1"></div>
      <button @click="saveAsImage" class="text-xs text-blue-500 hover:text-blue-700 px-2 py-1 border rounded bg-blue-50 hover:bg-blue-100">
        儲存為圖片
      </button>
      <button @click="clear" class="text-xs text-red-500 hover:text-red-700 px-2 py-1 border rounded">
        清除
      </button>
    </div>
    <div class="canvas-container w-full overflow-auto">
      <canvas ref="canvasEl" class="w-full"></canvas>
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
</style>
