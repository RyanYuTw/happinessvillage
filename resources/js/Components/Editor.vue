<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import Blockquote from '@tiptap/extension-blockquote'
import Placeholder from '@tiptap/extension-placeholder'
import InputFieldExtension from './Editor/InputFieldExtension'
import DrawingExtension from './Editor/DrawingExtension'
import ImageAnnotationExtension from './Editor/ImageAnnotationExtension'
import ChartExtension from './Editor/ChartExtension'
import ZhuyinExtension from './Editor/ZhuyinExtension'
import ZhuyinSelector from './Editor/ZhuyinSelector.vue'
import Link from '@tiptap/extension-link'
import { TextStyle } from '@tiptap/extension-text-style'
import Youtube from '@tiptap/extension-youtube'
import FontSize from './Editor/FontSizeExtension'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableHeader } from '@tiptap/extension-table-header'
import { TableCell } from '@tiptap/extension-table-cell'
import { Color } from '@tiptap/extension-color'
import { Highlight } from '@tiptap/extension-highlight'
import { FontFamily } from '@tiptap/extension-font-family'
import TextBox from './Editor/TextBoxExtension'
import Image from '@tiptap/extension-image'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'
import Indent from './Editor/IndentExtension'
import Underline from '@tiptap/extension-underline'
import Superscript from '@tiptap/extension-superscript'
import Subscript from '@tiptap/extension-subscript'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { createLowlight } from 'lowlight'
import TextAlign from '@tiptap/extension-text-align'
import axios from 'axios'
import { 
  Bold, Italic, Strikethrough, Heading1, Heading2, List, 
  Type, PenTool, ImagePlus, BarChart3, Languages,
  Link2, Video, ALargeSmall, ListOrdered, Table as TableIcon,
  Indent as IndentIcon, Outdent as OutdentIcon, Underline as UnderlineIcon,
  Superscript as SuperscriptIcon, Subscript as SubscriptIcon, Code2,
  AlignLeft, AlignCenter, AlignRight, AlignJustify, FileCode, Zap,
  Quote, ListTodo
} from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

// Zhuyin Selection State
const showZhuyinSelector = ref(false)
const showPolyphoneModal = ref(false)
const selectedWord = ref('')
const polyphoneOptions = ref([])
const zhuyinProcessingData = ref([])
const currentPolyphoneIndex = ref(-1)
const polyphoneCandidates = ref([])
const zhuyinSize = ref(0.3) // Default Zhuyin size
const fontSize = ref('16px') // Default font size
const showLinkModal = ref(false)
const linkUrl = ref('')
const showTableModal = ref(false)
const textColor = ref('#000000')
const bgColor = ref('#ffff00')
const fontFamily = ref('Arial')
const borderColor = ref('#000000')

const boxFillColor = ref('#ffffff')
const showHtmlModal = ref(false)

const htmlContent = ref('')
const isRealTimeZhuyin = ref(false)
const isComposing = ref(false)
let typingTimer = null
let lastConvertedPosition = -1
let isConverting = ref(false)

