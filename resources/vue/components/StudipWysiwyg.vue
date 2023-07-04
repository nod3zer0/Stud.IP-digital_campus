<template>
    <ckeditor
        v-if="enabled"
        :editor="editor"
        :config="editorConfig"
        @ready="prefill"
        v-model="currentText"
        @input="onInput"
    />
    <textarea
        v-else
        :value="text"
        @input="$emit('input', $event.target.value)"
        ref="textarea"
        class="studip-wysiwyg wysiwyg"
    ></textarea>
</template>

<script>
import { ClassicEditor, BalloonEditor } from '../../assets/javascripts/chunks/wysiwyg.js';
import Toolbar from '../../assets/javascripts/lib/toolbar.js';

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
        enabled() {
            return STUDIP.editor_enabled;
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
    mounted() {
        if (!this.enabled) {
            Toolbar.initialize(this.$refs.textarea);
        }
    },
};
</script>
