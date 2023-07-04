<template>
    <studip-dialog
        v-if="show"
        height="720"
        width="960"
        :title="$gettext('Bild hinzufügen')"
        @close="onCancel"
        closeClass="cancel"
        :closeText="$gettext('Abbrechen')"
    >
        <template #dialogContent>
            <form id="stock-images-upload-form" class="default" @submit.prevent="onSubmit">
                <UploadBox v-if="state === STATES.IDLE" @upload="onUpload" />
                <MetadataBox
                    v-if="state === STATES.UPLOADED"
                    :file="file"
                    :metadata="metadata"
                    :suggested-tags="suggestedTags"
                    @change="onChangeMetadata"
                />
            </form>
        </template>

        <template #dialogButtons>
            <button
                form="stock-images-upload-form"
                type="submit"
                class="button accept"
                :disabled="state !== STATES.UPLOADED"
            >
                {{ $gettext('Hinzufügen') }}
            </button>
        </template>
    </studip-dialog>
</template>
<script>
import MetadataBox from './MetadataBox.vue';
import UploadBox from './UploadBox.vue';
import { mapActions } from 'vuex';

const STATES = { IDLE: 'idle', UPLOADED: 'uploaded' };

export default {
    props: ['show', 'suggestedTags'],
    components: { MetadataBox, UploadBox },
    data: () => ({
        file: null,
        metadata: {
            title: '',
            description: '',
            author: '',
            license: '',
            tags: [],
        },
        state: STATES.IDLE,
        STATES,
    }),
    methods: {
        onCancel() {
            this.$emit('cancel');
            this.resetLocalCopy();
        },
        onChangeMetadata(metadata) {
            this.metadata = metadata;
        },
        onSubmit() {
            this.$emit('confirm', { file: this.file, metadata: this.metadata });
        },
        onUpload({ file }) {
            this.file = file;
            this.state = STATES.UPLOADED;
        },
        resetLocalCopy() {
            this.file = null;
            this.metadata = {};
            this.state = STATES.IDLE;
        },
    },
    watch: {
        show() {
            this.resetLocalCopy();
        },
    },
};
</script>

<style scoped>
form {
    height: 100%;
}
</style>