// Create lowlight instance for code highlighting
const lowlight = createLowlight()

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      link: false,
      codeBlock: false,
      underline: false,
      blockquote: false,
    }),
    Blockquote,
    TaskList,
    TaskItem.configure({
      nested: true,
    }),
    // Custom keymap to override Ctrl+R
    {
      name: 'customKeymap',
      addKeyboardShortcuts() {
        return {
          'Mod-r': () => {
            // Prevent default Ctrl+R behavior (right align)
            return true
          },
        }
      },
    },
    Placeholder.configure({
      placeholder: 'Write something...',
    }),
    InputFieldExtension,
    DrawingExtension,
    ImageAnnotationExtension,
    ChartExtension,
    ZhuyinExtension,
    TextStyle,
    FontSize,
    Image.extend({
      addAttributes() {
        return {
          ...this.parent?.(),
          width: { default: null },
          height: { default: null },
          style: { default: null },
          'data-border-width': { default: null },
          'data-border-color': { default: null },
        }
      },
    }).configure({
      inline: false,
      allowBase64: true,
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-blue-600 underline',
      },
    }),
    Youtube.configure({
      width: 640,
      height: 480,
    }),
    Table.configure({
      resizable: true,
      HTMLAttributes: {
        class: 'border-collapse border border-gray-300',
      },
    }),
    TableRow,
    TableHeader.configure({
      HTMLAttributes: {
        class: 'border border-gray-300 bg-gray-100 font-bold p-2',
      },
    }),
    TableCell.configure({
      HTMLAttributes: {
        class: 'border border-gray-300 p-2',
      },
    }),
    Color,
    Highlight.configure({
      multicolor: true,
    }),
    FontFamily.configure({
      types: ['textStyle'],
    }),
    TextBox,
    Indent,
    Underline,
    Superscript,
    Subscript,
    CodeBlockLowlight.configure({
      lowlight,
      HTMLAttributes: {
        class: 'hljs bg-gray-800 text-white p-4 rounded my-2 overflow-x-auto',
      },
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
      alignments: ['left', 'center', 'right', 'justify'],
      defaultShortcuts: false, // Disable default shortcuts to prevent Ctrl+R conflict
    }),
  ],
  onUpdate: ({ editor, transaction }) => {
    emit('update:modelValue', editor.getHTML())
  },
  editorProps: {
    handleDOMEvents: {
      compositionstart: () => {
        isComposing.value = true
        return false
      },
      compositionend: () => {
        isComposing.value = false
        
        if (isRealTimeZhuyin.value) {
          setTimeout(() => {
            if (!editor.value) return
            
            const { from } = editor.value.state.selection
            let textStart = from
            let foundText = ''
            
            // 向前掃描找到連續的中文字元
            for (let pos = from - 1; pos >= Math.max(0, from - 20); pos--) {
              try {
                const resolvedPos = editor.value.state.doc.resolve(pos)
                const nodeAfter = resolvedPos.nodeAfter
                
                // 如果遇到 ruby 節點，停止
                if (nodeAfter && nodeAfter.type.name === 'ruby') {
                  break
                }
                
                const char = editor.value.state.doc.textBetween(pos, pos + 1)
                
                // 如果不是中文字元，停止
                if (!/[\u4e00-\u9fa5]/.test(char)) {
                  break
                }
                
                foundText = char + foundText
                textStart = pos
              } catch (e) {
                break
              }
            }
            
            // 只處理單一字元
            if (foundText && foundText.length === 1) {
              convertRealTime(textStart, from, foundText)
            }
          }, 200)
        }
        
        return false
      }
    },
    attributes: {
      class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl mx-auto focus:outline-none min-h-[300px] p-4 border rounded-md shadow-sm',
    }
  },
})

const addInputField = () => {
  editor.value.chain().focus().insertContent({ type: 'inputField' }).run()
}

const addDrawing = () => {
  editor.value.chain().focus().insertContent({ type: 'drawing' }).run()
}

const addImageAnnotation = () => {
  editor.value.chain().focus().insertContent({ type: 'imageAnnotation' }).run()
}

const addChart = () => {
  editor.value.chain().focus().insertContent({ type: 'chart' }).run()
}

const addZhuyin = async () => {
  const { from, to } = editor.value.state.selection
  const text = editor.value.state.doc.textBetween(from, to)
  
  if (!text) return

  try {
    const response = await axios.post('/api/zhuyin', { word: text })
    const data = response.data

    // Prepare data for processing
    zhuyinProcessingData.value = data.map(item => ({
      char: item.char,
      zhuyin: item.zhuyin,
      selected: item.zhuyin.length === 1 ? item.zhuyin[0] : null
    }))

    // Find polyphones
    const polyphones = []
    zhuyinProcessingData.value.forEach((item, index) => {
      if (!item.selected) {
        polyphones.push({ ...item, originalIndex: index })
      }
    })

    if (polyphones.length > 0) {
      polyphoneCandidates.value = polyphones
      currentPolyphoneIndex.value = 0
      showZhuyinSelector.value = true
    } else {
      insertResolvedZhuyin()
    }
  } catch (error) {
    console.error('Error fetching Zhuyin:', error)
    alert('注音轉換失敗，請稍後再試。')
  }
}

