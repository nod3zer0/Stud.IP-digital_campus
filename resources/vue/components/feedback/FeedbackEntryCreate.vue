<template>
    <div v-if="feedbackElement" class="feedback-entry-create">
        <studip-five-stars-input v-model="rating" />
        <label v-if="isCommentable">
            {{ $gettext('Kommentar') }}
            <textarea v-model="comment"></textarea>
        </label>
        <label v-if="anonymousEntriesEnabled">
            <input type="checkbox" v-model="anonymous" />
            {{ $gettext('Feedback anonym abgeben') }}
        </label>
        <div class="button-wrapper">
            <button class="button accept" @click="submitEntry">
                {{ $gettext('Absenden') }}
            </button>
            <button v-if="hasEntry" class="button cancel" @click="$emit('cancel')">
                {{ $gettext('Abbrechen') }}
            </button>
            
        </div>
    </div>
</template>

<script>
import StudipFiveStarsInput from './StudipFiveStarsInput.vue';
import { mapActions } from 'vuex';

export default {
    name: 'feedback-entry-create',
    components: {
        StudipFiveStarsInput,
    },
    props: {
        feedbackElement: {
            type: Object || null,
        },
        entry: {
            type: Object,
            default: null,
        },
        currentUser: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            rating: 0,
            comment: '',
            anonymous: false
        };
    },
    computed: {
        hasEntry() {
            return this.entry !== null;
        },
        anonymousEntriesEnabled() {
            return this.feedbackElement?.attributes['anonymous-entries'];
        },
        isCommentable() {
            return this.feedbackElement?.attributes['is-commentable'];
        }
    },
    methods: {
        ...mapActions({
            loadFeedbackEntriesById: 'feedback-entries/byId',
            createFeedbackEntries: 'feedback-entries/create',
            updateFeedbackEntries: 'feedback-entries/update',
        }),
        async submitEntry() {
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
                            type: 'users' 
                        }
                    }
                },
            };
            if (this.isCommentable) {
                data.attributes.comment = this.comment
            }
            if (this.anonymousEntriesEnabled) {
                data.attributes.anonymous = this.anonymous;
            }
            if (this.hasEntry) {
                data.id = this.entry.id;
                data.type = this.entry.type;
                await this.updateFeedbackEntries(data);
            } else {
                await this.createFeedbackEntries(data);
            }
            this.$emit('submit');
        },
    },
    mounted() {
        if (this.hasEntry) {
            this.rating = parseInt(this.entry.attributes.rating);
            this.comment = this.entry.attributes.comment;
            this.anonymous = this.entry.attributes.anonymous;
        }
    },
};
</script>