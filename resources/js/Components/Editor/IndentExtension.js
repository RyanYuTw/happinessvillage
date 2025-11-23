import { Extension } from '@tiptap/core'
import { TextSelection, AllSelection } from '@tiptap/pm/state'

export const Indent = Extension.create({
    name: 'indent',

    addOptions() {
        return {
            types: ['paragraph', 'heading', 'listItem'],
            minLevel: 0,
            maxLevel: 8,
        }
    },

    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    indent: {
                        default: 0,
                        parseHTML: element => {
                            const level = element.getAttribute('data-indent')
                            return level ? parseInt(level, 10) : 0
                        },
                        renderHTML: attributes => {
                            if (!attributes.indent || attributes.indent === 0) {
                                return {}
                            }

                            return {
                                'data-indent': attributes.indent,
                                style: `padding-left: ${attributes.indent * 2}em;`,
                            }
                        },
                    },
                },
            },
        ]
    },

    addCommands() {
        return {
            indent: () => ({ tr, state, dispatch }) => {
                const { selection } = state
                tr = tr.setSelection(selection)

                tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                    if (this.options.types.includes(node.type.name)) {
                        const currentIndent = node.attrs.indent || 0
                        if (currentIndent < this.options.maxLevel) {
                            tr.setNodeMarkup(pos, undefined, {
                                ...node.attrs,
                                indent: currentIndent + 1,
                            })
                        }
                    }
                })

                if (dispatch) {
                    dispatch(tr)
                }

                return true
            },

            outdent: () => ({ tr, state, dispatch }) => {
                const { selection } = state
                tr = tr.setSelection(selection)

                tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                    if (this.options.types.includes(node.type.name)) {
                        const currentIndent = node.attrs.indent || 0
                        if (currentIndent > this.options.minLevel) {
                            tr.setNodeMarkup(pos, undefined, {
                                ...node.attrs,
                                indent: currentIndent - 1,
                            })
                        }
                    }
                })

                if (dispatch) {
                    dispatch(tr)
                }

                return true
            },
        }
    },

    addKeyboardShortcuts() {
        return {
            Tab: () => this.editor.commands.indent(),
            'Shift-Tab': () => this.editor.commands.outdent(),
        }
    },
})

export default Indent