const convertRealTime = async (from, to, text) => {
  // 防止重複轉換同一位置
  if (isConverting.value || from === lastConvertedPosition) {
    return
  }
  
  isConverting.value = true
  
  try {
    const response = await axios.post('/api/zhuyin', { word: text })
    const data = response.data
    
    // 檢查是否有多音字（包括異體字）
    const hasMultiplePronunciations = data.some(item => item.zhuyin.length > 1)
    
    if (hasMultiplePronunciations) {
      // 有多音字或異體字，顯示選擇器
      zhuyinProcessingData.value = data.map((item, index) => ({
        char: item.char,
        zhuyin: item.zhuyin,
        selected: item.zhuyin.length === 1 ? item.zhuyin[0] : null,
        from: from,
        to: to
      }))
      
      // 只顯示需要選擇的字
      polyphoneCandidates.value = data
        .map((item, index) => ({
          char: item.char,
          zhuyin: item.zhuyin,
          originalIndex: index
        }))
        .filter(item => item.zhuyin.length > 1)
      
      if (polyphoneCandidates.value.length > 0) {
        currentPolyphoneIndex.value = 0
        showZhuyinSelector.value = true
      } else {
        // 所有字都只有一個讀音，直接插入
        insertResolvedZhuyin()
      }
    } else {
      // 沒有多音字，直接使用第一個選項
      const zhuyinData = data.map(item => ({
        char: item.char,
        rt: item.zhuyin[0],
        size: zhuyinSize.value
      }))
      
      const { state, view } = editor.value
      const { schema } = state
      
      const createText = (t) => schema.text(t)
      const createRuby = (t, rt, size) => schema.nodes.ruby.create({ rt, size }, createText(t))
      
      const nodes = []
      zhuyinData.forEach(item => {
        if (item.rt && item.rt !== item.char) {
          nodes.push(createRuby(item.char, item.rt, item.size || 0.5))
        } else {
          nodes.push(createText(item.char))
        }
      })
      
      const tr = state.tr.replaceWith(from, to, nodes)
      view.dispatch(tr)
    }
    
    // 記錄已轉換的位置
    lastConvertedPosition = from
    
    // 500ms 後重置，允許下一次轉換
    setTimeout(() => {
      lastConvertedPosition = -1
    }, 500)
    
  } catch (error) {
    console.error('Real-time Zhuyin error:', error)
  } finally {
    isConverting.value = false
  }
}
const setFontSize = (size) => {
  fontSize.value = size
  editor.value.chain().focus().setFontSize(size).run()
}

const toggleLink = () => {
  const previousUrl = editor.value.getAttributes('link').href
  if (previousUrl) {
    editor.value.chain().focus().unsetLink().run()
  } else {
    showLinkModal.value = true
  }
}

const insertLink = () => {
  if (linkUrl.value) {
    editor.value.chain().focus().setLink({ href: linkUrl.value }).run()
    linkUrl.value = ''
    showLinkModal.value = false
  }
}

const addVideo = () => {
  const url = prompt('請輸入 YouTube 影片網址:')
  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run()
  }
}

const insertTable = () => {
  editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
  showTableModal.value = false
}

const addTableRow = () => {
  editor.value.chain().focus().addRowAfter().run()
}

const deleteTableRow = () => {
  editor.value.chain().focus().deleteRow().run()
}

const addTableColumn = () => {
  editor.value.chain().focus().addColumnAfter().run()
}

const deleteTableColumn = () => {
  editor.value.chain().focus().deleteColumn().run()
}

const mergeCells = () => {
  editor.value.chain().focus().mergeCells().run()
}

const splitCell = () => {
  editor.value.chain().focus().splitCell().run()
}

const deleteTable = () => {
  editor.value.chain().focus().deleteTable().run()
}

const setTextColor = (color) => {
  textColor.value = color
  editor.value.chain().focus().setColor(color).run()
}

const setHighlight = (color) => {
  bgColor.value = color
  editor.value.chain().focus().setHighlight({ color }).run()
}

const setFont = (font) => {
  fontFamily.value = font
  editor.value.chain().focus().setFontFamily(font).run()
}

const setTextBox = () => {
  editor.value.chain().focus().setTextBox({ 
    borderColor: borderColor.value,
    fillColor: boxFillColor.value 
  }).run()
}

const removeTextBox = () => {
  editor.value.chain().focus().unsetTextBox().run()
}

const handleZhuyinResolve = (selectedZhuyin) => {
  const currentPolyphone = polyphoneCandidates.value[currentPolyphoneIndex.value]
  zhuyinProcessingData.value[currentPolyphone.originalIndex].selected = selectedZhuyin

  if (currentPolyphoneIndex.value < polyphoneCandidates.value.length - 1) {
    currentPolyphoneIndex.value++
  } else {
    showZhuyinSelector.value = false
    insertResolvedZhuyin()
  }
}

