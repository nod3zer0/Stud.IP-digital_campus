<template>
    <div class="cw-block cw-block-text">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            ref="defaultBlock"
            @storeEdit="storeText"
            @closeEdit="closeEdit"
        >
            <template #content>
                <section class="cw-block-content" v-html="currentText" ref="content"></section>
            </template>
            <template v-if="canEdit" #edit>
                <studip-wysiwyg v-model="currentText"></studip-wysiwyg>
            </template>
            <template #info><translate>Informationen zum Text-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import StudipWysiwyg from '../StudipWysiwyg.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-text-block',
    components: {
        CoursewareDefaultBlock,
        StudipWysiwyg,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
        };
    },
    computed: {
        text() {
            return this.block?.attributes?.payload?.text;
        },
    },
    mounted() {
        this.currentText = this.text;
        this.loadMathjax();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        closeEdit() {
            this.currentText = this.text;
            this.loadMathjax();
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

            mathjaxP && mathjaxP
            .then(({ Hub }) => {
                Hub.Queue(['Typeset', Hub, view.$refs.content]);
            })
            .catch(() => {
                console.log('Warning: Could not load MathJax.');
            });
        }
    },
};
</script>
