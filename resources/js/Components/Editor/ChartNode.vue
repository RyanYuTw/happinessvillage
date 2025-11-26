<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { ref, computed, watch } from 'vue'
import { Bar, Line, Pie, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, ArcElement } from 'chart.js'
import { showError } from '@/utils/sweetalert'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, ArcElement)

const props = defineProps(nodeViewProps)

const isEditing = ref(false)
const chartType = ref(props.node.attrs.chartType || 'bar')
const chartDataJson = ref(JSON.stringify(props.node.attrs.chartData || {
  labels: ['January', 'February', 'March'],
  datasets: [{ data: [40, 20, 12], label: 'Data' }]
}, null, 2))

const chartComponent = computed(() => {
  switch (chartType.value) {
    case 'line': return Line
    case 'pie': return Pie
    case 'doughnut': return Doughnut
    default: return Bar
  }
})

const chartData = computed(() => {
  try {
    return JSON.parse(chartDataJson.value)
  } catch (e) {
    return { labels: [], datasets: [] }
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}

const save = () => {
  try {
    const data = JSON.parse(chartDataJson.value)
    props.updateAttributes({ chartData: data, chartType: chartType.value })
    isEditing.value = false
  } catch (e) {
    showError('JSON 格式錯誤')
  }
}
</script>

<template>
  <node-view-wrapper class="chart-node my-4 border rounded shadow-sm inline-block bg-white p-4 w-full max-w-lg">
    <div class="controls mb-2 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-600">類型:</span>
        <select v-model="chartType" class="text-xs border rounded px-2 py-1">
          <option value="bar">長條圖</option>
          <option value="line">折線圖</option>
          <option value="pie">圓餅圖</option>
          <option value="doughnut">甜甜圈圖</option>
        </select>
      </div>
      <button @click="isEditing = !isEditing" class="text-xs text-blue-500 border px-2 py-1 rounded">
        {{ isEditing ? '關閉編輯' : '編輯數據' }}
      </button>
    </div>

    <div v-if="isEditing" class="mb-4">
      <textarea
        v-model="chartDataJson"
        class="w-full h-32 border p-2 text-xs font-mono bg-gray-50"
      ></textarea>
      <button @click="save" class="mt-1 bg-blue-500 text-white px-3 py-1 rounded text-xs">更新圖表</button>
    </div>

    <div class="h-64">
      <component :is="chartComponent" :data="chartData" :options="chartOptions" />
    </div>
  </node-view-wrapper>
</template>
