<template>
    <studip-dialog
        :title="$gettext('Feedback zur Aufgabe geben')"
        :confirmText="$gettext('Speichern')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="420"
        @close="$emit('close')"
        @confirm="create"
    >
        <template #dialogContent>
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
export default {
    props: ['content'],
    data: () => ({
        localContent: '',
    }),
    methods: {
        resetLocalVars() {
            this.localContent = this.content;
        },
        create() {
            this.$emit('create', { content: this.localContent });
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
