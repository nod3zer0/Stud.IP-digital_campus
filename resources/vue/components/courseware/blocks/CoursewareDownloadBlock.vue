<template>
    <div class="cw-block cw-block-download">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            :defaultGrade="false"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <div v-if="currentFile !== null" class="cw-block-download-content">
                    <div v-if="currentInfo !== '' && !userHasDownloaded" class="messagebox messagebox_info">
                        {{ currentInfo }}
                    </div>
                    <div v-if="currentSuccess !== '' && userHasDownloaded" class="messagebox messagebox_info">
                        {{ currentSuccess }}
                    </div>
                    <div class="cw-block-download-file-item">
                        <a
                            target="_blank"
                            :download="currentFile.name"
                            :title="$gettext('Datei herunterladen')"
                            :href="currentFile.download_url"
                            @click="handleDownload"
                        >
                            <span class="cw-block-file-info" :class="['cw-block-file-icon-' + currentFile.icon]">
                                {{ currentFile.name }}
                            </span>
                        </a>
                    </div>
                </div>
                <div v-else class="cw-block-download-content">
                    <div class="cw-block-download-file-item-not-available">
                        <span class="cw-block-file-info cw-block-file-icon-none">
                            {{ $gettext('Datei ist nicht verfügbar') }}
                        </span>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab :index="0" :name="$gettext('Datei')" :selected="true">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Überschrift') }}
                                <input type="text" v-model="currentTitle" />
                            </label>
                            <label>
                                {{ $gettext('Datei') }}
                                <studip-file-chooser v-model="currentFileId" selectable="file" :courseId="context.id" :userId="userId" :excludedCourseFolderTypes="excludedCourseFolderTypes" />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="1" :name="$gettext('Infobox')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Infobox vor Download') }}
                                <input type="text" v-model="currentInfo" />
                            </label>
                            <label>
                                {{ $gettext('Infobox nach Download') }}
                                <input type="text" v-model="currentSuccess" />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="2" :name="$gettext('Fortschritt')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Fortschritt erst beim Herunterladen') }}
                                <select v-model="currentGrade">
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Download-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-download-block',
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
            currentInfo: '',
            currentSuccess: '',
            currentGrade: '',
            currentFileId: '',
            currentFile: null,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
            relatedTermOfUse: 'terms-of-use/related',
            getUserDataById: 'courseware-user-data-fields/byId',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        info() {
            return this.block?.attributes?.payload?.info;
        },
        success() {
            return this.block?.attributes?.payload?.success;
        },
        grade() {
            return this.block?.attributes?.payload?.grade;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        userData() {
            return this.getUserDataById({ id: this.block.relationships['user-data-field'].data.id });
        },
        userHasDownloaded() {
            let downloaded = this.userData?.attributes?.payload?.downloaded;

            if (downloaded === undefined) {
                return false;
            }

            return downloaded;
        },
    },
    mounted() {
        this.initCurrentData();
        if (this.userProgress && this.userProgress.attributes.grade === 0 && !this.grade) {
            this.userProgress = 1;
        }
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            updateBlock: 'updateBlockInContainer',
            updateUserDataFields: 'courseware-user-data-fields/update',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentInfo = this.info;
            this.currentFileId = this.fileId;
            this.currentSuccess = this.success;
            this.currentGrade = this.grade;
            if (this.currentFileId !== '') {
                this.loadFile();
            }
        },
        async loadFile() {
            const id = `${this.currentFileId}`;
            const options = { include: 'terms-of-use' };
            await this.loadFileRef({ id: id, options });
            const fileRef = this.fileRefById({ id: id });
            if (
                fileRef &&
                this.relatedTermOfUse({ parent: fileRef, relationship: 'terms-of-use' }).attributes[
                    'download-condition'
                ] === 0
            ) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
                    icon: this.getIcon(fileRef.attributes['mime-type']),
                    download_url: this.urlHelper.getURL(
                        'sendfile.php',
                        { type: 0, file_id: fileRef.id, file_name: fileRef.attributes.name },
                        true
                    ),
                });
            }
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
            if (!this.currentFile.icon) {
                this.currentFile.icon = this.getIcon(file.mime_type);
            }
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
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.info = this.currentInfo;
            attributes.payload.success = this.currentSuccess;
            attributes.payload.grade = this.currentGrade;
            attributes.payload.file_id = this.currentFileId;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        handleDownload() {
            let data = {
                id: this.userData.id,
                type: 'courseware-user-data-fields',
                relationships: {
                    block: {
                        data: {
                            id: this.block.id,
                            type: this.block.type,
                        },
                    },
                },
                attributes: {
                    payload: {
                        downloaded: true,
                    },
                },
            };
            this.updateUserDataFields(data);
            this.userProgress = 1;
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/files.scss';
</style>
