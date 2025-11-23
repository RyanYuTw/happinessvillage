import { Node, mergeAttributes } from '@tiptap/core'

export const TextBox = Node.create({
    name: 'textBox',
    group: 'inline',
    inline: true,
    content: 'inline*',

    addOptions() {
        return {
            HTMLAttributes: {},
        }
    },

    addAttributes() {
        return {
            borderColor: {
                default: '#000000',
                parseHTML: element => element.getAttribute('data-border-color') || '#000000',
            },
            fillColor: {
                default: '#ffffff',
                parseHTML: element => element.getAttribute('data-fill-color') || '#ffffff',
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-text-box]',
                priority: 60,
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const borderColor = HTMLAttributes.borderColor || '#000000'
        const fillColor = HTMLAttributes.fillColor || '#ffffff'
        const style = `border: 2px solid ${borderColor}; background-color: ${fillColor}; padding: 2px 4px; border-radius: 3px; display: inline-block;`

        return [
            'span',
            mergeAttributes(this.options.HTMLAttributes, {
                'data-text-box': '',
                'data-border-color': borderColor,
                'data-fill-color': fillColor,
                style: style,
            }),
            0,
        ]
    },

    addCommands() {
        return {
            setTextBox: (attributes) => ({ state, dispatch, chain }) => {
                const { from, to } = state.selection
                
                if (from === to) return false
                
                const selectedContent = state.doc.slice(from, to).content
                const textBoxNode = state.schema.nodes.textBox.create(attributes, selectedContent)
                
                if (dispatch) {
                    const tr = state.tr.replaceRangeWith(from, to, textBoxNode)
                    dispatch(tr)
                }
                return true
            },
            unsetTextBox: () => ({ state, dispatch }) => {
                const { from } = state.selection
                const $pos = state.doc.resolve(from)
                const node = $pos.parent
                
                if (node.type.name === 'textBox') {
                    const nodePos = from - $pos.parentOffset
                    if (dispatch) {
                        const tr = state.tr.replaceWith(nodePos, nodePos + node.nodeSize, node.content)
                        dispatch(tr)
                    }
                    return true
                }
                return false
            },
        }
    },
})

export default TextBox
