<template>
    <studip-dialog
        height="430"
        width="600"
        :title="$gettext('Feedback')"
        :confirmText="$gettext('Feedback abgeben')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        @close="$emit('close')"
        @confirm="submitEntry"
    >
        <template v-slot:dialogContent>
            <h2>{{ $gettextInterpolate($gettext('Bewertung für %{title}'),  { title: structuralElement.attributes.title }) }}</h2>

            <div class="feedback-entry-create">
                <studip-five-stars-input v-model="rating" />
                <label v-if="isCommentable">
                    {{ $gettext('Kommentar') }}
                    <textarea v-model="comment"></textarea>
                </label>
                <label v-if="anonymousEntriesEnabled">
                    <input type="checkbox" v-model="anonymous" />
                    {{ $gettext('Feedback anonym abgeben') }}
                </label>
            </div>
        </template>
    </studip-dialog>
</template>
<script>
import StudipFiveStarsInput from '../../feedback/StudipFiveStarsInput.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-feedback-popup',
    components: {
        StudipFiveStarsInput,
    },
    props: {
        feedbackElement: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            rating: 0,
            comment: '',
            anonymous: false
        };
    },
    computed: {
        ...mapGetters({
            currentUser: 'currentUser',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        structuralElement() {
            return this.structuralElementById({ id: this.feedbackElement.relationships.range.data.id });
        },
        anonymousEntriesEnabled() {
            return this.feedbackElement.attributes['anonymous-entries'];
        },
        isCommentable() {
            return this.feedbackElement.attributes['is-commentable'];
        }
    },
    methods: {
        ...mapActions({
            createFeedbackEntries: 'feedback-entries/create',
        }),
        submitEntry() {
            let data = {
                attributes: {
                    rating: this.rating,
                },
                relationships: {
                    'feedback-element': {
                        data: {
                            type: 'feedback-elements',
                            id: this.feedbackElement.id,
                        },
                    },
                    author: {
                        data: {
                            id: this.currentUser.id,
                            type: 'users',
                        },
                    },
                },
            };
            if (this.isCommentable) {
                data.attributes.comment = this.comment
            }
            if (this.anonymousEntriesEnabled) {
                data.attributes.anonymous = this.anonymous;
            }
            this.createFeedbackEntries(data);
            this.$emit('submit');
        },
    },
};
</script>
<style scoped>
h2 {
    margin-top: 0;
    margin-bottom: 20px;
}
</style>
