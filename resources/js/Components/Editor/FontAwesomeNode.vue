<template>
  <node-view-wrapper 
    as="span" 
    class="fa-icon-wrapper inline-block" 
    :class="{ 'selected': selected }"
    contenteditable="false"
  >
    <i 
      :class="node.attrs.icon" 
      :style="{ color: node.attrs.color, fontSize: node.attrs.size }"
    ></i>
  </node-view-wrapper>
</template>

<script setup>
import { NodeViewWrapper } from '@tiptap/vue-3'

defineProps({
  node: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.fa-icon-wrapper {
  cursor: pointer;
  padding: 2px;
  border-radius: 2px;
  transition: background-color 0.2s;
}

.fa-icon-wrapper:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.fa-icon-wrapper.selected {
  background-color: rgba(59, 130, 246, 0.2);
  outline: 2px solid rgb(59, 130, 246);
}
</style>
