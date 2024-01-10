<template>
    <div class="cw-block cw-block-folder">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <div v-if="isHomework" class="cw-block-folder-info">
                    <p>
                        {{
                            $gettext(
                                'Dieser Ordner ist ein Hausaufgabenordner. Es können nur Dateien eingestellt werden.'
                            )
                        }}
                    </p>
                    <p v-if="!isTeacher">
                        {{ $gettext('Sie selbst haben folgende Dateien in diesen Ordner eingestellt') }}:
                    </p>
                </div>
                <ul class="cw-block-folder-list">
                    <li v-for="file in files" :key="file.id" class="cw-block-folder-file-item">
                        <a
                            v-if="downloadEnabled"
                            target="_blank"
                            :download="file.attributes.name"
                            :title="$gettext('Datei herunterladen')"
                            :href="file.meta['download-url']"
                        >
                            <span
                                class="cw-block-file-info"
                                :class="['cw-block-file-icon-' + getIcon(file.attributes['mime-type'])]"
                            >
                                {{ file.attributes.name }}
                            </span>
                            <div v-if="isTeacher && isHomework" class="cw-block-file-details">
                                <span class="cw-block-file-owner">
                                    {{ file.relationships.owner.meta.name }}
                                </span>
                                <span class="cw-block-file-mkdate">
                                    {{ getFormattedDate(file.attributes.mkdate) }}
                                </span>
                            </div>
                        </a>
                        <template v-else>
                            <span
                                class="cw-block-file-info download-disabled"
                                :class="['cw-block-file-icon-' + getIcon(file.attributes['mime-type'])]"
                            >
                                {{ file.attributes.name }}
                            </span>
                            <div class="cw-block-file-details">
                                <span class="cw-block-file-mkdate">
                                    {{ getFormattedDate(file.attributes.mkdate) }}
                                </span>
                            </div>
                        </template>
                    </li>
                    <li v-if="files.length === 0">
                        <span class="cw-block-file-info cw-block-file-icon-empty">
                            {{ $gettext('Dieser Ordner ist leer') }}
                        </span>
                    </li>
                </ul>
                <div v-if="uploadEnabled" class="cw-block-folder-upload">
                    <form class="default" @submit.prevent="">
                        <label>
                            {{ $gettext('Dateien zum Hochladen auswählen') }}
                            <input class="cw-file-input" ref="uploadFile" type="file" @change="displayTermSelector" />
                            <button class="button" @click="uploadFile">
                                {{ $gettext('Datei hochladen') }}
                            </button>
                        </label>
                    </form>
                    <studip-dialog
                        v-if="showTermSelector"
                        width="780"
                        height="510"
                        :title="$gettext('Lizenz auswählen')"
                        :confirmText="$gettext('Speichern')"
                        confirmClass="accept"
                        :closeText="$gettext('Lizenzauswahl abbrechen')"
                        closeClass="cancel"
                        @close="showTermSelector = false"
                        @confirm="selectTerm"
                    >
                        <template v-slot:dialogContent>
                            <form class="default" @submit.prevent="">
                                <div style="margin-bottom: 1ex">
                                    {{
                                        $gettext(
                                            'Bereitgestellte Dateien können heruntergeladen und ggf. weiterverbreitet werden. Dabei ist das Urheberrecht sowohl beim Hochladen der Datei als auch bei der Nutzung zu beachten. Bitte geben Sie daher an, um welche Art von Bereitstellung es sich handelt. Diese Angabe dient mehreren Zwecken: Beim Herunterladen wird ein Hinweis angezeigt, welche Nutzung der Datei zulässig ist. Beim Hochladen stellt die Angabe eine Entscheidungshilfe dar, damit Sie sichergehen können, dass die Datei tatsächlich bereitgestellt werden darf.'
                                        )
                                    }}
                                </div>
                                <fieldset class="select_terms_of_use">
                                    <template v-for="term in termsOfUse">
                                        <input
                                            type="radio"
                                            name="content_terms_of_use_id"
                                            :value="term.id"
                                            v-model="selectedTerm"
                                            :id="'content_terms_of_use-' + term.id"
                                            :checked="selectedTerm === term.id"
                                            :aria-description="term.description"
                                            :key="term.id + '_input'"
                                        />
                                        <label @click="selectedTerm = term.id" :key="term.id + 'label'">
                                            <div class="icon">
                                                <studip-icon :shape="term.attributes.icon" :size="32" />
                                            </div>
                                            <div class="text">
                                                {{ term.attributes.name }}
                                            </div>
                                            <studip-icon shape="arr_1down" :size="24" class="arrow" />
                                            <studip-icon shape="check-circle" :size="24" class="check" />
                                        </label>
                                        <div class="terms_of_use_description" :key="term.id + '_description'">
                                            <div class="description">
                                                {{ term.attributes.description }}
                                            </div>
                                        </div>
                                    </template>
                                </fieldset>
                            </form>
                        </template>
                    </studip-dialog>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        {{ $gettext('Ordner') }}
                        <studip-file-chooser v-model="currentFolderId" selectable="folder" :courseId="context.id" :userId="userId" />
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Dateiordner-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-folder-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentFolderId: '',
            currentFolderType: '',
            showTermSelector: false,
            selectedTerm: null,
        };
    },
    computed: {
        ...mapGetters({
            folderById: 'folders/byId',
            termsOfUse: 'terms-of-use/all',
        }),
        folderType() {
            return this.block?.attributes?.payload?.type;
        },
        storedFolderType() {
            return this.block?.attributes?.payload?.folder_type;
        },
        folderTypeHasChanged() {
            return this.folderType === this.storedFolderType;
        },
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        files() {
            return this.block?.attributes?.payload?.files;
        },
        isHomework() {
            return this.folderType === 'HomeworkFolder';
        },
        uploadEnabled() {
            return !this.isTeacher && this.isHomework;
        },
        downloadEnabled() {
            return this.isTeacher || !this.isHomework;
        },
    },
    async mounted() {
        await this.loadTermsOfUse();
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFolder: 'folders/loadById',
            loadBlock: 'courseware-blocks/loadById',
            updateBlock: 'updateBlockInContainer',
            createFile: 'createFile',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
            loadTermsOfUse: 'terms-of-use/loadAll',
        }),
        async initCurrentData() {
            this.currentTitle = this.title;
            this.currentFolderId = this.folderId;
            if (this.$refs?.uploadFile) {
                this.$refs.uploadFile.value = null;
            }
            this.selectedTerm = this.getDefaultTerm();
        },
        async setCurrentFolderType() {
            await this.loadFolder({ id: this.currentFolderId });
            const folder = this.folderById({ id: this.currentFolderId });
            this.currentFolderType = folder?.attributes['folder-type'];
        },
        getIcon(mimeType) {
            let icon = 'file';
            if (mimeType.includes('audio')) {
                icon = 'audio';
            }
            if (mimeType.includes('image')) {
                icon = 'pic';
            }
            if (mimeType.includes('video')) {
                icon = 'video';
            }
            if (mimeType.includes('text')) {
                icon = 'text';
            }
            if (mimeType.includes('pdf')) {
                icon = 'pdf';
            }
            if (mimeType.includes('msword')) {
                icon = 'word';
            }
            if (mimeType.includes('opendocument.text')) {
                icon = 'word';
            }
            if (mimeType.includes('openxmlformats-officedocument. wordprocessingml.document')) {
                icon = 'word';
            }
            if (mimeType.includes('msexcel')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('opendocument.spreadsheet')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('openxmlformats-officedocument. spreadsheetml.sheet')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('mspowerpoint')) {
                icon = 'ppt';
            }
            if (mimeType.includes('zip')) {
                icon = 'archive';
            }

            return icon;
        },
        getFormattedDate(unformattedDate) {
            const date = new Date(unformattedDate);
            const localeDate = date.toLocaleDateString('de-DE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            });

            return `${localeDate} ${date.getHours()}:${(date.getMinutes() < 10 ? '0' : '') + date.getMinutes()}:${
                (date.getSeconds() < 10 ? '0' : '') + date.getSeconds()
            }`;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.folder_id = this.currentFolderId;
            attributes.payload.type = this.currentFolderType;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        displayTermSelector() {
            this.showTermSelector = true;
        },
        selectTerm() {
            this.showTermSelector = false;
            this.uploadFile();
        },
        async uploadFile() {
            const userFile = this.$refs?.uploadFile?.files[0];
            if (!userFile) {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Datei aus.'),
                });
                return;
            }

            let file = {
                attributes: {
                    name: userFile.name.replace(/\s/g, '_'),
                },
                relationships: {
                    'terms-of-use': {
                        data: {
                            id: this.selectedTerm,
                        },
                    },
                },
            };
            let fileObj = await this.createFile({
                file: file,
                filedata: userFile,
                folder: { id: this.currentFolderId },
            });
            if (fileObj && fileObj.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Die Datei wurde erfolgreich im Dateibereich abgelegt.'),
                });
            } else {
                if (this.folderType !== 'HomeworkFolder') {
                    this.companionError({
                        info: this.$gettext('Es ist ein Fehler aufgetretten.'),
                    });
                }
            }
            this.reload();
        },
        async reload() {
            await this.loadBlock({ id: this.block.id });
            this.initCurrentData();
        },
        getDefaultTerm() {
            const defaultTerm = this.termsOfUse.filter((term) => term.attributes['is-default'])[0];
            if (defaultTerm) {
                return defaultTerm.id;
            }
            return null;
        },
    },
    watch: {
        currentFolderId() {
            if (this.canEdit) {
                this.setCurrentFolderType();
            }
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/files.scss';
</style>
