import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import FontAwesomeNode from './FontAwesomeNode.vue'

export default Node.create({
  name: 'fontAwesomeIcon',

  group: 'inline',

  inline: true,
  
  atom: true,
  
  selectable: true,

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
        tag: 'span[data-fa-icon]',
        getAttrs: element => {
          return {
            icon: element.getAttribute('data-icon') || 'fa-solid fa-star',
            color: element.getAttribute('data-color') || '#000000',
            size: element.getAttribute('data-size') || '1em',
          }
        },
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['span', { 'data-fa-icon': 'true', 'data-icon': HTMLAttributes.icon, 'data-color': HTMLAttributes.color, 'data-size': HTMLAttributes.size, class: 'fa-icon-wrapper' }, 
      ['i', {
        class: HTMLAttributes.icon,
        style: `color: ${HTMLAttributes.color}; font-size: ${HTMLAttributes.size};`
      }]
    ]
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
