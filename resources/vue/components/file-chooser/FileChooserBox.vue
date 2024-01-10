<template>
    <div class="file-chooser-box">
        <header>
            <file-chooser-breadcrumb :folders="folders" />
            <button class="toggle-view" :title="$gettext('Rasteransicht umschalten')" @click="toggleGrid">
                <studip-icon :shape="showGrid ? 'view-list' : 'view-wall'" />
            </button>
        </header>
        <div v-if="showMessageBox" class="messagebox messagebox_success">
            <div class="messagebox_buttons">
                <a
                    href="#"
                    :title="$gettext('Nachrichtenbox schließen')"
                    class="close"
                    @click.prevent="showMessageBox = false"
                ></a>
            </div>
            {{ successMessage }}
        </div>
        <div v-if="contentForbidden" class="messagebox messagebox_error">
            {{ $gettext('Sie sind nicht berechtigt, den Inhalt dieses Ordners anzuzeigen.') }}
        </div>
        <div v-else class="file-chooser-box-content">
            <div v-if="showGrid" class="file-chooser-items">
                <template v-if="!isEmpty">
                    <file-chooser-folder-item v-for="folder in subFolders" :key="folder.id" :folder="folder" />
                    <file-chooser-file-item
                        v-for="file in currentFolderFiles"
                        :key="file.id"
                        :file="file"
                        @selectId="$emit('selectId')"
                    />
                    <studip-progress-indicator v-if="loadingFiles" :description="$gettext('Lade Dateien…')" />
                </template>
                <file-chooser-empty v-if="isEmpty" />
            </div>
            <file-chooser-table
                v-else
                :files="currentFolderFiles"
                :subfolders="subFolders"
                :empty="isEmpty"
                @selectId="$emit('selectId')"
            />
            <file-chooser-toolbar
                :class="{ 'with-table': !showGrid }"
                @fileAdded="fileAdded"
                @folderAdded="folderAdded"
            />
        </div>
    </div>
</template>

<script>
import FileChooserBreadcrumb from './FileChooserBreadcrumb.vue';
import FileChooserEmpty from './FileChooserEmpty.vue';
import FileChooserFileItem from './FileChooserFileItem.vue';
import FileChooserFolderItem from './FileChooserFolderItem.vue';
import FileChooserTable from './FileChooserTable.vue';
import FileChooserToolbar from './FileChooserToolbar.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'file-chooser-box',
    components: {
        FileChooserBreadcrumb,
        FileChooserEmpty,
        FileChooserFileItem,
        FileChooserFolderItem,
        FileChooserTable,
        FileChooserToolbar,
        StudipProgressIndicator,
    },
    props: {
        excludedFolderTypes: { type: Array, default: () => [] },
    },
    data() {
        return {
            loadingFiles: false,
            showGrid: true,
            showMessageBox: false,
            successMessage: '',
            contentForbidden: false,
        };
    },
    computed: {
        ...mapGetters({
            activeFolder: 'file-chooser/activeFolder',
            activeFolderId: 'file-chooser/activeFolderId',
            currentFolderFiles: 'file-chooser/currentFolderFiles',
            relatedUsersFolders: 'file-chooser/relatedUsersFolders',
            relatedCoursesFolders: 'file-chooser/relatedCoursesFolders',
            rangeType: 'file-chooser/activeFolderRangeType',
        }),
        rootFolder() {
            if (this.folders.length > 0) {
                return this.folders.find((folder) => {
                    return folder.attributes['folder-type'] === 'RootFolder';
                });
            }
            return null;
        },
        folders() {
            if (this.rangeType === 'courses') {
                return this.relatedCoursesFolders;
            }
            if (this.rangeType === 'users') {
                return this.relatedUsersFolders;
            }

            return [];
        },
        subFolders() {
            const excludedFolderTypes = ['InboxFolder', 'OutboxFolder'].concat(this.excludedFolderTypes);
            return this.folders.filter((folder) => {
                return (
                    folder.relationships?.parent?.data?.id === this.activeFolderId
                    && !excludedFolderTypes.includes(folder.attributes['folder-type'])
                );
            });
        },
        isEmpty() {
            return this.activeFolder?.attributes['is-empty'] ?? false;
        },
        foldersCounter() {
            return this.activeFolder?.relationships?.folders?.meta?.count ?? 0;
        },
        filesCounter() {
            return this.activeFolder?.relationships?.['file-refs']?.meta?.count ?? 0;
        },
    },
    methods: {
        ...mapActions({
            loadFolderFiles: 'file-chooser/loadFolderFiles',
        }),
        async loadFiles(folderId) {
            this.contentForbidden = false;
            if (this.filesCounter === 0) {
                return;
            }
            setTimeout(() => {
                if (loading) {
                    this.loadingFiles = true;
                }
            }, 100);
            let loading = true;
            try {
                await this.loadFolderFiles({ folderId });
            } catch(response) {
                if (response.data?.errors[0].status === '403') {
                    this.contentForbidden = true;
                }
            }
            loading = false;
            this.loadingFiles = false;
        },
        toggleGrid() {
            this.showGrid = !this.showGrid;
        },
        fileAdded() {
            this.showMessageBox = true;
            this.successMessage = this.$gettext('Es wurde eine Datei hochgeladen.');
        },
        folderAdded() {
            this.showMessageBox = true;
            this.successMessage = this.$gettext('Der Ordner wurde angelegt.');
        },
    },
    watch: {
        activeFolderId(newId) {
            this.showMessageBox = false;
            this.loadFiles(newId);
        },
    },
};
</script>
<style lang="scss">
.file-chooser-box {
    flex-grow: 1;

    header {
        display: flex;
        flex-direction: row;
        position: sticky;
        top: 0;
        background-color: var(--content-color-20);
        padding: 0.5em 1em;
        border: solid thin var(--content-color-40);
        margin-bottom: 1em;

        .file-chooser-breadcrumb {
            flex-grow: 1;
        }
        .toggle-view {
            width: 20px;
            height: 20px;
            border: none;
            background-color: transparent;
            cursor: pointer;
        }
    }
    .file-chooser-box-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow-y: scroll;
        height: calc(100% - 36px);
    }
}
.file-chooser-items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    overflow-y: auto;

    .file-chooser-item {
        display: flex;
        flex-direction: column;
        width: 104px;
        min-height: 104px;
        border: solid thin transparent;
        background-color: transparent;
        word-break: break-word;
        margin: 0 4px 4px 4px;
        padding: 4px;
        cursor: pointer;

        &.selected {
            background-color: var(--activity-color-20);
            border: solid thin var(--base-color);
            font-weight: 700;
        }
        &.disabled {
            cursor: default;
        }
        img {
            margin: 0 auto;
        }
        span {
            width: 100%;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
    }
}
</style>
