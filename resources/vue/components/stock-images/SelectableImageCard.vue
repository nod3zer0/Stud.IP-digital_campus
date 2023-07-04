<template>
    <div class="stock-images-selectable-image" tabindex="0">
        <Thumbnail :url="thumbnailUrl" contain class="stock-images-image-card__thumbnail" width="8rem" />
        <div>{{ stockImage.attributes?.title ?? '' }}</div>
    </div>
</template>

<script>
import Thumbnail from './Thumbnail.vue';

export default {
    props: {
        stockImage: {
            type: Object,
            required: true,
        },
    },
    components: { Thumbnail },
    computed: {
        thumbnailUrl() {
            return (
                this.stockImage.attributes['download-urls'].small ??
                this.stockImage.attributes['download-urls'].original
            );
        },
    },
};
</script>

<style scoped>
.stock-images-selectable-image {
    overflow: hidden;
    position: relative;
}
.stock-images-selectable-image > :last-child {
    background: #ffffff;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 8rem;
    min-height: 3em;
    -webkit-line-clamp: 2;
    display: -webkit-box;
    -webkit-box-orient: vertical;
}
.stock-images-image-card__thumbnail {
    background-image: url(../images/checkered-background.png);
}
</style>
