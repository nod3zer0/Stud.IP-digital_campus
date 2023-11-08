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
            />
        </div>
        <courseware-companion-box
                v-if="!userIsTeacher && feedback.length === 0"
                :msgCompanion="$gettext('Es wurde noch kein Feedback abgegeben.')"
                mood="pointing"
            />
        <div v-if="userIsTeacher" class="cw-structural-element-feedback-create">
            <textarea v-model="feedbackText" :placeholder="placeHolder" spellcheck="true"></textarea>
            <button class="button" @click="postFeedback"><translate>Senden</translate></button>
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
            placeHolder: this.$gettext('Schreiben Sie ein Feedback...'),
            srMessage: ''
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
        }
    },
    methods: {
        ...mapActions({
            createFeedback: 'courseware-structural-element-feedback/create',
            loadRelatedFeedback: 'courseware-structural-element-feedback/loadRelated',
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
            this.updateSrMessage(this.$gettext('Feedback gesendet'));
            const data = {
                attributes: {
                    feedback: this.feedbackText,
                },
                relationships: {
                    'structural-element': {
                        data: {
                            id: this.structuralElement.id,
                            type: this.structuralElement.type
                        }
                    }
                },
            };
            await this.createFeedback( data, { root: true });
            this.feedbackText = '';
            this.loadFeedback();
        },
        updateSrMessage(message) {
            this.srMessage = '';
            this.srMessage = message;
        }
    },
    async mounted() {
        await this.loadFeedback();
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
}
</script>