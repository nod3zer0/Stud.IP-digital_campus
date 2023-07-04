<template>
    <div class="stock-images-thumbnail" v-if="url">
        <div :style="{ width }">
            <img :src="url" :style="{ 'object-fit': contain ? 'contain' : 'cover' }" role="presentation" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        url: {
            type: String,
            required: true,
        },
        width: {
            type: String,
            default: '6rem',
        },
        contain: {
            type: Boolean,
            default: false
        }
    },
};
</script>

<style scoped>
.stock-images-thumbnail {
    display: inline-flex;
    position: relative;
}
.stock-images-thumbnail > div {
    aspect-ratio: 1/1;
    display: block;
    overflow: hidden;
}

img {
    display: block;
    height: 100%;
    max-width: 100%;
    width: 100%;
}
</style>
