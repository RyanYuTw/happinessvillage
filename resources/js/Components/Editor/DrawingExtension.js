import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import DrawingNode from './DrawingNode.vue'

export default Node.create({
    name: 'drawing',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            lines: {
                default: null,
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'drawing-component',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['drawing-component', mergeAttributes(HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(DrawingNode)
    },
})
