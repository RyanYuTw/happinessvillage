<template>
    <AdminLayout>
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-base sm:text-lg font-medium text-gray-900">手冊預覽</h2>
                <Link :href="route('admin.handbooks')" class="text-sm text-gray-600 hover:text-gray-900">
                    返回列表
                </Link>
            </div>
            
            <div class="p-4 sm:p-6">
                <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">年度：</span>
                        <span>{{ handbook.year }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">年級：</span>
                        <span>{{ handbook.grade }}年級</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">學期：</span>
                        <span>{{ handbook.semester }}學期</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">課別：</span>
                        <span>{{ handbook.lesson }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">狀態：</span>
                        <span :class="handbook.status ? 'text-green-600' : 'text-gray-600'">
                            {{ handbook.status ? '已發布' : '未發布' }}
                        </span>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">內容</h3>
                    <div ref="contentContainer" class="handbook-content prose max-w-none" v-html="decodedContent" @vue:updated="applyTableWidths"></div>
                </div>
                
                <div class="mt-6 flex gap-2">
                    <Link :href="route('admin.handbooks.edit', handbook.id)" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded text-sm font-medium">
                        編輯
                    </Link>
                    <Link :href="route('admin.handbooks')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium">
                        返回
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed, onMounted, nextTick, ref } from 'vue'

const props = defineProps({
    handbook: Object
})

const contentContainer = ref(null)

const decodedContent = computed(() => {
    if (!props.handbook.content) return '無內容'
    const txt = document.createElement('textarea')
    txt.innerHTML = props.handbook.content
    return txt.value
})

const applyTableWidths = () => {
    setTimeout(() => {
        const tables = document.querySelectorAll('.handbook-content table')
        tables.forEach(table => {
            const cells = table.querySelectorAll('td[colwidth], th[colwidth]')
            cells.forEach(cell => {
                const width = cell.getAttribute('colwidth')
                if (width) {
                    cell.style.width = width + 'px'
                }
            })
            
            // 計算表格總寬度
            const firstRow = table.querySelector('tr')
            if (firstRow) {
                let totalWidth = 0
                const firstRowCells = firstRow.querySelectorAll('td[colwidth], th[colwidth]')
                firstRowCells.forEach(cell => {
                    totalWidth += parseInt(cell.getAttribute('colwidth') || '0')
                })
                if (totalWidth > 0) {
                    table.style.width = totalWidth + 'px'
                }
            }
        })
    }, 100)
}

onMounted(() => {
    applyTableWidths()
})
</script>

<style scoped>
/* Table Alignment */
.handbook-content table {
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

.handbook-content table colgroup col {
  max-width: 100%;
}

.handbook-content table[data-align="center"] {
  margin-left: auto;
  margin-right: auto;
}

.handbook-content table[data-align="right"] {
  margin-left: auto;
  margin-right: 0;
}

.handbook-content table[data-align="left"] {
  margin-left: 0;
  margin-right: auto;
}

/* Table Border Width */
.handbook-content table {
  border: 1px solid #d1d5db !important;
}

.handbook-content table td,
.handbook-content table th {
  border: 1px solid #d1d5db !important;
  padding: 0.5rem;
}

.handbook-content table[data-border="0"] {
  border-width: 0px !important;
  border-style: none !important;
}

.handbook-content table[data-border="0"] td,
.handbook-content table[data-border="0"] th { 
  border-width: 0px !important;
  border-style: none !important;
}

.handbook-content table[data-border="1"] {
  border-width: 1px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="1"] td,
.handbook-content table[data-border="1"] th { 
  border-width: 1px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="2"] {
  border-width: 2px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="2"] td,
.handbook-content table[data-border="2"] th { 
  border-width: 2px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="3"] {
  border-width: 3px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="3"] td,
.handbook-content table[data-border="3"] th { 
  border-width: 3px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="4"] {
  border-width: 4px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="4"] td,
.handbook-content table[data-border="4"] th { 
  border-width: 4px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="5"] {
  border-width: 5px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

.handbook-content table[data-border="5"] td,
.handbook-content table[data-border="5"] th { 
  border-width: 5px !important;
  border-style: solid !important;
  border-color: #d1d5db !important;
}

/* Ruby (注音) styling */
.handbook-content ruby {
  display: inline-flex !important;
  flex-direction: row !important;
  align-items: center !important;
  vertical-align: baseline !important;
  margin: 0 0.15em !important;
  line-height: 1.8 !important;
}

.handbook-content ruby > rt,
.handbook-content ruby rt {
  display: inline-block !important;
  font-size: 0.5em !important;
  writing-mode: vertical-rl !important;
  text-orientation: upright !important;
  margin-left: 0.15em !important;
  line-height: 1 !important;
  font-family: Bopomofo, Microsoft JhengHei, sans-serif !important;
  color: inherit !important;
  white-space: nowrap !important;
}
</style>
