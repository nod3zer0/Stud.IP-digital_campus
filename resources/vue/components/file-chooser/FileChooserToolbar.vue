<template>
    <div class="file-chooser-toolbar">
        <button v-if="showButtons" class="button" :disabled="!canAddFolder" @click="addFolder">
            {{ $gettext('Ordner hinzufügen') }}
        </button>
        <form v-if="showFolderAdder" class="inline-form" @submit.prevent="">
            <label for="file-chooser-add-folder">{{ $gettext('Ordner hinzufügen') }}:</label>
            <input
                id="file-chooser-add-folder"
                type="text"
                v-model="newFolderName"
                :placeholder="$gettext('Ordnername')"
            />
            <div class="inline-buttons">
                <button :title="$gettext('Ordner anlegen')" @click="createFolder">
                    <studip-icon shape="accept" />
                </button>
                <button :title="$gettext('Abbrechen')" @click="closeAddFolder"><studip-icon shape="decline" /></button>
            </div>
        </form>
        <button v-if="showButtons && !isFolderChooser" class="button" @click="$refs.fileInput.click()">
            {{ $gettext('Datei hinzufügen') }}
        </button>
        <input v-show="false" type="file" ref="fileInput" :disabled="!canAddFile" @change="updateUpload" />
        <form v-if="showUpload" class="inline-form" @submit.prevent="">
            <label for="file-chooser-add-file">{{ $gettext('Datei hinzufügen') }}:</label>
            <input
                id="file-chooser-add-file"
                :title="$gettext('Datei auswählen')"
                type="text"
                :value="uploadFileName"
                readonly
                @click="$refs.fileInput.click()"
            />
            <div class="inline-buttons">
                <button :title="$gettext('Datei hochladen')" @click="createFile"><studip-icon shape="accept" /></button>
                <button :title="$gettext('Abbrechen')" @click="closeAddFile"><studip-icon shape="decline" /></button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'file-chooser-toolbar',
    data() {
        return {
            showFolderAdder: false,
            newFolderName: '',
            showUpload: false,
            uploadFile: null,
        };
    },
    computed: {
        ...mapGetters({
            activeFolderId: 'file-chooser/activeFolderId',
            activeFolder: 'file-chooser/activeFolder',
            activeFolderRangeType: 'file-chooser/activeFolderRangeType',
            courseId: 'file-chooser/courseId',
            isFolderChooser: 'file-chooser/isFolderChooser',
            userId: 'file-chooser/userId',
        }),
        showButtons() {
            return !this.showUpload && !this.showFolderAdder;
        },
        canAddFolder() {
            if (this.activeFolder) {
                return (
                    this.activeFolder.attributes['is-writable'] && this.activeFolder.attributes['is-subfolder-allowed']
                );
            }
            return false;
        },
        canAddFile() {
            if (this.activeFolder) {
                return this.activeFolder.attributes['is-writable'];
            }
            return false;
        },
        uploadFileName() {
            return this.uploadFile.name;
        },
    },
    methods: {
        ...mapActions({
            loadRangeFolders: 'file-chooser/loadRangeFolders',
            loadFolderFiles: 'file-chooser/loadFolderFiles',
        }),
        addFolder() {
            this.showFolderAdder = true;
        },
        closeAddFolder() {
            this.showFolderAdder = false;
            this.newFolderName = '';
        },
        async createFolder() {
            if (this.newFolderName === '') {
                this.closeAddFolder();
            }
            this.showFolderAdder = false;
            const httpClient = await this.getHttpClient();
            const newFolder = {
                data: {
                    type: 'folders',
                    attributes: {
                        name: this.newFolderName,
                        'folder-type': 'StandardFolder',
                    },
                    relationships: {
                        parent: {
                            data: {
                                id: this.activeFolderId,
                                type: 'folders',
                            },
                        },
                    },
                },
            };
            const context = {
                type: this.activeFolderRangeType,
                id: this.activeFolderRangeType === 'users' ? this.userId : this.courseId,
            };
            await httpClient.post(`${context.type}/${context.id}/folders`, newFolder);
            this.$emit('folderAdded');
            this.newFolderName = '';
            this.loadRangeFolders({ rangeType: context.type, rangeId: context.id });
        },
        getHttpClient() {
            return axios.create({
                baseURL: STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
                headers: {
                    'Content-Type': 'application/vnd.api+json',
                },
            });
        },
        updateUpload() {
            this.showUpload = true;
            this.uploadFile = this.$refs.fileInput.files[0];
        },
        closeAddFile() {
            this.showUpload = false;
            this.$refs.fileInput.value = null;
        },
        async createFile() {
            this.showUpload = false;
            const httpClient = await this.getHttpClient();
            const formData = new FormData();
            formData.append('file', this.uploadFile, this.uploadFileName);
            const url = `folders/${this.activeFolderId}/file-refs`;
            let request = await httpClient.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            let response = null;
            try {
                response = await httpClient.get(request.headers.location);
            } catch (e) {
                console.debug(e);
                response = null;
            }

            await this.loadFolderFiles({ folderId: this.activeFolderId });
            this.$emit('fileAdded');
            this.$refs.fileInput.value = null;
        },
    },
    watch: {
        activeFolderId(newId) {
            this.closeAddFolder();
            this.closeAddFile();
        },
    },
};
</script>

<style lang="scss">
.file-chooser-toolbar {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    border-top: solid thin var(--content-color-40);

    &.with-table {
        border: none;
        margin-top: -16px;
    }

    .inline-form {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: start;
        gap: 4px;
        width: 100%;
        margin: 0.8em 0.6em 0.8em 0;

        label {
            line-height: 30px;
        }

        input {
            flex-grow: 1;
            padding: 4px;
            border: solid thin var(--content-color-40);
            border-radius: 0;
        }
        button {
            border: solid thin var(--base-color);
            background-color: transparent;
            height: 30px;
            width: 30px;
            cursor: pointer;

            img {
                vertical-align: middle;
            }
        }
    }
}
</style>
