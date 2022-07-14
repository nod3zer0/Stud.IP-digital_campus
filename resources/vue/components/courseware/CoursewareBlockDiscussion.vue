<template>
    <div class="cw-block-discussion">
        <courseware-collapsible-box
            :title="text.comments"
            :open="hasComments"
        >
            <courseware-block-comments
            :block="block"
            @hasComments="hasComments = true"
            />
        </courseware-collapsible-box>

        <courseware-collapsible-box
            v-if="canEdit || userIsTeacher"
            :title="text.feedback"
            :open="hasFeedback"
        >
            <courseware-block-feedback
                :block="block"
                :canEdit="canEdit"
                @hasFeedback="hasFeedback = true"
            />
        </courseware-collapsible-box>
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareBlockComments from './CoursewareBlockComments.vue';
import CoursewareBlockFeedback from './CoursewareBlockFeedback.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-block-discussion',
    components: {
        CoursewareCollapsibleBox,
        CoursewareBlockComments,
        CoursewareBlockFeedback,
    },
    props: {
        block: Object,
        canEdit: Boolean
    },
    data() {
        return {
            hasComments: false,
            hasFeedback: false,
            text: {
                comments: this.$gettext('Kommentare'),
                feedback: this.$gettext('Feedback')
            }
        }
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
        }),
    }
}
</script>
