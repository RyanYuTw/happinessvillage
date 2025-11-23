<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  ...nodeViewProps,
  selected: Boolean
})

const isInTable = computed(() => {
  return props.editor.isActive('tableCell') || props.editor.isActive('tableHeader')
})

const deleteNode = () => {
  props.deleteNode()
}

const isDragging = ref(false)
const isEditing = ref(false)
const isSelected = ref(false)
const startX = ref(0)
const startY = ref(0)
const offsetX = computed(() => isInTable.value ? 0 : (props.node.attrs.x || 100))
const offsetY = computed(() => isInTable.value ? 0 : (props.node.attrs.y || 100))
const inputRef = ref(null)

const handleMouseDown = (e) => {
  if (isEditing.value) return
  
  if (!isInTable.value) {
    isSelected.value = true
    isDragging.value = true
    const prosemirror = document.querySelector('.ProseMirror')
    const rect = prosemirror.getBoundingClientRect()
    startX.value = e.clientX - rect.left - offsetX.value
    startY.value = e.clientY - rect.top - offsetY.value
    e.preventDefault()
  } else {
    if (e.target.tagName === 'INPUT') {
      isSelected.value = true
    }
  }
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

const handleClick = (e) => {
  if (!isEditing.value && isInTable.value) {
    isSelected.value = true
  }
}

const handleClickOutside = (e) => {
  const wrapper = e.target.closest('.input-field-wrapper')
  if (!wrapper) {
    isSelected.value = false
  }
}

const handleKeyDown = (e) => {
  if (isInTable.value && isSelected.value && !isEditing.value) {
    if (e.key === 'Delete' || e.key === 'Backspace') {
      deleteNode()
      e.preventDefault()
    }
  }
}

const handleMouseMove = (e) => {
  if (!isDragging.value) return
  const prosemirror = document.querySelector('.ProseMirror')
  const rect = prosemirror.getBoundingClientRect()
  const newX = e.clientX - rect.left - startX.value
  const newY = e.clientY - rect.top - startY.value
  props.updateAttributes({ x: newX, y: newY })
}

const handleMouseUp = () => {
  isDragging.value = false
}

onMounted(() => {
  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<template>
  <node-view-wrapper 
    class="group input-field-wrapper"
    :class="[
      isInTable ? 'inline-block' : 'absolute',
      { 'ring-2 ring-blue-500': isSelected, 'cursor-move': !isEditing && !isInTable, 'cursor-text': isEditing, 'cursor-pointer': isInTable && !isEditing }
    ]"
    :style="isInTable ? {} : { left: offsetX + 'px', top: offsetY + 'px', zIndex: 1000 }"
    @mousedown.stop="handleMouseDown"
    @click.stop="handleClick"
    @dblclick.stop="handleDoubleClick"
  >
    <button
      v-if="isSelected && !isEditing"
      @click="deleteNode"
      :class="isInTable ? 'relative ml-1' : 'absolute -top-2 -right-2'"
      class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
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
