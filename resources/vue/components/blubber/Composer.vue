<template>
    <div class="writer" :style="composerStyle">
        <studip-icon shape="blubber" :size="30" role="info"></studip-icon>
        <textarea
            :placeholder="placeholder || $gettext('Schreib was, frag was. Enter zum Abschicken.')"
            v-model="localText"
            @change="saveCommentToSession"
            @focus="resizeTextarea"
            @keydown.enter.exact="submit"
            @keyup.up.exact="editPreviousComment"
            @keyup="saveCommentToSession"
            ref="textarea"
        ></textarea>
        <a class="send" @click="submit" :title="$gettext('Abschicken')">
            <studip-icon shape="arr_2up" :size="30"></studip-icon>
        </a>
        <label class="upload" :title="$gettext('Datei hochladen')" tabindex="0" ref="label" @keydown="simulateClick">
            <input type="file" multiple style="display: none" @change="onFilesPick" />
            <studip-icon shape="upload" :size="30"></studip-icon>
        </label>
    </div>
</template>
<script>
export default {
    name: 'blubber-composer',
    model: {
        prop: 'text',
        event: 'change',
    },
    props: {
        placeholder: {
            type: String,
            default: '',
        },
        progress: {
            type: Number,
            default: 0,
        },
        text: {
            type: String,
            default: '',
        },
    },
    data: () => ({
        localText: '',
    }),
    computed: {
        composerStyle() {
            return {
                'background-size': `${this.progress}%`,
            };
        },
    },
    methods: {
        editPreviousComment() {
            this.$emit('edit-previous');
        },
        focusTextarea() {
            this.$refs.textarea.focus();
            this.$refs.textarea.setSelectionRange(0, 0);
        },
        onFilesPick(event) {
            let files =
                event.dataTransfer !== undefined
                    ? event.dataTransfer.files // file drop
                    : event.target.files; // upload button
            this.$emit('pick-files', files);
        },
        reset() {
            this.localText = '';
        },
        resizeTextarea() {
            const { textarea } = this.$refs;

            const style = window.getComputedStyle(textarea, null);
            let heightOffset;

            if (style.boxSizing === 'content-box') {
                heightOffset = -(parseFloat(style.paddingTop) + parseFloat(style.paddingBottom));
            } else {
                heightOffset = parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
            }
            if (isNaN(heightOffset)) {
                heightOffset = 0;
            }

            textarea.style.height = '';
            textarea.style.height = (textarea.scrollHeight + heightOffset) + 'px';
        },
        simulateClick(event) {
            if (event.code === 'Enter') {
                this.$refs.label.click();
            }
        },
        submit(event) {
            const text = this.localText;
            this.reset();

            if (text.trim().length === 0) {
                return false;
            }

            event.preventDefault();
            this.$emit('add-posting', text);
        },
        saveCommentToSession() {
            this.resizeTextarea();
            this.$emit('change', this.localText);
        },
    },
    mounted() {
        this.localText = this.text;
        this.$nextTick(() => {
            this.resizeTextarea();
        });
    },
    watch: {
        text(newText, oldText) {
            if (this.localText !== newText) {
                this.localText = newText;
                this.focusTextarea();
            }
        },
    },
};
</script>
