<template>
    <tr @click="onSelect">
        <td>
            <label>
                <input type="checkbox" :checked="isChecked" @change="onCheckboxChange" />
                <span class="sr-only">{{
                    $gettextInterpolate($gettext('%{context} auswählen'), { context: stockImage.attributes.title })
                }}</span>
            </label>
        </td>
        <td>
            <div>
                <Thumbnail
                    v-if="thumbnailUrl"
                    :url="thumbnailUrl"
                    width="6rem"
                    style="background: var(--light-gray-color-40)"
                    contain
                />
                <div>
                    <div>{{ stockImage.attributes.title }}</div>
                    <div>
                        <span class="stock-image-author">{{ stockImage.attributes.author }}</span>
                        <span class="stock-image-tags">
                            <button
                                type="button"
                                class="stock-image-tag"
                                v-for="tag in stockImage.attributes.tags"
                                :key="tag"
                                @click="onTagClick(tag)"
                            >
                                {{ tag }}
                            </button>
                        </span>
                    </div>

                    <ul class="stock-image-palette" :title="$gettext('Bildfarben')" role="presentation">
                        <li
                            v-for="(color, index) in palette"
                            :key="index"
                            :style="`background-color: rgb(${color[0]} ${color[1]} ${color[2]});`"
                            :alt="color.join(',')"
                        ></li>
                    </ul>
                </div>
            </div>
        </td>
        <td>
            <studip-icon shape="file-pic" alt="" />
            {{ imageFormat(stockImage) }}
        </td>
        <td><studip-file-size :size="stockImage.attributes.size" /></td>
        <td>{{ stockImage.attributes.width }} × {{ stockImage.attributes.height }}</td>
    </tr>
</template>
<script>
import Thumbnail from './Thumbnail.vue';
import { getFormat } from './format.js';

export default {
    props: {
        stockImage: {
            type: Object,
            required: true,
        },
        isChecked: {
            type: Boolean,
            default: false,
        },
    },
    components: {
        Thumbnail,
    },
    computed: {
        palette() {
            return this.stockImage.attributes.palette ?? [];
        },
        thumbnailUrl() {
            return (
                this.stockImage.attributes['download-urls'].small ??
                this.stockImage.attributes['download-urls'].original
            );
        },
    },
    methods: {
        imageFormat(image) {
            return getFormat(image.attributes['mime-type']);
        },
        onCheckboxChange() {
            this.$emit('checked');
        },
        onSelect({ target }) {
            if (!['INPUT', 'LABEL', 'BUTTON'].includes(target.tagName)) {
                this.$emit('select');
            }
        },
        onTagClick(tag) {
            this.$emit('search', tag);
        },
    },
};
</script>

<style scoped>
tr > td:nth-child(1) {
    height: 100%;
    min-height: 100%;
    padding: 0;
}

tr > td:nth-child(1) > label {
    height: 100%;
    min-height: 100%;
    display: flex;
    padding-inline: 1em;
}

tr > td:nth-child(2) > div {
    align-items: center;
    display: flex;
    flex-direction: row;
    gap: 1rem;
}

tr > td:nth-child(2) > div div:last-child {
    flex: 1;
    margin-inline-end: 1rem;
}

tr > td:nth-child(3) img {
    vertical-align: middle;
}

.stock-image-author,
.stock-image-tags {
    font-size: 0.8em;
    opacity: 0.75;
}
.stock-image-tags {
    display: flex;
    gap: 0.5em;
    margin-block: 0.5em;
}
.stock-image-tag {
    background-color: var(--base-color);
    border: none;
    color: var(--white);
    cursor: pointer;
    padding: 0.25em 0.5em;
}
.stock-image-palette {
    display: flex;
    width: 100%;
    height: 0.25em;
    padding-inline-start: 0;
}

.stock-image-palette li {
    display: inline;
    flex: 1;
}
</style>