const insertResolvedZhuyin = () => {
  const firstItem = zhuyinProcessingData.value[0]
  
  // 如果有 from/to，表示是即時轉換，使用 replaceWith
  if (firstItem.from !== undefined && firstItem.to !== undefined) {
    const { state, view } = editor.value
    const { schema } = state
    
    const createText = (t) => schema.text(t)
    const createRuby = (t, rt, size) => schema.nodes.ruby.create({ rt, size }, createText(t))
    
    const nodes = []
    zhuyinProcessingData.value.forEach(item => {
      const rt = item.selected || item.zhuyin[0]
      if (rt && rt !== item.char) {
        nodes.push(createRuby(item.char, rt, zhuyinSize.value))
      } else {
        nodes.push(createText(item.char))
      }
    })
    
    const tr = state.tr.replaceWith(firstItem.from, firstItem.to, nodes)
    view.dispatch(tr)
  } else {
    // 一般轉換，使用 insertZhuyin
    const formatted = zhuyinProcessingData.value.map(item => ({
      char: item.char,
      rt: item.selected || item.char,
      size: zhuyinSize.value
    }))
    
    editor.value.chain().focus().insertZhuyin(formatted).run()
  }
  
  // Reset state
  zhuyinProcessingData.value = []
  polyphoneCandidates.value = []
  currentPolyphoneIndex.value = -1
}

const openHtmlModal = () => {
  htmlContent.value = editor.value.getHTML()
  showHtmlModal.value = true
}

const saveHtmlContent = () => {
  editor.value.commands.setContent(htmlContent.value)
  showHtmlModal.value = false
}

// 圖片縮放功能
let resizingImg = null
let startX = 0
let startWidth = 0
let startHeight = 0

const handleImageMouseDown = (e) => {
  if (e.target.tagName === 'IMG') {
    e.preventDefault()
    resizingImg = e.target
    startX = e.clientX
    startWidth = resizingImg.offsetWidth
    startHeight = resizingImg.offsetHeight
    
    document.addEventListener('mousemove', handleImageMouseMove)
    document.addEventListener('mouseup', handleImageMouseUp)
  }
}

const handleImageMouseMove = (e) => {
  if (!resizingImg) return
  
  const deltaX = e.clientX - startX
  const newWidth = Math.max(50, startWidth + deltaX)
  const aspectRatio = startHeight / startWidth
  const newHeight = newWidth * aspectRatio
  
  resizingImg.style.width = newWidth + 'px'
  resizingImg.style.height = newHeight + 'px'
}

const handleImageMouseUp = () => {
  resizingImg = null
  document.removeEventListener('mousemove', handleImageMouseMove)
  document.removeEventListener('mouseup', handleImageMouseUp)
}

watch(editor, (newEditor) => {
  if (newEditor) {
    const editorEl = newEditor.view.dom
    editorEl.addEventListener('mousedown', handleImageMouseDown)
  }
})

onUnmounted(() => {
  if (editor.value) {
    const editorEl = editor.value.view.dom
    editorEl.removeEventListener('mousedown', handleImageMouseDown)
  }
  document.removeEventListener('mousemove', handleImageMouseMove)
  document.removeEventListener('mouseup', handleImageMouseUp)
})

</script>

