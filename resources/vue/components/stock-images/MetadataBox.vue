<template>
    <div class="upload-metadata-box">
        <div>
            <ThumbnailCard
                v-if="fileURL"
                :height="height ?? 0"
                :mime-type="file.type"
                :size="file.size"
                :url="fileURL"
                :width="width ?? 0"
            />
        </div>
        <div>
            <AttributesFieldset :metadata="metadata" :suggested-tags="suggestedTags" @change="onChange" />
        </div>
    </div>
</template>

<script>
import ThumbnailCard from './ThumbnailCard.vue';
import AttributesFieldset from './AttributesFieldset.vue';
import { getFormat } from './format.js';

export default {
    props: ['file', 'metadata', 'suggestedTags'],

    components: { AttributesFieldset, ThumbnailCard },

    data: () => ({
        fileURL: null,
        height: null,
        image: null,
        width: null,
    }),

    computed: {
        tags: {
            get() {
                return this.metadata.tags;
            },
            set(tags) {
                this.$set(this.metadata, 'tags', tags);
            },
        },
    },

    methods: {
        onChange(metadata) {
            this.$emit('change', metadata);
        },
    },

    mounted() {
        this.fileURL = URL.createObjectURL(this.file);
        this.image = new Image();
        this.image.onload = ({ target }) => {
            this.height = target.height;
            this.width = target.width;
        };
        this.image.src = this.fileURL;
        this.$set(this.metadata, 'title', this.file.name);
    },

    beforeDestroy() {
        if (this.fileURL) {
            URL.revokeObjectURL(this.fileURL);
        }
    },
};
</script>

<style scoped>
.upload-metadata-box {
    display: flex;
    gap: 1em;
}
.upload-metadata-box > div:first-child {
    flex-basis: 200px;
    flex-grow: 0;
    overflow: hidden;
}
.upload-metadata-box > div:last-child {
    flex-basis: 30em;
    flex-grow: 1;
}
.upload-metadata-box div:first-child ul {
    font-size: 0.9em;
    line-height: 1.5;
    margin-block-start: 1em;
    padding-inline-start: 0;
}
</style>
