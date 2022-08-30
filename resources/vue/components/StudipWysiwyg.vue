<template>
    <ckeditor
        v-if="enabled"
        :editor="editor"
        :config="editorConfig"
        @ready="prefill"
        v-model="currentText"
        @input="onInput"
    ></ckeditor>
    <textarea
        v-else
        :value="text"
        @input="$emit('input', $event.target.value)"
        ref="textarea"
        class="studip-wysiwyg wysiwyg"
    ></textarea>
</template>

<script>
import ClassicEditor from '../../assets/javascripts/chunks/wysiwyg.js';
import Toolbar from '../../assets/javascripts/lib/toolbar.js';

export default {
    name: 'studip-wysiwyg',
    model: {
        prop: 'text',
        event: 'input',
    },
    props: {
        text: String,
    },
    data() {
        return {
            currentText: '',
            editor: ClassicEditor,
            editorConfig: {},
        };
    },
    computed: {
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
