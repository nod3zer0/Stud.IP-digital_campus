<template>
    <section
        v-if="canEdit || userIsTeacher"
        class="cw-block-feedback"
        :class="[emptyFeedback ? 'cw-block-feedback-empty' : '']"
    >
        <span class="sr-only" aria-live="polite">{{ srMessage }}</span>
        <div class="cw-block-features-content">
            <div class="cw-block-feedback-items" v-show="!emptyFeedback" ref="feedbacks">
                <courseware-talk-bubble
                    v-for="feedback in feedback"
                    :key="feedback.id"
                    :payload="buildPayload(feedback)"
                />
            </div>
            <courseware-companion-box
                v-if="!userIsTeacher && feedback.length === 0"
                :msgCompanion="$gettext('Es wurde noch kein Feedback abgegeben.')"
                mood="pointing"
            />
            <div v-if="userIsTeacher" class="cw-block-feedback-create">
                <textarea v-model="feedbackText" :placeholder="placeHolder" spellcheck="true"></textarea>
                <button class="button" @click="postFeedback">{{ $gettext('Senden') }}</button>
            </div>
        </div>
    </section>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareTalkBubble from './CoursewareTalkBubble.vue';
import { mapActions, mapGetters } from 'vuex';


export default {
    name: 'courseware-block-feedback',
    components: {
        CoursewareCompanionBox,
        CoursewareTalkBubble,
    },
    props: {
        block: Object,
        canEdit: Boolean,
    },
    data() {
        return {
            feedbackText: '',
            placeHolder: this.$gettext('Schreiben Sie ein Feedback...'),
            srMessage: ''
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getRelatedFeedback: 'courseware-block-feedback/related',
            getRelatedUser: 'users/related',
            userIsTeacher: 'userIsTeacher',
        }),
        feedback() {
            const { id, type } = this.block;

            return this.getRelatedFeedback({ parent: { id, type }, relationship: 'feedback' });
        },
        emptyFeedback() {
            if (this.feedback === null || this.feedback.length === 0) {
                return true;
            }

            return false;
        }
    },
    methods: {
        ...mapActions({
            createFeedback: 'courseware-block-feedback/create',
            loadRelatedFeedback: 'courseware-block-feedback/loadRelated',
        }),
        buildPayload(feedback) {
            const { id, type } = feedback;
            const user = this.getRelatedUser({ parent: { id, type }, relationship: 'user' });

            return {
                own: user.id === this.userId,
                content: feedback.attributes.feedback,
                chdate: feedback.attributes.chdate,
                mkdate: feedback.attributes.mkdate,
                user_name: user?.attributes?.['formatted-name'] ?? '',
                user_avatar: user?.meta?.avatar.small,
            };
        },
        async loadFeedback() {
            const parent = {
                type: this.block.type,
                id: this.block.id,
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
            this.updateSrMessage(this.$gettext('Feedback gesendet'));
            const data = {
                attributes: {
                    feedback: this.feedbackText,
                },
                relationships: {
                    block: {
                        data: {
                            type: this.block.type,
                            id: this.block.id,
                        },
                    },
                },
            };
            await this.createFeedback(data, { root: true });
            this.feedbackText = '';
            this.loadFeedback();
        },
        updateSrMessage(message) {
            this.srMessage = '';
            this.srMessage = message;
        }
    },
    async mounted() {
        await this.loadFeedback(this.block.id);
    },
    updated() {
        this.$refs.feedbacks.scrollTop = this.$refs.feedbacks.scrollHeight;
    },
    watch: {
        feedback() {
            if (this.feedback && this.feedback.length > 0) {
                this.$emit('hasFeedback');
            }
        }
    }
};
</script>
