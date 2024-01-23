<template>
    <studip-dialog
        :title="$gettext('Feedback zur Aufgabe ändern')"
        :confirmText="$gettext('Speichern')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="420"
        @close="$emit('close')"
        @confirm="update"
    >
        <template #dialogContent>
            <CompanionBox
                v-if="localContent === ''"
                mood="pointing"
                :msgCompanion="
                    $gettext('Sie haben kein Feedback geschrieben, beim Speichern wird dieses Feedback gelöscht!')
                "
            />
            <form class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Feedback') }}
                    <textarea v-model="localContent" />
                </label>
            </form>
        </template>
    </studip-dialog>
</template>

<script>
import CompanionBox from '../layouts/CoursewareCompanionBox.vue';

export default {
    props: ['content'],
    components: {
        CompanionBox,
    },
    data: () => ({
        localContent: '',
    }),
    methods: {
        resetLocalVars() {
            this.localContent = this.content;
        },
        update() {
            this.$emit('update', { content: this.localContent });
        },
    },
    mounted() {
        this.resetLocalVars();
    },
    watch: {
        content(newValue) {
            if (newValue !== this.localContent) {
                this.resetLocalVars();
            }
        },
    },
};
</script>
