import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import InputNode from './InputNode.vue'

export default Node.create({
  name: 'inputField',

  group: 'inline',

  inline: true,

  atom: true,

  selectable: true,

  addAttributes() {
    return {
      value: {
        default: '',
      },
      placeholder: {
        default: '輸入文字...',
      },
      x: {
        default: 100,
      },
      y: {
        default: 100,
      },
    }
  },

  parseHTML() {
    return [
      {
        tag: 'input-field',
        getAttrs: element => ({
          value: element.getAttribute('value') || '',
          x: parseInt(element.getAttribute('x')) || 100,
          y: parseInt(element.getAttribute('y')) || 100,
        }),
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['input-field', mergeAttributes(HTMLAttributes, {
      value: HTMLAttributes.value,
      x: HTMLAttributes.x,
      y: HTMLAttributes.y,
    })]
  },

  addNodeView() {
    return VueNodeViewRenderer(InputNode)
  },
})
