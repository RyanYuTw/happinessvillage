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
  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  margin: 0 2px;
}

.zhuyin-rt {
  display: inline-block;
  line-height: 1;
  font-family: "Bopomofo", system-ui, sans-serif;
  text-align: center;
  vertical-align: middle;
  font-size: 1em; /* Base size for zoom scaling */
}

.zhuyin-container {
  display: inline-flex;
  writing-mode: vertical-rl;
  text-orientation: upright;
  flex-direction: column;
  align-items: flex-start;
  column-gap: 0;
  position: relative; /* For absolute positioning of neutral tone */
}

.symbols-group {
  display: inline-flex;
  flex-direction: row; /* Horizontal in vertical-rl becomes vertical */
  align-items: center;
  justify-content: flex-start;
  position: relative;
  writing-mode: vertical-rl;
  text-orientation: upright;
}

.neutral-tone {
  font-size: 1em;
  line-height: 1;
  order: -1; /* Place before symbols */
  margin-bottom: 0.2em; /* Space between tone and symbols */
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
