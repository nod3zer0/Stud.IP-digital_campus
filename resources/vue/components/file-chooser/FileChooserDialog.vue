<template>
    <studip-dialog
        :title="dialogTitle"
        :confirmText="$gettext('Auswählen')"
        confirmClass="accept"
        :closeText="$gettext('Abbrechen')"
        closeClass="cancel"
        @close="$emit('close')"
        @confirm="selectId"
        :confirmDisabled="!hasSelection"
        :height="height"
        :width="width"
    >
        <template v-slot:dialogContent>
            <div class="file-chooser-content">
                <ul class="file-chooser-folder-selector">
                    <li v-if="courseId && allowCourseFolders" class="file-chooser-tree-item">
                        <a
                            href="#"
                            @click.prevent="setFolder('courses')"
                            :class="{ selected: coursesRootFolderSelected }"
                        >
                            <studip-icon shape="seminar" />
                            <span>{{ $gettext('Diese Veranstaltung') }}</span>
                        </a>
                        <ul class="file-chooser-tree file-chooser-tree-first-level">
                            <file-chooser-tree v-for="child in coursesTree.children" :key="child.id" :folder="child" />
                        </ul>
                    </li>
                    <li v-if="userId && allowUserFolders" class="file-chooser-tree-item">
                        <a href="#" @click.prevent="setFolder('users')" :class="{ selected: usersRootFolderSelected }">
                            <studip-icon shape="content" />
                            <span>{{ $gettext('Arbeitsplatz') }}</span>
                        </a>
                        <ul class="file-chooser-tree file-chooser-tree-first-level">
                            <file-chooser-tree v-for="child in usersTree.children" :key="child.id" :folder="child" />
                        </ul>
                    </li>
                </ul>
                <file-chooser-box
                    :excludedFolderTypes="[...excludedCourseFolderTypes, ...excludedUserFolderTypes]"
                    @selectId="selectId"
                />
            </div>
        </template>
    </studip-dialog>
