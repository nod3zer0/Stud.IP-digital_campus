<template>
    <form class="default feedback-element-update" @submit.prevent="">
        <h3>{{ $gettext('Feedback-Element bearbeiten') }}</h3>
        <label>
            {{ $gettext('Frage') }}
            <input type="text" v-model="currentQuestion" />
        </label>
        <label>
            {{ $gettext('Beschreibung') }}
            <textarea v-model="currentDescription"></textarea>
        </label>
        <div class="button-wrapper">
            <button class="button accept" @click="submitUpdate">
                {{ $gettext('Absenden') }}
            </button>
            <button class="button cancel" @click="$emit('cancel')">
                {{ $gettext('Abbrechen') }}
            </button>
        </div>
    </form>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'feedback-element-update',
    props: {
        feedbackElementId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            currentQuestion: '',
            currentDescription: '',
        };
    },
    computed: {
        ...mapGetters({
            feedbackElementById: 'feedback-elements/byId',
        }),
        feedbackElement() {
            return this.feedbackElementById({ id: this.feedbackElementId });
        },
    },
    methods: {
        ...mapActions({
            updateFeedbackElement: 'feedback-elements/update',
        }),
        async submitUpdate() {
            let data = {
                id: this.feedbackElementId,
                type: 'feedback-elements',
                attributes: {
                    question: this.currentQuestion,
                    description: this.currentDescription,
                },
            };
            await this.updateFeedbackElement(data);
            this.$emit('submit');
        },
    },
    mounted() {
        this.currentQuestion = this.feedbackElement.attributes?.question;
        this.currentDescription = this.feedbackElement.attributes?.description.replace(/<\/?[^>]+>/gi, ' ').trim();
    },
};
</script>
