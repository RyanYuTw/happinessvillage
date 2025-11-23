<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { onMounted, ref, onBeforeUnmount } from 'vue'
import * as fabric from 'fabric'

const props = defineProps(nodeViewProps)

const canvasEl = ref(null)
const containerEl = ref(null)
const fileInput = ref(null)
let canvas = null
const imageUrl = ref(props.node.attrs.imageUrl || '')
const inputs = ref(props.node.attrs.inputs || [])

onMounted(() => {
  initCanvas()
})

const initCanvas = () => {
  if (canvasEl.value) {
    canvas = new fabric.Canvas(canvasEl.value, {
      isDrawingMode: true,
      width: 500,
      height: 400, // Default, will adjust to image
    })

    if (imageUrl.value) {
      loadImage(imageUrl.value)
    }

    if (props.node.attrs.drawing) {
      canvas.loadFromJSON(props.node.attrs.drawing, canvas.renderAll.bind(canvas))
    }

    canvas.on('path:created', save)
    canvas.on('object:modified', save)
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      imageUrl.value = e.target.result
      loadImage(imageUrl.value)
      save()
    }
    reader.readAsDataURL(file)
  }
}

const loadImage = (url) => {
  fabric.Image.fromURL(url, (img) => {
    if (!canvas) return
    
    // Resize canvas to match image (max width 600)
    const maxWidth = 600
    let width = img.width
    let height = img.height
    
    if (width > maxWidth) {
      const scale = maxWidth / width
      width = maxWidth
      height = height * scale
      img.scale(scale)
    }
    
    canvas.setWidth(width)
    canvas.setHeight(height)
    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas))
  })
}

const addInput = () => {
  inputs.value.push({
    x: 50,
    y: 50,
    width: 150,
    value: '',
    placeholder: 'Input here',
    id: Date.now()
  })
  save()
}

const updateInput = (index, key, value) => {
  inputs.value[index][key] = value
  save()
}

const removeInput = (index) => {
  inputs.value.splice(index, 1)
  save()
}

const save = () => {
  if (canvas) {
    props.updateAttributes({
      imageUrl: imageUrl.value,
      drawing: canvas.toJSON(),
      inputs: inputs.value
    })
  }
}

const clearDrawing = () => {
  if (canvas) {
    canvas.clear()
    if (imageUrl.value) {
      loadImage(imageUrl.value)
    }
    save()
  }
}

onBeforeUnmount(() => {
  if (canvas) {
    canvas.dispose()
  }
})
</script>

<template>
  <node-view-wrapper class="image-annotation-node my-4 border rounded shadow-sm inline-block bg-white p-2 relative">
    <div v-if="!imageUrl" class="p-8 text-center border-2 border-dashed rounded bg-gray-50">
      <p class="mb-2 text-gray-500">上傳圖片以進行標註</p>
      <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/*" />
    </div>

    <div v-else ref="containerEl" class="relative">
      <div class="controls mb-1 flex gap-2 justify-end">
        <button @click="addInput" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">新增輸入框</button>
        <button @click="clearDrawing" class="text-xs text-red-500 border px-2 py-1 rounded">清除塗鴉</button>
        <button @click="imageUrl = ''; inputs = []" class="text-xs text-gray-500 border px-2 py-1 rounded">更換圖片</button>
      </div>
      
      <div class="canvas-wrapper relative">
        <canvas ref="canvasEl"></canvas>
        
        <!-- Overlay Inputs -->
        <div
          v-for="(input, index) in inputs"
          :key="input.id"
          class="absolute"
          :style="{ left: input.x + 'px', top: input.y + 'px', width: input.width + 'px' }"
        >
           <!-- Simple drag handle simulation (in real app use a drag lib) -->
           <div class="drag-handle bg-gray-200 h-4 cursor-move flex justify-between px-1 items-center text-[10px]">
             <span>拖曳 ({{input.x}}, {{input.y}})</span>
             <button @click="removeInput(index)" class="text-red-500">x</button>
           </div>
           <input
             type="text"
             :value="input.value"
             @input="updateInput(index, 'value', $event.target.value)"
             :placeholder="input.placeholder"
             class="w-full border border-gray-300 text-sm px-1 bg-white/80"
           />
           <!-- Controls to move (simplified for demo) -->
           <div class="flex gap-1 mt-1">
             <input type="number" v-model.number="input.x" @change="save" class="w-12 text-[10px] border" placeholder="X">
             <input type="number" v-model.number="input.y" @change="save" class="w-12 text-[10px] border" placeholder="Y">
           </div>
        </div>
      </div>
    </div>
  </node-view-wrapper>
</template>
