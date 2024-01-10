<template>
    <div>
        <studip-dialog
            :title="$gettext('Feedback erstellen')"
            :confirmText="$gettext('Erstellen')"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            confirmClass="accept"
            height="420"
            width="500"
            @confirm="createFeedback"
            @close="$emit('close')"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Frage') }}
                        <input type="text" v-model="question" >
                    </label>
                    <label>
                        {{ $gettext('Beschreibung') }}
                        <textarea v-model="description"></textarea>
                    </label>
                    <label>
                        <input type="checkbox" v-model="anonymous" >
                        {{ $gettext('Feedback kann anonym abgegeben werden') }}
                    </label>
                    <label>
                        <input type="checkbox" v-model="commentable" >
                        {{ $gettext('Abgegebenes Feedback kann einen Kommentar beinhalten') }}
                    </label>

                </form>
            </template>
        </studip-dialog>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'feedback-create-dialog',
    props: {
        defaultQuestion: {
            type: String,
            default: ''
        },
        defaultDescription: {
            type: String,
            default: ''
        },
        defaultCommentable: {
            type: Boolean,
            default: true
        },
        defaultAnonymous: {
            type: Boolean,
            default: false
        },
        rangeType: {
            type: String,
            required: true
        },
        rangeId: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            question: '',
            description: '',
            commentable: true,
            anonymous: false
        }
    },
    methods: {
        ...mapActions({
            createFeedbackElement: 'feedback-elements/create',
        }),
        createFeedback() {
            const data = {
                attributes: {
                    question: this.question,
                    description:this.description,
                    mode: 1,
                    'results-visible': true,
                    'is-commentable': this.commentable,
                    'anonymous-entries': this.anonymous,
                },
                relationships: {
                    range: {
                        data: {
                            type: this.rangeType,
                            id: this.rangeId,
                        },
                    },
                },
            };
            this.createFeedbackElement(data).then(() => {
                this.$emit('created');
                this.$emit('close');
            });
        },
        initData() {
            this.question = this.defaultQuestion;
            this.description = this.defaultDescription;
            this.commentable = this.defaultCommentable;
            this.anonymous = this.defaultAnonymous;
        }
    },
    mounted() {
        this.initData();
    }
};
</script>
