<template>
    <div class="cw-block-discussion">
        <courseware-call-to-action-box
            v-if="commentable"
            iconShape="chat"
            :actionTitle="callToActionTitleComments"
            :titleClosed="text.comments.titleClosed"
            :titleOpen="text.comments.titleOpen"
            :foldable="true"
            :open="false"
        >
            <template #content>
                <courseware-block-comments :block="block" />
            </template>
        </courseware-call-to-action-box>

        <courseware-call-to-action-box
            v-if="showFeedback"
            iconShape="exclaim-circle"
            :actionTitle="callToActionTitleFeedback"
            :titleClosed="text.feedback.titleClosed"
            :titleOpen="text.feedback.titleOpen"
            :foldable="true"
            :open="displayFeedback"
        >
            <template #content>
                <courseware-block-feedback :block="block" :canEdit="canEdit" />
            </template>
        </courseware-call-to-action-box>
    </div>
</template>

<script>
import CoursewareCallToActionBox from '../layouts/CoursewareCallToActionBox.vue';
import CoursewareBlockComments from './CoursewareBlockComments.vue';
import CoursewareBlockFeedback from './CoursewareBlockFeedback.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-block-discussion',
    components: {
        CoursewareCallToActionBox,
        CoursewareBlockComments,
        CoursewareBlockFeedback,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        commentable: Boolean,
        displayFeedback: Boolean
    },
    data() {
        return {
            text: {
                comments: {
                    titleClosed: this.$gettext('Kommentare anzeigen'),
                    titleOpen: this.$gettext('Kommentare ausblenden'),
                },
                feedback: {
                    titleClosed: this.$gettext('Anmerkungen anzeigen'),
                    titleOpen: this.$gettext('Anmerkungen ausblenden'),
                },
            },
        };
    },
    computed: {
        ...mapGetters({
            getRelatedFeedback: 'courseware-block-feedback/related',
            getRelatedComments: 'courseware-block-comments/related',
            userIsTeacher: 'userIsTeacher',
        }),
        feedback() {
            const { id, type } = this.block;

            return this.getRelatedFeedback({ parent: { id, type }, relationship: 'feedback' });
        },
        feedbackCounter() {
            return this.feedback?.length ?? 0;
        },
        hasFeedback() {
            if (this.feedback === null ||  this.feedbackCounter === 0) {
                return false;
            }

            return true;
        },
        showFeedback() {
            return ((this.canEdit || this.userIsTeacher) && this.hasFeedback) || this.displayFeedback;
        },
        callToActionTitleFeedback() {
            return this.$gettextInterpolate(
                this.$ngettext(
                    '%{length} Anmerkung (Nur für Nutzende mit Schreibrechten sichtbar)',
                    '%{length} Anmerkungen (Nur für Nutzende mit Schreibrechten sichtbar)',
                    this.feedbackCounter
                ),
            { length: this.feedbackCounter });
        },
        comments() {
            const { id, type } = this.block;

            return this.getRelatedComments({ parent: { id, type }, relationship: 'comments' });
        },
        commentsCounter() {
            return this.comments?.length ?? 0;
        },
        callToActionTitleComments() {
            return this.$gettextInterpolate(
                this.$ngettext(
                    '%{length} Kommentar',
                    '%{length} Kommentare',
                    this.commentsCounter
                ),
            { length: this.commentsCounter });
        },
    },
};
</script>