<template>
  <div class="editor-wrapper bg-white rounded-lg shadow overflow-hidden relative">
    <!-- Image Resize Handle (hidden, handled by CSS) -->
    <ZhuyinSelector
      :show="showZhuyinSelector"
      :candidates="polyphoneCandidates"
      :current-index="currentPolyphoneIndex"
      @resolve="handleZhuyinResolve"
      @cancel="showZhuyinSelector = false"
    />
    
    <div v-if="editor" class="toolbar flex gap-2 p-2 border-b bg-gray-50 flex-wrap items-center">
      <!-- 文字格式 -->
      <button
        @click="editor.chain().focus().toggleBold().run()"
        :class="{ 'is-active': editor.isActive('bold') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="粗體"
      >
        <Bold :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">粗體</span>
      </button>
      <button
        @click="editor.chain().focus().toggleItalic().run()"
        :class="{ 'is-active': editor.isActive('italic') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="斜體"
      >
        <Italic :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">斜體</span>
      </button>
      <button
        @click="editor.chain().focus().toggleStrike().run()"
        :class="{ 'is-active': editor.isActive('strike') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="刪除線"
      >
        <Strikethrough :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">刪除線</span>
      </button>
      <button
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="{ 'is-active': editor.isActive('underline') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="底線"
      >
        <UnderlineIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">底線</span>
      </button>
      <button
        @click="editor.chain().focus().toggleSuperscript().run()"
        :class="{ 'is-active': editor.isActive('superscript') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="上標"
      >
        <SuperscriptIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">上標</span>
      </button>
      <button
        @click="editor.chain().focus().toggleSubscript().run()"
        :class="{ 'is-active': editor.isActive('subscript') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="下標"
      >
        <SubscriptIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">下標</span>
      </button>
      <button
        @click="editor.chain().focus().toggleCodeBlock().run()"
        :class="{ 'is-active': editor.isActive('codeBlock') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="程式碼區塊"
      >
        <Code2 :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">程式碼區塊</span>
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 標題與列表 -->
      <button
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="標題 1"
      >
        <Heading1 :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">標題 1</span>
      </button>
      <button
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="標題 2"
      >
        <Heading2 :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">標題 2</span>
      </button>
      <button
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="{ 'is-active': editor.isActive('bulletList') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="項目列表"
      >
        <List :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">項目列表</span>
      </button>
      <button
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="{ 'is-active': editor.isActive('orderedList') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="編號列表"
      >
        <ListOrdered :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">編號列表</span>
      </button>
      <button
        @click="editor.chain().focus().toggleTaskList().run()"
        :class="{ 'is-active': editor.isActive('taskList') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="待辦清單"
      >
        <ListTodo :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">待辦清單</span>
      </button>
      <button
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="{ 'is-active': editor.isActive('blockquote') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="引用"
      >
        <Quote :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">引用</span>
      </button>
      <button
        @click="editor.chain().focus().indent().run()"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="增加縮排"
      >
        <IndentIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">增加縮排</span>
      </button>
      <button
        @click="editor.chain().focus().outdent().run()"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="減少縮排"
      >
        <OutdentIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">減少縮排</span>
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 對齊 -->
      <button
        @click="editor.chain().focus().indent().run()"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="增加縮排"
      >
        <IndentIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">減少縮排</span>
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 對齊 -->
      <button
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="左對齊"
      >
        <AlignLeft :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">左對齊</span>
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="置中對齊"
      >
        <AlignCenter :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">置中對齊</span>
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="右對齊"
      >
        <AlignRight :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">右對齊</span>
      </button>
      <button
        @click="editor.chain().focus().setTextAlign('justify').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'justify' }) }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="分散對齊"
      >
        <AlignJustify :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">分散對齊</span>
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 字型與顏色 -->
      <div class="flex items-center gap-2 ml-2 px-2 border-l border-gray-300">
        <span class="text-xs text-gray-600 font-medium">字型</span>
        <select 
          v-model="fontFamily" 
          @change="setFont(fontFamily)"
          class="text-xs border rounded px-2 py-1 bg-white cursor-pointer"
        >
          <option value="Arial">Arial</option>
          <option value="'Times New Roman'">Times New Roman</option>
          <option value="'Courier New'">Courier New</option>
          <option value="Georgia">Georgia</option>
          <option value="Verdana">Verdana</option>
          <option value="'Microsoft JhengHei', 微軟正黑體">微軟正黑體</option>
          <option value="'Noto Sans TC', sans-serif">Noto Sans TC</option>
          <option value="'Noto Serif TC', serif">Noto Serif TC</option>
        </select>
      </div>
      
      <!-- Text Color Control -->
      <div class="flex items-center gap-2 ml-2 px-2 border-l border-gray-300">
        <span class="text-xs text-gray-600 font-medium">文字色</span>
        <input 
          type="color" 
          v-model="textColor"
          @input="setTextColor(textColor)"
          class="w-8 h-6 border rounded cursor-pointer"
          title="選擇文字顏色"
        />
      </div>
      
      <!-- Background/Highlight Color Control -->
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-600 font-medium">背景色</span>
        <input 
          type="color" 
          v-model="bgColor"
          @input="setHighlight(bgColor)"
          class="w-8 h-6 border rounded cursor-pointer"
          title="選擇背景顏色"
        />
        <button 
          @click="editor.chain().focus().unsetHighlight().run()"
          class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
          title="清除背景色"
        >
          清除
        </button>
      </div>
      
      <!-- Text Box Controls -->
      <div class="flex items-center gap-2 ml-2 px-2 border-l border-gray-300">
        <span class="text-xs text-gray-600 font-medium">文字方框</span>
        <input 
          type="color" 
          v-model="borderColor"
          class="w-8 h-6 border rounded cursor-pointer"
          title="選擇框線顏色"
        />
        <span class="text-xs text-gray-600">填滿</span>
        <input 
          type="color" 
          v-model="boxFillColor"
          class="w-8 h-6 border rounded cursor-pointer"
          title="選擇填滿顏色"
        />
        <button 
          @click="setTextBox"
          class="text-xs px-2 py-1 border rounded hover:bg-blue-100 bg-blue-50"
          title="套用方框"
        >
          套用
        </button>
        <button 
          @click="removeTextBox"
          class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
          title="移除方框"
        >
          移除
        </button>
      </div>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 插入元素 -->
      <button
        @click="addInputField"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入輸入框"
      >
        <Type :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入輸入框</span>
      </button>
      <button
        @click="addDrawing"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入塗鴉"
      >
        <PenTool :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入塗鴉</span>
      </button>
      <button
        @click="addImageAnnotation"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入圖片標註"
      >
        <ImagePlus :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入圖片標註</span>
      </button>
      <button
        @click="addChart"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入圖表"
      >
        <BarChart3 :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入圖表</span>
      </button>
      <button
        @click="toggleLink"
        :class="{ 'is-active': editor.isActive('link') }"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入連結"
      >
        <Link2 :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入連結</span>
      </button>
      <button
        @click="addVideo"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入影片"
      >
        <Video :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入影片</span>
      </button>
      <button
        @click="showTableModal = true"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="插入表格"
      >
        <TableIcon :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">插入表格</span>
      </button>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 注音功能 -->
      <button
        @click="isRealTimeZhuyin = !isRealTimeZhuyin"
        :class="[
          'group relative p-2 rounded hover:bg-gray-200',
          isRealTimeZhuyin ? 'bg-blue-100 text-blue-600' : 'text-gray-600'
        ]"
        title="即時注音開關"
      >
        <Zap :size="20" :fill="isRealTimeZhuyin ? 'currentColor' : 'none'" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">即時注音</span>
      </button>

      <button
        @click="addZhuyin"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="轉換為注音"
      >
        <Languages :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">轉換為注音</span>
      </button>
      
      <!-- 注音設定 -->
      <div class="flex items-center gap-2 ml-2 px-2 border-l border-gray-300">
        <span class="text-xs text-gray-600 font-medium">注音大小</span>
        <input
          type="range"
          min="0.1"
          max="1.0"
          step="0.05"
          v-model.number="zhuyinSize"
          class="w-20 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-500"
        />
        <span class="text-xs text-gray-500 w-10 text-center">{{ zhuyinSize.toFixed(2) }}</span>
      </div>
      
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- 其他 -->
      <button
        @click="openHtmlModal"
        class="group relative p-2 rounded hover:bg-gray-200"
        title="編輯 HTML"
      >
        <FileCode :size="20" />
        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">編輯 HTML</span>
      </button>
      
      <!-- 字體大小 -->
      <div class="flex items-center gap-2 ml-2 px-2 border-l border-gray-300">
        <span class="text-xs text-gray-600 font-medium">字體</span>
        <select 
          v-model="fontSize" 
          @change="setFontSize(fontSize)"
          class="text-xs border rounded px-2 py-1 bg-white cursor-pointer"
        >
          <option value="12px">12px</option>
          <option value="14px">14px</option>
          <option value="16px" selected>16px</option>
          <option value="18px">18px</option>
          <option value="20px">20px</option>
          <option value="24px">24px</option>
          <option value="28px">28px</option>
          <option value="32px">32px</option>
        </select>
      </div>
      
      <!-- 表格控制 (在表格內時顯示) -->
      <div v-if="editor && editor.isActive('table')" class="flex items-center gap-1 ml-2 px-2 border-l border-gray-300">
        <button @click="addTableRow" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="新增列">
          +列
        </button>
        <button @click="deleteTableRow" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="刪除列">
          -列
        </button>
        <button @click="addTableColumn" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="新增欄">
          +欄
        </button>
        <button @click="deleteTableColumn" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="刪除欄">
          -欄
        </button>
        <button @click="mergeCells" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="合併儲存格">
          合併
        </button>
        <button @click="splitCell" class="text-xs px-2 py-1 border rounded hover:bg-gray-100" title="分割儲存格">
          分割
        </button>
        <button @click="deleteTable" class="text-xs px-2 py-1 border rounded hover:bg-red-100 text-red-600" title="刪除表格">
          刪除表格
        </button>
      </div>
    </div>
    
    <!-- Table Modal -->
    <div v-if="showTableModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-96">
        <h3 class="text-lg font-bold mb-4">插入表格</h3>
        <p class="text-sm text-gray-600 mb-4">將插入一個 3×3 的表格（含標題列）</p>
        <div class="flex justify-end gap-2">
          <button @click="showTableModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            取消
          </button>
          <button @click="insertTable" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            插入
          </button>
        </div>
      </div>
    </div>
    
    <!-- Link Modal -->
    <div v-if="showLinkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-96">
        <h3 class="text-lg font-bold mb-4">插入連結</h3>
        <input
          v-model="linkUrl"
          type="url"
          placeholder="https://example.com"
          class="w-full px-3 py-2 border rounded mb-4"
          @keyup.enter="insertLink"
        />
        <div class="flex justify-end gap-2">
          <button @click="showLinkModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            取消
          </button>
          <button @click="insertLink" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            確定
          </button>
        </div>
      </div>
    </div>

    <!-- HTML Editor Modal -->
    <div v-if="showHtmlModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-[800px] max-h-[90vh] flex flex-col">
        <h3 class="text-lg font-bold mb-4">編輯 HTML 原始碼</h3>
        <textarea
          v-model="htmlContent"
          class="w-full h-96 p-4 border rounded font-mono text-sm mb-4 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          spellcheck="false"
        ></textarea>
        <div class="flex justify-end gap-2">
          <button @click="showHtmlModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800 border rounded hover:bg-gray-50">
            取消
          </button>
          <button @click="saveHtmlContent" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            儲存
          </button>
        </div>
      </div>
    </div>
    <editor-content :editor="editor" />
  </div>
