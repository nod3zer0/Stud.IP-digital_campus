<template v-if="isReadable">
    <button
        v-if="isButton"
        class="file-chooser-item"
        :class="{ selected: isSelected, disabled: isFolderChooser }"
        :title="fileName"
        @click="selectFile"
        v-on:dblclick="instantSelectFile"
    >
        <studip-icon :shape="fileIcon" :size="48" />
        <span>{{ fileName }}</span>
    </button>
    <tr v-else :class="{ selected: isSelected }">
        <td class="document-icon">
            <a href="#" @click.prevent="selectFile" v-on:dblclick.prevent="instantSelectFile">
                <studip-icon :shape="fileIcon" :size="24" />
            </a>
        </td>
        <td>
            <a href="#" @click.prevent="selectFile" v-on:dblclick.prevent="instantSelectFile">{{ fileName }}</a>
        </td>
        <td>{{ fileSize }}</td>
        <td class="responsive-hidden">{{ fileOwner }}</td>
        <td>{{ fileMkdate }}</td>
    </tr>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'file-chooser-file-item',
    props: {
        file: {
            type: Object,
            required: true,
        },
        tag: {
            type: String,
            default: 'button',
            validator: (tag) => {
                return ['button', 'tr'].includes(tag);
            },
        },
    },
    computed: {
        ...mapGetters({
            selectedFileId: 'file-chooser/selectedFileId',
            isFolderChooser: 'file-chooser/isFolderChooser',
        }),
        isReadable() {
            return this.file.attributes['is-readable'];
        },
        isSelected() {
            return this.selectedFileId === this.file.id;
        },
        isButton() {
            return this.tag === 'button';
        },
        isTableRow() {
            return this.tag === 'tr';
        },
        fileName() {
            return this.file.attributes.name;
        },
        fileMimeType() {
            return this.file.attributes['mime-type'];
        },
        fileIcon() {
            if (this.fileMimeType.includes('audio')) {
                return 'file-audio2';
            }
            if (this.fileMimeType.includes('video')) {
                return 'file-video';
            }
            if (this.fileMimeType.includes('image')) {
                return 'file-pic2';
            }
            if (this.fileMimeType.includes('pdf')) {
                return 'file-pdf';
            }
            if (this.fileMimeType.includes('zip')) {
                return 'file-archive';
            }
            if (this.fileMimeType.includes(['msexcel', 'spreadsheetml.sheet'])) {
                return 'file-excel';
            }
            if (this.fileMimeType.includes(['opendocument.spreadsheet'])) {
                return 'file-spreadsheet';
            }
            if (this.fileMimeType.includes(['msword', 'wordprocessingml.document'])) {
                return 'file-word';
            }
            if (this.fileMimeType.includes(['opendocument.text'])) {
                return 'file-text';
            }
            if (this.fileMimeType.includes(['mspowerpoint', 'presentationml.presentation'])) {
                return 'file-ppt ';
            }
            if (this.fileMimeType.includes(['opendocument.presentation'])) {
                return 'file-presentation';
            }

            return 'file';
        },
        fileSize() {
            let i = -1;
            let size = this.file.attributes.filesize;
            const units = ['KB', 'MB', 'GB', 'TB'];
            do {
                size = size / 1024;
                i++;
            } while (size > 1000);

            return Math.max(size, 0.1).toFixed(1) + ' ' + units[i];
        },
        fileOwner() {
            return this.file.relationships.owner?.meta?.name;
        },
        fileMkdate() {
            const date = new Date(this.file.attributes.mkdate);
            const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };

            return date.toLocaleDateString('de-DE', options);
        },
    },
    methods: {
        ...mapActions({
            setSelectedFileId: 'file-chooser/setSelectedFileId',
        }),
        selectFile() {
            if (this.isFolderChooser) {
                return;
            }
            this.setSelectedFileId(this.file.id);
        },
        instantSelectFile() {
            this.selectFile();
            this.$emit('selectId');
        },
    },
};
</script>
