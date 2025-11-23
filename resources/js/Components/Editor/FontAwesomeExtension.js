import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import FontAwesomeNode from './FontAwesomeNode.vue'

export default Node.create({
  name: 'fontAwesomeIcon',

  group: 'inline',

  inline: true,

  atom: true,
  
  selectable: true,
  
  draggable: false,

  addAttributes() {
    return {
      icon: {
        default: 'fa-solid fa-star',
      },
      color: {
        default: '#000000',
      },
      size: {
        default: '1em',
      },
    }
  },

  parseHTML() {
    return [
      {
        tag: 'i[data-fa-icon]',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['i', mergeAttributes({
      'data-fa-icon': 'true',
      class: HTMLAttributes.icon,
      style: `color: ${HTMLAttributes.color}; font-size: ${HTMLAttributes.size};`
    })]
  },

  addNodeView() {
    return VueNodeViewRenderer(FontAwesomeNode)
  },

  addCommands() {
    return {
      insertFontAwesomeIcon: (attributes) => ({ commands }) => {
        return commands.insertContent({
          type: this.name,
          attrs: attributes,
        })
      },
    }
  },
})
