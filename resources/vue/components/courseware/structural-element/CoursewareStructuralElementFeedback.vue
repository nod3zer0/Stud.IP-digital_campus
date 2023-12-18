<template>
    <section
        v-if="canEdit || userIsTeacher"
        class="cw-structural-element-feedback"
        :class="[emptyFeedback ? 'cw-structural-element-feedback-empty' : '']"
    >
        <span class="sr-only" aria-live="polite">{{ srMessage }}</span>
        <div class="cw-structural-element-feedback-items" v-show="!emptyFeedback" ref="feedbacks">
            <courseware-talk-bubble
                v-for="feedback in feedback"
                :key="feedback.id"
                :payload="buildPayload(feedback)"
                @delete="deleteFeedback(feedback)"
            />
        </div>
        <courseware-companion-box
            v-if="!userIsTeacher && feedback.length === 0"
            :msgCompanion="$gettext('Es wurde noch keine Anmerkungen abgegeben.')"
            mood="pointing"
        />
        <div v-if="userIsTeacher" class="cw-structural-element-feedback-create">
            <textarea v-model="feedbackText" :placeholder="placeHolder" spellcheck="true"></textarea>
            <button class="button" @click="postFeedback">
                {{ $gettext('Senden') }}
            </button>
        </div>
    </section>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareTalkBubble from '../layouts/CoursewareTalkBubble.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-feedback',
    components: {
        CoursewareCompanionBox,
        CoursewareTalkBubble,
    },
    props: {
        structuralElement: Object,
        canEdit: Boolean,
    },
    data() {
        return {
            feedbackText: '',
            placeHolder: this.$gettext('Schreiben Sie eine Anmerkung...'),
            srMessage: '',
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getRelatedFeedback: 'courseware-structural-element-feedback/related',
            getRelatedUser: 'users/related',
            userIsTeacher: 'userIsTeacher',
        }),
        feedback() {
            const parent = {
                type: this.structuralElement.type,
                id: this.structuralElement.id,
            };

            return this.getRelatedFeedback({ parent, relationship: 'feedback' });
        },
        emptyFeedback() {
            if (this.feedback === null || this.feedback.length === 0) {
                return true;
            }

            return false;
        },
    },
    methods: {
        ...mapActions({
            createFeedback: 'courseware-structural-element-feedback/create',
            loadRelatedFeedback: 'courseware-structural-element-feedback/loadRelated',
            deleteElementFeedback: 'courseware-structural-element-feedback/delete',
        }),
        buildPayload(feedback) {
            const { id, type } = feedback;
            const user = this.getRelatedUser({ parent: { id, type }, relationship: 'user' });

            return {
                own: user.id === this.userId,
                content: feedback.attributes.feedback,
                chdate: feedback.attributes.chdate,
                mkdate: feedback.attributes.mkdate,
                user_formatted_name: user?.attributes?.['formatted-name'] ?? '',
                username: user?.attributes?.username ?? '',
                user_avatar: user?.meta?.avatar.small,
            };
        },
        async loadFeedback() {
            const parent = {
                type: this.structuralElement.type,
                id: this.structuralElement.id,
            };
            await this.loadRelatedFeedback({
                parent,
                relationship: 'feedback',
                options: {
                    include: 'user',
                },
            });
        },
        async postFeedback() {
            this.updateSrMessage(this.$gettext('Anmerkung gesendet'));
            const data = {
                attributes: {
                    feedback: this.feedbackText,
                },
                relationships: {
                    'structural-element': {
                        data: {
                            id: this.structuralElement.id,
                            type: this.structuralElement.type,
                        },
                    },
                },
            };
            await this.createFeedback(data, { root: true });
            this.feedbackText = '';
            this.loadFeedback();
        },
        deleteFeedback(feedback) {
            this.deleteElementFeedback({ id: feedback.id, type: feedback.type });
        },
        updateSrMessage(message) {
            this.srMessage = '';
            this.srMessage = message;
        },
    },
    updated() {
        this.$refs.feedbacks.scrollTop = this.$refs.feedbacks.scrollHeight;
    },
};
</script>
