<template>
    <studip-dialog
        :title="title"
        :closeText="$gettext('Schließen')"
        height="600"
        width="600"
        @close="$emit('close')"
    >
        <template v-slot:dialogContent>
            <h2 class="cw-comments-overview-dialog-comments-context">
                <a :href="contextUrl">{{ context }}</a>
            </h2>
            <courseware-block-comments
                v-if="isBlock && isComment"
                :block="item" 
            />
            <courseware-structural-element-comments
                v-if="isStructuralElement && isComment"
                :structuralElement="item"
            />
            <courseware-block-feedback
                v-if="isBlock && isFeedback"
                :block="item" 
            />
            <courseware-structural-element-feedback
                v-if="isStructuralElement && isFeedback"
                :structuralElement="item"
            />
        </template>
    </studip-dialog>
</template>

<script>
import CoursewareBlockComments from './blocks/CoursewareBlockComments.vue';
import CoursewareBlockFeedback from './blocks/CoursewareBlockFeedback.vue';
import CoursewareStructuralElementComments from './structural-element/CoursewareStructuralElementComments.vue';
import CoursewareStructuralElementFeedback from './structural-element/CoursewareStructuralElementFeedback.vue';

export default {
    name: 'courseware-comments-overview-dialog',
    components: {
        CoursewareBlockComments,
        CoursewareBlockFeedback,
        CoursewareStructuralElementComments,
        CoursewareStructuralElementFeedback
    },
    props: {
        itemType: String,
        item: Object,
        comType: String,
    },
    computed: {
        context() {
            if (this.isBlock) {
                const block = this.item;
                return `${block.unitName} | ${block.element.attributes.title} | ${block.attributes.title}`;
            }
            if (this.isStructuralElement) {
                const element = this.item;
                return `${element.unitName} | ${element.attributes.title}`;
            }
            return '';
        },
        contextUrl() {
            if (this.isBlock) {
                return this.item.elementURL
            }
            if (this.isStructuralElement) {
                return this.item.url;
            }
            return '';
        },
        title() {
            if (this.isComment) {
                return this.$gettext('Kommentare');
            }
            if (this.isFeedback) {
                return this.$gettext('Feedback');
            }

            return '';

        },
        isStructuralElement() {
            return this.itemType === 'structuralElement';
        },
        isBlock() {
            return this.itemType === 'block';
        },
        isComment() {
            return this.comType === 'comment';
        },
        isFeedback() {
            return this.comType === 'feedback';
        }
    },
};
</script>
