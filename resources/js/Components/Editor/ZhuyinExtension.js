import { Node, mergeAttributes, Extension } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import RubyNodeComponent from './RubyNode.vue'

export const RubyNode = Node.create({
    name: 'ruby',
    group: 'inline',
    inline: true,
    content: 'text*',
    selectable: false, // Don't select the whole node when clicking

    addAttributes() {
        return {
            rt: {
                default: '',
            },
            size: {
                default: 0.3,
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'ruby',
                getAttrs: element => {
                    const rt = element.querySelector('rt')
                    const size = element.getAttribute('data-size')
                    return {
                        rt: rt ? rt.textContent : '',
                        size: size ? parseFloat(size) : 0.3
                    }
                },
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['ruby', mergeAttributes(HTMLAttributes, { 'data-size': HTMLAttributes.size }), ['span', 0], ['rt', {}, HTMLAttributes.rt]]
    },

    addNodeView() {
        return VueNodeViewRenderer(RubyNodeComponent)
    },
})

export const ZhuyinExtension = Extension.create({
    name: 'zhuyin',

    addExtensions() {
        return [RubyNode]
    },

    addCommands() {
        return {
            insertZhuyin: (zhuyinData) => ({ state, dispatch }) => {
                const { selection, schema } = state
                const { from, to } = selection

                if (dispatch) {
                    const nodes = []
                    const createText = (t) => schema.text(t)
                    const createRuby = (t, rt, size) => schema.nodes.ruby.create({ rt, size }, createText(t))

                    zhuyinData.forEach(item => {
                        if (item.rt && item.rt !== item.char) {
                            nodes.push(createRuby(item.char, item.rt, item.size || 0.5))
                        } else {
                            nodes.push(createText(item.char))
                        }
                    })

                    const tr = state.tr.replaceWith(from, to, nodes)
                    dispatch(tr)
                }
                return true
            },
        }
    },
})

export default ZhuyinExtension
