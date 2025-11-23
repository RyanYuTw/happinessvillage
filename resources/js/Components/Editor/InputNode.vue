<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'

const props = defineProps(nodeViewProps)
</script>

<template>
  <node-view-wrapper class="inline-block mx-1">
    <input
      type="text"
      :value="node.attrs.value"
      @input="updateAttributes({ value: $event.target.value })"
      :placeholder="node.attrs.placeholder"
      class="border border-gray-300 rounded px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
      style="width: 30px;"
    />
  </node-view-wrapper>
</template>
