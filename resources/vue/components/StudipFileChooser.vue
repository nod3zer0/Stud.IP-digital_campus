<template>
    <div class="file-chooser">
        <button class="button" @click="openDialog">{{ buttonTitle }}</button><span>{{ selectedName }}</span>
        <file-chooser-dialog v-if="showDialog" v-bind="$props" @close="closeDialog" @selected="select" />
    </div>
</template>

<script>
import FileChooserDialog from './file-chooser/FileChooserDialog.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'studip-file-chooser',
    components: {
        FileChooserDialog,
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
    },
    model: {
        prop: 'selectedId',
        event: 'select',
    },
    data() {
        return {
            showDialog: false,
            selectedFile: null,
            selectedFolder: null,
        };
    },
    computed: {
        ...mapGetters({
            fileById: 'file-refs/byId',
            folderById: 'folders/byId',
        }),
        buttonTitle() {
            if (this.selectable === 'folder') {
                return this.$gettext('Ordner auswählen');
            }

            return this.$gettext('Datei auswählen');
        },
        selectedName() {
            if (this.selectable === 'folder') {
                if (this.selectedId === '') {
                    return this.$gettext('Kein Ordner ausgewählt');
                }
                return this.$gettextInterpolate(this.$gettext('Ordner "%{folderName}" ausgewählt'), {
                    folderName: this.folderById({ id: this.selectedId })?.attributes?.name ?? '-',
                });
            }

            if (this.selectedId === '') {
                return this.$gettext('Keine Datei ausgewählt');
            }
            return this.$gettextInterpolate(this.$gettext('Datei "%{fileName}" ausgewählt'), {
                fileName: this.fileById({ id: this.selectedId })?.attributes?.name ?? '-',
            });
        },
    },
    methods: {
        ...mapActions({
            loadFile: 'file-refs/loadById',
            loadFolder: 'folders/loadById',
        }),
        openDialog() {
            this.showDialog = true;
        },
        closeDialog() {
            this.showDialog = false;
        },
        select(id) {
            this.closeDialog();
            this.$emit('select', id);
        },
        loadSelection() {
            if (this.selectable === 'folder') {
                if (this.selectedId !== '') {
                    this.loadFolder({ id: this.selectedId });
                }
            } else {
                if (this.selectedId !== '') {
                    this.loadFile({ id: this.selectedId });
                }
            }
        }
    },
    mounted() {
        this.loadSelection();
    },
};
</script>

<style lang="scss" scoped>
.file-chooser {
    text-indent: 0;
    max-width: 48em;
    button {
        margin: 0.5ex 0 0.5ex 0;
        min-width: 140px;
    }
    span {
        box-sizing: border-box;
        border: solid thin var(--content-color-40);
        border-left: none;
        display: inline-block;
        font-size: 14px;
        line-height: 130%;
        min-width: 100px;
        width: calc(100% - 140px);
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 5px 15px;
        vertical-align: middle;
        white-space: nowrap;
    }
}
</style>
