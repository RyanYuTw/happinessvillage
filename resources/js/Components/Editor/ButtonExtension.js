import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import ButtonNode from './ButtonNode.vue'

export default Node.create({
  name: 'button',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      text: {
        default: '按鈕',
      },
      url: {
        default: '#',
      },
      color: {
        default: '#fef08a', // yellow-200
      },
      textColor: {
        default: '#854d0e', // yellow-900
      },
      backgroundImage: {
        default: null,
      },
      drawingBackgroundImage: {
        default: null,
      },
      align: {
        default: 'center',
      },
    }
  },

  parseHTML() {
    return [
      {
        tag: 'div[data-type="button"]',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    const backgroundStyle = HTMLAttributes.backgroundImage
      ? `background-image: url('${HTMLAttributes.backgroundImage}'); background-size: cover; background-position: center;`
      : `background-color: ${HTMLAttributes.color || '#fef08a'};`
    
    // 如果有塗鴉背景圖片，使用特殊的 URL
    const href = HTMLAttributes.drawingBackgroundImage
      ? `/drawing-editor?backgroundImage=${encodeURIComponent(HTMLAttributes.drawingBackgroundImage)}&returnUrl=${encodeURIComponent(window.location.href)}`
      : (HTMLAttributes.url || '#')
    
    // 對齊樣式
    const align = HTMLAttributes.align || 'center'
    const alignStyle = align === 'left' ? 'text-align: left;' : 
                      align === 'right' ? 'text-align: right;' : 
                      'text-align: center;'
    
    return [
      'div',
      mergeAttributes(HTMLAttributes, { 
        'data-type': 'button',
        style: alignStyle
      }),
      [
        'a',
        {
          href,
          style: `display: inline-block; padding: 0.75rem 1.5rem; ${backgroundStyle} color: ${HTMLAttributes.textColor || '#854d0e'}; text-decoration: none; border-radius: 0.375rem; font-weight: 500; text-align: center;`,
          target: HTMLAttributes.drawingBackgroundImage ? '_blank' : '_blank',
          rel: 'noopener noreferrer',
        },
        HTMLAttributes.text || '按鈕',
      ],
    ]
  },

  addNodeView() {
    return VueNodeViewRenderer(ButtonNode)
  },

  addCommands() {
    return {
      setButton:
        (options) =>
        ({ commands }) => {
          return commands.insertContent({
            type: this.name,
            attrs: options,
          })
        },
    }
  },
})
