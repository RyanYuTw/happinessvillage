<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  ...nodeViewProps,
  selected: Boolean
})

const deleteNode = () => {
  props.deleteNode()
}

const isDragging = ref(false)
const isEditing = ref(false)
const isSelected = ref(false)
const startX = ref(0)
const startY = ref(0)
const offsetX = ref(props.node.attrs.x || 100)
const offsetY = ref(props.node.attrs.y || 100)
const inputRef = ref(null)

const handleMouseDown = (e) => {
  if (isEditing.value) return
  isSelected.value = true
  isDragging.value = true
  startX.value = e.clientX - offsetX.value
  startY.value = e.clientY - offsetY.value
  e.preventDefault()
}

const handleDoubleClick = () => {
  isEditing.value = true
  setTimeout(() => {
    inputRef.value?.focus()
  }, 0)
}

const handleBlur = () => {
  isEditing.value = false
}

const handleClickOutside = (e) => {
  const wrapper = e.target.closest('.input-field-wrapper')
  if (!wrapper) {
    isSelected.value = false
  }
}

const handleMouseMove = (e) => {
  if (!isDragging.value) return
  offsetX.value = e.clientX - startX.value
  offsetY.value = e.clientY - startY.value
  props.updateAttributes({ x: offsetX.value, y: offsetY.value })
}

const handleMouseUp = () => {
  isDragging.value = false
}

onMounted(() => {
  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <node-view-wrapper 
    class="absolute group input-field-wrapper"
    :class="{ 'ring-2 ring-blue-500': isSelected, 'cursor-move': !isEditing, 'cursor-text': isEditing }"
    :style="{ left: offsetX + 'px', top: offsetY + 'px', zIndex: 1000 }"
    @mousedown="handleMouseDown"
    @dblclick="handleDoubleClick"
  >
    <button
      v-if="isSelected && !isEditing"
      @click="deleteNode"
      class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
      style="z-index: 1001;"
    >
      ×
    </button>
    <input
      ref="inputRef"
      type="text"
      :value="node.attrs.value"
      @input="updateAttributes({ value: $event.target.value })"
      @blur="handleBlur"
      :readonly="!isEditing"
      class="border border-gray-300 rounded px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none bg-white"
      :class="{ 'pointer-events-none': !isEditing }"
      style="width: 30px;"
    />
  </node-view-wrapper>
</template>
