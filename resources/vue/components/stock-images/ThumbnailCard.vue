<template>
    <div class="stock-images-thumbnail-card">
        <Thumbnail :url="url" width="200px" style="background: var(--light-gray-color-40)" contain />
        <table class="default">
            <tbody>
                <tr>
                    <td>{{ $gettext('Format') }}</td>
                    <td>
                        <studip-icon shape="file-pic" role="presentation" alt="" />
                        {{ format }}
                        ({{ width }}&nbsp;×&nbsp;{{ height }})
                    </td>
                </tr>
                <tr>
                    <td>{{ $gettext('Größe') }}</td>
                    <td>
                        <studip-file-size :size="size" />
                    </td>
                </tr>
                <tr v-if="mkdate">
                    <td>{{ $gettext('Erstellt') }}</td>
                    <td>
                        <studip-date-time :timestamp="mkdate / 1000" />
                    </td>
                </tr>

                <tr v-if="chdate">
                    <td>{{ $gettext('Geändert') }}</td>
                    <td>
                        <studip-date-time :timestamp="chdate / 1000" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import Thumbnail from './Thumbnail.vue';
import { getFormat } from './format.js';

export default {
    props: {
        chdate: {
            type: Date,
            required: false,
        },
        height: {
            type: Number,
            required: true,
        },
        mimeType: {
            type: String,
            required: true,
        },
        mkdate: {
            type: Date,
            required: false,
        },
        size: {
            type: Number,
            required: true,
        },
        url: {
            type: String,
            required: true,
        },
        width: {
            type: Number,
            required: true,
        },
    },
    components: { Thumbnail },
    computed: {
        format() {
            return getFormat(this.mimeType);
        },
    },
};
</script>
