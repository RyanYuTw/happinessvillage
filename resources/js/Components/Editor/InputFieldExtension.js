import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import InputNode from './InputNode.vue'

export default Node.create({
  name: 'inputField',

  group: 'inline',

  inline: true,

  atom: true,

  addAttributes() {
    return {
      value: {
        default: '',
      },
      placeholder: {
        default: '輸入文字...',
      },
    }
  },

  parseHTML() {
    return [
      {
        tag: 'input-field',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['input-field', mergeAttributes(HTMLAttributes)]
  },

  addNodeView() {
    return VueNodeViewRenderer(InputNode)
  },
})