</template>
<script>
import FileChooserBox from './FileChooserBox.vue';
import FileChooserTree from './FileChooserTree.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'file-chooser-dialog',
    components: {
        FileChooserBox,
        FileChooserTree,
    },
    props: {
        selectable: {
            type: String,
            default: 'file',
            validator: (value) => {
                return ['file', 'folder'].includes(value);
            },
        },
        selectedId: {
            type: String,
            required: false,
        },
        courseId: {
            type: String,
            validator: (value) => {
                return value !== '';
            },
            required: false,
        },
        userId: {
            type: String,
            validator: (value) => {
                return value !== '';
            },
            required: false,
        },
        isImage: { type: Boolean, default: false },
        isVideo: { type: Boolean, default: false },
        isAudio: { type: Boolean, default: false },
        isDocument: { type: Boolean, default: false },
        excludedCourseFolderTypes: { type: Array, default: () => [] },
        excludedUserFolderTypes: { type: Array, default: () => [] },
        allowUserFolders: { type: Boolean, default: true },
        allowCourseFolders: { type: Boolean, default: true },
    },
    data() {
        return {
            height: 600,
            width: 1000,
            scope: 'courses',
            coursesTree: [],
            usersTree: [],
        };
    },
    computed: {
        ...mapGetters({
            activeFolderId: 'file-chooser/activeFolderId',
            relatedUsersFolders: 'file-chooser/relatedUsersFolders',
            relatedCoursesFolders: 'file-chooser/relatedCoursesFolders',
            isFolderChooser: 'file-chooser/isFolderChooser',
            selectedFileId: 'file-chooser/selectedFileId',
            selectedFolderId: 'file-chooser/selectedFolderId',
            fileById: 'file-refs/byId',
            folderById: 'folders/byId',
        }),
        dialogTitle() {
            if (this.isFolderChooser) {
                return this.$gettext('Ordner auswählen');
            }

            return this.$gettext('Datei auswählen');
        },
        showCourse() {
            return this.scope === 'courses';
        },
        showUser() {
            return this.scope === 'users';
        },
        hasSelection() {
            if (this.isFolderChooser) {
                return this.selectedFolderId !== '';
            }

            return this.selectedFileId !== '';
        },
        coursesRootFolder() {
            return this.getRootFolder(this.relatedCoursesFolders);
        },
        usersRootFolder() {
            return this.getRootFolder(this.relatedUsersFolders);
        },
        coursesRootFolderSelected() {
            return this.coursesRootFolder?.id === this.activeFolderId;
        },
        usersRootFolderSelected() {
            return this.usersRootFolder?.id === this.activeFolderId;
        },
    },
    methods: {
        ...mapActions({
            setCourseId: 'file-chooser/setCourseId',
            setUserId: 'file-chooser/setUserId',
            setSelectable: 'file-chooser/setSelectable',
            setIsAudio: 'file-chooser/setIsAudio',
            setIsDocument: 'file-chooser/setIsDocument',
            setIsImage: 'file-chooser/setIsImage',
            setIsVideo: 'file-chooser/setIsVideo',

            setSelectedFileId: 'file-chooser/setSelectedFileId',
            setSelectedFolderId: 'file-chooser/setSelectedFolderId',
            setActiveFolderId: 'file-chooser/setActiveFolderId',
            loadRangeFolders: 'file-chooser/loadRangeFolders',
        }),
        selectId() {
            if (this.isFolderChooser) {
                this.$emit('selected', this.selectedFolderId);
            } else {
                this.$emit('selected', this.selectedFileId);
            }
        },
        setDimensions() {
            this.height = (window.innerHeight * 0.8).toFixed(0);
            this.width = Math.min((window.innerWidth * 0.9).toFixed(0), 1200).toFixed(0);
        },
        setFolder(range) {
            if (range === 'courses') {
                this.setActiveFolderId(this.coursesRootFolder.id);
            }
            if (range === 'users') {
                this.setActiveFolderId(this.usersRootFolder.id);
            }
        },
        getRootFolder(folders) {
            if (folders?.length > 0) {
                return folders.filter((folder) => {
                    return folder.attributes['folder-type'] === 'RootFolder';
                })[0];
            }
            return null;
        },
        getFolderTree(rootFolder, folders, excludedFolderTypes) {
            if (rootFolder) {
                rootFolder.children = this.getSubfolders(rootFolder, folders, excludedFolderTypes);

                return rootFolder;
            }

            return [];
        },
        getSubfolders(parent, folders, excludedFolderTypes) {
            const children = folders.filter((folder) => {
                return (
                    folder.relationships?.parent?.data?.id === parent.id &&
                    !excludedFolderTypes.includes(folder.attributes['folder-type'])
                );
            });
            children.forEach((child) => {
                child.children = this.getSubfolders(child, folders, excludedFolderTypes);
            });

            return children;
        },
    },
    async mounted() {
        this.setDimensions();

        this.setSelectable(this.selectable);
        this.setCourseId(this.courseId);
        this.setUserId(this.userId);
        if (this.selectable === 'file') {
            this.setIsAudio(this.isAudio);
            this.setIsDocument(this.isDocument);
            this.setIsImage(this.isImage);
            this.setIsVideo(this.isVideo);
        }

        if (this.userId && this.allowUserFolders) {
            await this.loadRangeFolders({ rangeType: 'users', rangeId: this.userId });
            const excludedFolderTypes = ['InboxFolder', 'OutboxFolder'];
            this.usersTree = this.getFolderTree(this.usersRootFolder, this.relatedUsersFolders, [
                ...excludedFolderTypes,
                ...this.excludedUserFolderTypes,
            ]);
            if (!this.courseId && !this.selectedId) {
                this.setActiveFolderId(this.usersRootFolder.id);
            }
        }

        if (this.courseId && this.allowCourseFolders) {
            await this.loadRangeFolders({ rangeType: 'courses', rangeId: this.courseId });
            this.coursesTree = this.getFolderTree(
                this.coursesRootFolder,
                this.relatedCoursesFolders,
                this.excludedCourseFolderTypes
            );
            if (!this.selectedId) {
                this.setActiveFolderId(this.coursesRootFolder.id);
            }
        }

        if (this.selectedId) {
            if (this.isFolderChooser) {
                const folder = this.folderById({ id: this.selectedId });
                this.setActiveFolderId(folder.relationships.parent.data.id);
                this.$nextTick(() => {
                    this.setSelectedFolderId(this.selectedId);
                });
            } else {
                const file = this.fileById({ id: this.selectedId });
                this.setActiveFolderId(file.relationships.parent.data.id);
                this.setSelectedFileId(file.id);
            }
        }
    },
};
</script>

<style lang="scss" scoped>
.file-chooser-content {
    display: flex;
    flex-direction: row;
    height: 100%;
    .file-chooser-folder-selector {
        min-width: 270px;
        max-width: 270px;
        list-style: none;
        margin: 0 1em 0 0;
        padding: 0 1em 0 0;
        border-right: solid thin var(--content-color-40);
        overflow-y: auto;
    }
}


@media (max-width: 580px) {
    .file-chooser-content .file-chooser-folder-selector {
        display: none;
    }
}
@media (max-width: 768px) {
    .file-chooser-content .file-chooser-folder-selector {
        min-width: 130px;
        max-width: 130px;
    }
}

</style>
