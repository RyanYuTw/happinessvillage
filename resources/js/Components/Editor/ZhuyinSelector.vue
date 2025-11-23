<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  candidates: {
    type: Array,
    default: () => []
  },
  currentIndex: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['resolve', 'cancel'])

const handleKeydown = (e) => {
  if (!props.show) return

  // Number keys 1-9
  if (e.key >= '1' && e.key <= '9') {
    const index = parseInt(e.key) - 1
    const currentCandidate = props.candidates[props.currentIndex]
    
    if (currentCandidate && currentCandidate.zhuyin[index]) {
      e.preventDefault()
      select(currentCandidate.zhuyin[index])
    }
  } else if (e.key === 'Escape') {
    emit('cancel')
  }
}

const select = (zhuyin) => {
  emit('resolve', zhuyin)
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-full">
      <h3 class="text-lg font-bold mb-4 text-gray-800">
        請選擇注音 ({{ currentIndex + 1 }}/{{ candidates.length }})
      </h3>
      
      <div v-if="candidates[currentIndex]" class="mb-4">
        <div class="text-4xl text-center mb-4 font-serif font-bold text-gray-900">
          {{ candidates[currentIndex].char }}
        </div>
        
        <div v-if="candidates[currentIndex].zhuyin.length > 1" class="text-xs text-gray-500 text-center mb-3">
          此字有 {{ candidates[currentIndex].zhuyin.length }} 個讀音（可能包含異體字讀音）
        </div>
        
        <div class="grid grid-cols-1 gap-2">
          <button
            v-for="(zhuyin, idx) in candidates[currentIndex].zhuyin"
            :key="idx"
            @click="select(zhuyin)"
            class="flex items-center p-3 rounded border hover:bg-blue-50 hover:border-blue-300 transition-colors text-left group"
          >
            <span class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded-full text-xs font-bold mr-3 group-hover:bg-blue-500 group-hover:text-white">
              {{ idx + 1 }}
            </span>
            <span class="text-lg font-medium text-gray-700 font-bopomofo">
              {{ zhuyin }}
            </span>
          </button>
        </div>
      </div>

      <div class="flex justify-end mt-4">
        <button @click="$emit('cancel')" class="px-4 py-2 text-gray-500 hover:text-gray-700">
          取消 (Esc)
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.font-bopomofo {
  font-family: "Bopomofo", sans-serif;
}
</style>
