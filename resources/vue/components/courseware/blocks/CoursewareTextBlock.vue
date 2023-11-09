<template>
    <div class="cw-block cw-block-text">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            ref="defaultBlock"
            @showEdit="initCurrent"
            @storeEdit="storeText"
            @closeEdit="initCurrent"
        >
            <template #content>
                <section class="formatted-content ck-content" v-html="currentText" ref="content"></section>
            </template>
            <template v-if="canEdit" #edit>
                <ckeditor :editor="editor" v-model="currentText" :config="editorConfig" @ready="onReady"></ckeditor>
            </template>
            <template #info>{{ $gettext('Informationen zum Text-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import FindAndReplace from '@ckeditor/ckeditor5-find-and-replace/src/findandreplace';
import { ClassicEditor, BalloonEditor } from '@/assets/javascripts/chunks/wysiwyg';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-text-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
            editor: ClassicEditor,
            editorConfig: {
                // The configuration of the editor.
                extraPlugins: [FindAndReplace],
            },
        };
    },
    computed: {
        text() {
            return this.block?.attributes?.payload?.text;
        },
        ckeToolbarTop() {
            const topBar = document.getElementById('top-bar');
            const responsiveContentbar = document.getElementById('responsive-contentbar');
            let top = topBar.clientHeight + topBar.clientTop;
            if (responsiveContentbar) {
                top += responsiveContentbar?.clientHeight + responsiveContentbar?.clientTop;
            } else {
                top += 85;
            }

            return top;
        },
    },
    mounted() {
        this.initCurrent();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrent() {
            this.currentText = this.text;
            this.loadMathjax();
        },
        onReady(editor) {
            editor.ui.viewportOffset = { top: this.ckeToolbarTop };
            editor.ui.update();
        },
        async storeText() {
            let attributes = this.block.attributes;
            attributes.payload.text = this.currentText;
            await this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
            this.$refs.defaultBlock.displayFeature(false);
            this.loadMathjax();
        },
        loadMathjax() {
            let mathjaxP;
            let view = this;

            if (window.MathJax && window.MathJax.Hub) {
                mathjaxP = Promise.resolve(window.MathJax);
            } else if (window.STUDIP && window.STUDIP.loadChunk) {
                mathjaxP = window.STUDIP.loadChunk('mathjax');
            }

            mathjaxP &&
                mathjaxP
                    .then(({ Hub }) => {
                        Hub.Queue(['Typeset', Hub, view.$refs.content]);
                    })
                    .catch(() => {
                        console.log('Warning: Could not load MathJax.');
                    });
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/text.scss';
</style>
