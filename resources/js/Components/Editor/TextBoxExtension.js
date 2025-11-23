import { Mark, mergeAttributes } from '@tiptap/core'

export const TextBox = Mark.create({
    name: 'textBox',

    addOptions() {
        return {
            HTMLAttributes: {},
        }
    },

    addAttributes() {
        return {
            borderColor: {
                default: '#000000',
                parseHTML: element => element.style.borderColor || '#000000',
                renderHTML: attributes => {
                    if (!attributes.borderColor) {
                        return {}
                    }
                    return {
                        style: `border: 2px solid ${attributes.borderColor}; padding: 2px 4px; border-radius: 3px; display: inline-block;`,
                    }
                },
            },
            fillColor: {
                default: null,
                parseHTML: element => element.style.backgroundColor || null,
                renderHTML: attributes => {
                    if (!attributes.fillColor) {
                        return {}
                    }
                    return {
                        style: `background-color: ${attributes.fillColor};`,
                    }
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-text-box]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const borderStyle = HTMLAttributes.borderColor
            ? `border: 2px solid ${HTMLAttributes.borderColor}; padding: 2px 4px; border-radius: 3px; display: inline-block;`
            : ''
        const fillStyle = HTMLAttributes.fillColor
            ? `background-color: ${HTMLAttributes.fillColor};`
            : ''

        return [
            'span',
            mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, {
                'data-text-box': '',
                style: `${borderStyle} ${fillStyle}`.trim(),
            }),
            0,
        ]
    },

    addCommands() {
        return {
            setTextBox: (attributes) => ({ commands }) => {
                return commands.setMark(this.name, attributes)
            },
            unsetTextBox: () => ({ commands }) => {
                return commands.unsetMark(this.name)
            },
        }
    },
})

export default TextBox
