<template>
    <studip-dialog
        v-if="stockImage"
        height="720"
        width="960"
        :title="$gettext('Bild bearbeiten')"
        @close="onCancel"
        closeClass="cancel"
        :closeText="$gettext('Schließen')"
    >
        <template v-slot:dialogContent>
            <form id="stock-images-edit-form" class="default" @submit.prevent="onSubmit">
                <div>
                    <ThumbnailCard
                        :chdate="new Date(stockImage.attributes.chdate)"
                        :height="stockImage.attributes.height"
                        :mime-type="stockImage.attributes['mime-type']"
                        :mkdate="new Date(stockImage.attributes.mkdate)"
                        :size="stockImage.attributes.size"
                        :url="stockImage.attributes['download-urls'].small"
                        :width="stockImage.attributes.width"
                    />
                </div>

                <div>
                    <AttributesFieldset :metadata="metadata" :suggested-tags="suggestedTags" @change="onChange" />
                </div>
            </form>
        </template>

        <template #dialogButtons>
            <button form="stock-images-edit-form" type="submit" class="button accept">
                {{ $gettext('Speichern') }}
            </button>
        </template>
    </studip-dialog>
</template>
<script>
import ThumbnailCard from './ThumbnailCard.vue';
import AttributesFieldset from './AttributesFieldset.vue';

export default {
    props: ['stockImage', 'suggestedTags'],
    components: { AttributesFieldset, ThumbnailCard },
    data: () => ({
        metadata: {},
    }),
    methods: {
        onCancel() {
            this.$emit('cancel');
        },
        onChange(metadata) {
            this.metadata = metadata;
        },
        onSubmit() {
            this.$emit('confirm', { ...this.metadata });
        },
        resetLocalCopy() {
            const {
                title = '',
                description = '',
                author = '',
                license = '',
                tags = [],
            } = this.stockImage?.attributes ?? [];
            this.metadata = { title, description, author, license, tags };
        },
    },
    mounted() {
        this.resetLocalCopy();
    },
    watch: {
        stockImage() {
            this.resetLocalCopy();
        },
    },
};
</script>

<style scoped>
form {
    display: flex;
    height: 100%;
    gap: 1.5em;
}

form > *:first-child {
    flex-basis: 200px;
    flex-grow: 0;
    overflow: hidden;
}

form > *:last-child {
    flex-basis: 30em;
    flex-grow: 1;
}
</style>
