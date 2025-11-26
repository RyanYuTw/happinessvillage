import { TableRow } from '@tiptap/extension-table-row'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'

export const TableRowResize = TableRow.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      height: {
        default: null,
        parseHTML: element => element.style.height || null,
        renderHTML: attributes => {
          if (!attributes.height) {
            return {}
          }
          return {
            style: `height: ${attributes.height}`
          }
        }
      }
    }
  },

  addProseMirrorPlugins() {
    return [
      ...(this.parent?.() || []),
      rowResizePlugin()
    ]
  }
})

function rowResizePlugin() {
  return new Plugin({
    key: new PluginKey('rowResize'),
    state: {
      init() {
        return {
          dragging: null,
          decorations: DecorationSet.empty
        }
      },
      apply(tr, value, oldState, newState) {
        const meta = tr.getMeta(this)
        if (meta) {
          return meta
        }
        
        if (oldState.doc !== newState.doc) {
          return {
            dragging: null,
            decorations: DecorationSet.empty
          }
        }
        
        return value
      }
    },
    props: {
      decorations(state) {
        return this.getState(state).decorations
      },
      
      handleDOMEvents: {
        mousemove(view, event) {
          const pluginState = this.getState(view.state)
          
          if (pluginState.dragging) {
            const { rowPos, startY, startHeight } = pluginState.dragging
            const deltaY = event.clientY - startY
            const newHeight = Math.max(30, startHeight + deltaY)
            
            const tr = view.state.tr
            const rowNode = view.state.doc.nodeAt(rowPos)
            
            if (rowNode) {
              tr.setNodeMarkup(rowPos, null, {
                ...rowNode.attrs,
                height: `${newHeight}px`
              })
              
              tr.setMeta(this, {
                dragging: pluginState.dragging,
                decorations: pluginState.decorations
              })
              
              view.dispatch(tr)
            }
            
            event.preventDefault()
            return true
          }
          
          // 檢查是否懸停在行邊界上
          const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })
          if (!pos) return false
          
          const $pos = view.state.doc.resolve(pos.pos)
          
          // 尋找表格行
          for (let d = $pos.depth; d > 0; d--) {
            const node = $pos.node(d)
            if (node.type.name === 'tableRow') {
              const rowPos = $pos.before(d)
              const rowDOM = view.nodeDOM(rowPos)
              
              if (rowDOM && rowDOM instanceof HTMLElement) {
                const rect = rowDOM.getBoundingClientRect()
                const distanceFromBottom = Math.abs(event.clientY - rect.bottom)
                
                if (distanceFromBottom < 5) {
                  view.dom.style.cursor = 'row-resize'
                  return false
                }
              }
            }
          }
          
          if (view.dom.style.cursor === 'row-resize') {
            view.dom.style.cursor = ''
          }
          
          return false
        },
        
        mousedown(view, event) {
          const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })
          if (!pos) return false
          
          const $pos = view.state.doc.resolve(pos.pos)
          
          // 尋找表格行
          for (let d = $pos.depth; d > 0; d--) {
            const node = $pos.node(d)
            if (node.type.name === 'tableRow') {
              const rowPos = $pos.before(d)
              const rowDOM = view.nodeDOM(rowPos)
              
              if (rowDOM && rowDOM instanceof HTMLElement) {
                const rect = rowDOM.getBoundingClientRect()
                const distanceFromBottom = Math.abs(event.clientY - rect.bottom)
                
                if (distanceFromBottom < 5) {
                  event.preventDefault()
                  
                  const tr = view.state.tr
                  tr.setMeta(this, {
                    dragging: {
                      rowPos,
                      startY: event.clientY,
                      startHeight: rect.height
                    },
                    decorations: DecorationSet.empty
                  })
                  
                  view.dispatch(tr)
                  return true
                }
              }
            }
          }
          
          return false
        },
        
        mouseup(view, event) {
          const pluginState = this.getState(view.state)
          
          if (pluginState.dragging) {
            const tr = view.state.tr
            tr.setMeta(this, {
              dragging: null,
              decorations: DecorationSet.empty
            })
            
            view.dispatch(tr)
            view.dom.style.cursor = ''
            
            event.preventDefault()
            return true
          }
          
          return false
        }
      }
    }
  })
}

export default TableRowResize
