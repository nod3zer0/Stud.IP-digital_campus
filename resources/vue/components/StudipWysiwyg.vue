<template>
    <ckeditor
        :editor="editor"
        :config="editorConfig"
        @ready="prefill"
        v-model="currentText"
        @input="onInput"
    />
</template>

<script>
import { ClassicEditor, BalloonEditor } from '../../assets/javascripts/chunks/wysiwyg.js';

export default {
    name: 'studip-wysiwyg',
    model: {
        prop: 'text',
        event: 'input',
    },
    props: {
        text: {
            type: String,
            required: true,
        },
        editorType: {
            type: String,
            validator: function (value) {
                return ['classic', 'balloon'].includes(value);
            },
            default: 'classic',
        },
    },
    data() {
        return {
            currentText: '',
            editorConfig: {},
        };
    },
    computed: {
        editor() {
            switch (this.editorType) {
                case 'classic':
                    return ClassicEditor;
                case 'balloon':
                    return BalloonEditor;
            }
            throw new Error('Unknown `editorType`');
        },
    },
    methods: {
        prefill(editor) {
            this.currentText = this.text;
        },
        onInput(value) {
            this.currentText = value;
            this.$emit('input', value);
        },
    },
    created() {
        STUDIP.loadChunk('mathjax');
    },
};
</script>
