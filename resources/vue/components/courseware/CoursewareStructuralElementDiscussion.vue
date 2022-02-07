<template>
    <div class="cw-structural-element-discussion">
        <courseware-collapsible-box
            :title="text.comments"
            :open="hasComments"
        >
            <courseware-structural-element-comments
            :structuralElement="structuralElement"
            @hasComments="hasComments = true"
            />
        </courseware-collapsible-box>

        <courseware-collapsible-box
            v-if="canEdit || userIsTeacher"
            :title="text.feedback"
            :open="hasFeedback"
        >
            <courseware-structural-element-feedback
                :structuralElement="structuralElement"
                :canEdit="canEdit"
                @hasFeedback="hasFeedback = true"
            />
        </courseware-collapsible-box>
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareStructuralElementComments from './CoursewareStructuralElementComments.vue';
import CoursewareStructuralElementFeedback from './CoursewareStructuralElementFeedback.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-discussion',
    components: {
        CoursewareCollapsibleBox,
        CoursewareStructuralElementComments,
        CoursewareStructuralElementFeedback,
    },
    props: {
        structuralElement: Object,
        canEdit: Boolean
    },
    data() {
        return {
            hasComments: false,
            hasFeedback: false,
            text: {
                comments: this.$gettext('Kommentare zur Seite'),
                feedback: this.$gettext('Feedback zur Seite')
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
