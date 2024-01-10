<template v-if="folderIsReadable">
    <li class="file-chooser-tree-item">
        <span class="folder-toggle">
            <a
                v-if="hasSubfolders"
                herf="#"
                @click.prevent="toggleSubfolders"
                :title="unfold ? $gettext('Ordner zuklappen') : $gettext('Ordner aufklappen')"
            >
                <studip-icon :shape="unfold ? 'arr_1down' : 'arr_1right'" />
            </a>
        </span>
        <a href="#" @click.prevent="selectFolder" :class="{ selected: isSelected }">
            <studip-icon :shape="folderIcon" />
            <span>{{ folder.attributes.name }}</span>
        </a>
        <ul v-if="unfold" class="file-chooser-tree">
            <li v-for="child in folder.children" :key="child.id" class="file-chooser-tree-item">
                <file-chooser-tree :folder="child" />
            </li>
        </ul>
    </li>
</template>

<script>
import folderIconMixin from '@/vue/mixins/file-chooser/folder-icon.js';
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'file-chooser-tree',
    mixins: [folderIconMixin],
    props: {
        folder: Object,
    },
    data() {
        return {
            unfold: false,
        };
    },
    computed: {
        ...mapGetters({
            activeFolderId: 'file-chooser/activeFolderId',
        }),
        isSelected() {
            return this.folder.id === this.activeFolderId;
        },
        hasSubfolders() {
            const counter = this.folder.relationships?.folders?.meta?.count ?? 0;

            return counter > 0;
        },
    },
    methods: {
        ...mapActions({
            setActiveFolderId: 'file-chooser/setActiveFolderId',
            setSelectedFolderId: 'file-chooser/setSelectedFolderId',
        }),
        selectFolder() {
            this.setActiveFolderId(this.folder.id);
            this.setSelectedFolderId('');
            this.unfold = true;
        },
        toggleSubfolders() {
            this.unfold = !this.unfold;
        },
    },
};
</script>

<style lang="scss">
.file-chooser-tree {
    padding-left: 18px;
    &.file-chooser-tree-first-level {
        padding-left: 0;
    }
}
.file-chooser-tree-item {
    list-style: none;
    padding: 2px 0 0 0;
    .folder-toggle {
        width: 16px;
    }
    a.selected {
        font-weight: 700;
    }
    img {
        vertical-align: middle;
    }
    span {
        width: calc(100% - 46px);
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        height: 16px;
        white-space: nowrap;
        vertical-align: sub;
    }
}
</style>
