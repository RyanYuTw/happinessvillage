import { Node, mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import ImageAnnotationNode from './ImageAnnotationNode.vue'

export default Node.create({
    name: 'imageAnnotation',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            imageUrl: {
                default: null,
            },
            drawing: {
                default: null,
            },
            inputs: {
                default: [],
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'image-annotation',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['image-annotation', mergeAttributes(HTMLAttributes)]
    },

    addNodeView() {
        return VueNodeViewRenderer(ImageAnnotationNode)
    },
})
