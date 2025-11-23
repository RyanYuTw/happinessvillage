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
        tag: 'span[data-input-field]',
        getAttrs: element => ({
          value: element.getAttribute('data-value') || '',
          x: parseInt(element.getAttribute('data-x')) || 100,
          y: parseInt(element.getAttribute('data-y')) || 100,
        }),
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['span', {
      'data-input-field': 'true',
      'data-value': HTMLAttributes.value,
      'data-x': HTMLAttributes.x,
      'data-y': HTMLAttributes.y,
      style: `position: absolute; left: ${HTMLAttributes.x}px; top: ${HTMLAttributes.y}px; z-index: 1000;`
    }, ['input', {
      type: 'text',
      value: HTMLAttributes.value,
      readonly: 'readonly',
      style: 'width: 30px; border: 1px solid #d1d5db; border-radius: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.875rem;'
    }]]
  },

  addNodeView() {
    return VueNodeViewRenderer(InputNode)
  },
})
