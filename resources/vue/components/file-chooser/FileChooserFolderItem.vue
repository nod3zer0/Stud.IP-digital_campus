<template v-if="folderIsReadable">
    <button
        v-if="isButton"
        class="file-chooser-item"
        :class="{ selected: isSelected, disabled: !isFolderChooser }"
        :title="folderName"
        @click="selectFolder"
        v-on:dblclick="openFolder"
    >
        <studip-icon :shape="folderIcon" :size="48" />
        <span>{{ folderName }}</span>
    </button>
    <tr v-else :class="{ selected: isSelected }">
        <td class="document-icon">
            <a href="#" @click.prevent="selectFolder" v-on:dblclick.prevent="openFolder"
                ><studip-icon :shape="folderIcon" :size="24"
            /></a>
        </td>
        <td>
            <a href="#" @click.prevent="selectFolder" v-on:dblclick.prevent="openFolder">{{ folderName }}</a>
        </td>
        <td>
            <template v-if="!folderIsEmpty">{{ folderSize }}</template>
        </td>
        <td class="responsive-hidden">{{ folderOwner }}</td>
        <td>{{ folderMkdate }}</td>
    </tr>
</template>

<script>
import folderIconMixin from '@/vue/mixins/file-chooser/folder-icon.js';
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'file-chooser-folder-item',
    mixins: [folderIconMixin],
    props: {
        folder: {
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
            selectedFolderId: 'file-chooser/selectedFolderId',
            isFolderChooser: 'file-chooser/isFolderChooser',
        }),
        isSelected() {
            return this.selectedFolderId === this.folder.id;
        },
        isButton() {
            return this.tag === 'button';
        },
        isTableRow() {
            return this.tag === 'tr';
        },
        folderOwner() {
            return this.folder.relationships.owner?.meta?.name;
        },
        folderMkdate() {
            const date = new Date(this.folder.attributes.mkdate);
            const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };

            return date.toLocaleDateString('de-DE', options);
        },
        folderSubfolderCounter() {
            return this.folder.relationships?.folders?.meta?.count ?? 0;
        },
        folderFilesCounter() {
            return this.folder.relationships?.['file-refs']?.meta?.count ?? 0;
        },
        folderSize() {
            const length = this.folderSubfolderCounter + this.folderFilesCounter;
            return this.$gettextInterpolate(this.$ngettext('%{length} Objekt', '%{length} Objekte', length), {
                length: length,
            });
        },
    },
    methods: {
        ...mapActions({
            setSelectedFolderId: 'file-chooser/setSelectedFolderId',
            setActiveFolderId: 'file-chooser/setActiveFolderId',
        }),
        selectFolder() {
            if (!this.isFolderChooser) {
                return;
            }
            this.setSelectedFolderId(this.folder.id);
        },
        openFolder() {
            this.setActiveFolderId(this.folder.id);
            this.setSelectedFolderId('');
        },
    },
};
</script>
