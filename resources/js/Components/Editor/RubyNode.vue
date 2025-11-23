<script setup>
import { computed } from 'vue'
import { NodeViewWrapper, NodeViewContent, nodeViewProps } from '@tiptap/vue-3'

const props = defineProps(nodeViewProps)

const parsedZhuyin = computed(() => {
  const rt = props.node.attrs.rt || ''
  let tone = ''
  let symbols = rt

  // Check for neutral tone (usually at start)
  if (rt.startsWith('˙')) {
    tone = '˙'
    symbols = rt.substring(1)
  } 
  // Check for other tones (usually at end)
  else if (['ˊ', 'ˇ', 'ˋ'].includes(rt.slice(-1))) {
    tone = rt.slice(-1)
    symbols = rt.slice(0, -1)
  }

  return { tone, symbols }
})
</script>

<template>
  <node-view-wrapper as="ruby" class="inline-ruby">
    <node-view-content as="span" />
    <rt class="zhuyin-rt" :style="{ zoom: node.attrs.size }" contenteditable="false">
      <div class="zhuyin-container">
        <!-- Right Column (in vertical-rl): Side Tone -->
        <div v-if="['ˊ', 'ˇ', 'ˋ'].includes(parsedZhuyin.tone)" class="side-tone">
          {{ parsedZhuyin.tone }}
        </div>
        
        <!-- Left Column (in vertical-rl): Symbols + Neutral -->
        <div class="symbols-group">
          <!-- Neutral Tone (Top) -->
          <div v-if="parsedZhuyin.tone === '˙'" class="neutral-tone">˙</div>
          <!-- Symbols -->
          <span v-for="(char, index) in parsedZhuyin.symbols" :key="index" class="symbol-char">
            {{ char }}
          </span>
        </div>
      </div>
    </rt>
  </node-view-wrapper>
</template>

<style>
.inline-ruby {
  display: inline-flex !important;
  flex-direction: row !important;
  align-items: center !important;
  vertical-align: baseline !important;
  margin: 0 0.15em !important;
  line-height: 1.8 !important;
}

.zhuyin-rt {
  display: inline-block !important;
  line-height: 1 !important;
  font-family: "Bopomofo", system-ui, sans-serif !important;
  margin-left: 0.15em !important;
  font-size: 1em !important;
}

.zhuyin-container {
  display: inline-flex !important;
  writing-mode: vertical-rl !important;
  text-orientation: upright !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  white-space: nowrap !important;
}

.symbols-group {
  display: inline-flex !important;
  flex-direction: row !important;
  align-items: center !important;
  writing-mode: vertical-rl !important;
  text-orientation: upright !important;
}

.neutral-tone {
  font-size: 1em;
  line-height: 1;
  order: -1;
  margin-bottom: 0.2em;
}

.symbol-char {
  line-height: 1;
}

.side-tone {
  font-size: 1em;
  line-height: 1;
  align-self: center;
  margin-left: -0.1em;
  transform: translate(-2px, 3px);
}
</style>