</template>

<style lang="css">
/* Basic Editor Styles */
.ProseMirror {
  outline: none;
  min-height: 300px;
  padding: 1rem;
}

.ProseMirror p {
  margin-bottom: 0.5em;
}

/* Zhuyin (Bopomofo) Styling */
ruby {
  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  margin: 0 0.1em;
  line-height: 1.2;
}

ruby > rt {
  display: block;
  font-size: 0.4em; /* Adjust size as needed */
  writing-mode: vertical-rl; /* Vertical text for Zhuyin */
  text-orientation: upright; /* Keep symbols upright */
  margin-left: 0.2em; /* Space between char and zhuyin */
  line-height: 1;
  letter-spacing: -0.1em; /* Tighten vertical spacing if needed */
  font-family: "Bopomofo", sans-serif; /* Optional: prefer fonts with good Zhuyin support */
  transform: translateY(-0.5em); /* Move zhuyin higher */
}

/* Active Button State */
.is-active {
  background-color: #e5e7eb; /* bg-gray-200 */
  font-weight: bold;
}

/* Resizable Images */
.ProseMirror img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 1rem 0;
  cursor: nwse-resize;
}

.ProseMirror img:hover {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.ProseMirror img.ProseMirror-selectednode {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Blockquote Styling */
.ProseMirror blockquote {
  border-left: 4px solid #d1d5db;
  padding-left: 1rem;
  margin: 1rem 0;
  color: #6b7280;
  font-style: italic;
}

/* Task List Styling */
.ProseMirror ul[data-type="taskList"] {
  list-style: none;
  padding: 0;
}

.ProseMirror ul[data-type="taskList"] li {
  display: flex;
  align-items: flex-start;
  margin: 0.5rem 0;
}

.ProseMirror ul[data-type="taskList"] li > label {
  flex: 0 0 auto;
  margin-right: 0.5rem;
  user-select: none;
}

.ProseMirror ul[data-type="taskList"] li > div {
  flex: 1 1 auto;
}

.ProseMirror ul[data-type="taskList"] input[type="checkbox"] {
  cursor: pointer;
  width: 1rem;
  height: 1rem;
}
</style>

<style lang="css" scoped>
.is-active {
  background-color: #e5e7eb; /* bg-gray-200 */
  font-weight: bold;
}
</style>
