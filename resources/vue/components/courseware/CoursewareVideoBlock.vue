<template>
    <div class="cw-block cw-block-video">
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
                <div v-if="currentTitle !== '' && currentURL" class="cw-block-title">{{ currentTitle }}</div>
                <video
                    v-show="currentURL"
                    :src="currentURL"
                    :type="currentFile !== '' ? currentFile.mime_type : ''"
                    controls
                    :autoplay="currentAutoplay === 'enabled'"
                    @contextmenu="contextHandler"
                />
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab
                        :index="0"
                        :name="$gettext('Grunddaten')"
                        :selected="true"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Überschrift') }}
                                <input type="text" v-model="currentTitle" />
                            </label>
                            <label>
                                {{ $gettext('Quelle') }}
                                <select v-model="currentSource">
                                    <option value="studip">{{ $gettext('Dateibereich') }}</option>
                                    <option value="web">{{ $gettext('Web-Adresse') }}</option>
                                </select>
                            </label>
                            <label v-show="currentSource === 'web'">
                                {{ $gettext('URL') }}
                                <input type="text" v-model="currentWebUrl" />
                            </label>
                            <label v-show="currentSource === 'studip'">
                                {{ $gettext('Datei') }}
                                <courseware-file-chooser
                                    v-model="currentFileId"
                                    :isVideo="true"
                                    @selectFile="updateCurrentFile"
                                />
                            </label>

                        </form>
                    </courseware-tab>
                    <courseware-tab
                        :index="1"
                        :name="$gettext('Video Einstellungen')"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Seitenverhältnis') }}
                                <select v-model="currentAspect">
                                    <option value="169">16:9</option>
                                    <option value="43">4:3</option>
                                </select>
                            </label>
                            <label>
                                {{ $gettext('Video startet automatisch') }}
                                <select v-model="currentAutoplay">
                                    <option value="disabled">{{ $gettext('Nein') }}</option>
                                    <option value="enabled">{{ $gettext('Ja') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ $gettext('Contextmenü') }}
                                <select v-model="currentContextMenu">
                                    <option value="enabled">{{ $gettext('Erlauben') }}</option>
                                    <option value="disabled">{{ $gettext('Verhindern') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>{{ $gettext('Informationen zum Video-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import { blockMixin } from './block-mixin.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-video-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareTabs,
        CoursewareTab,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentSource: '',
            currentTitle: '',
            currentFile: {},
            currentFileId: '',
            currentAspect: '',
            currentContextMenu: '',
            currentAutoplay: '',
            currentWebUrl: '',
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        webUrl() {
            return this.block?.attributes?.payload?.web_url;
        },
        aspect() {
            return this.block?.attributes?.payload?.aspect;
        },
        contextMenu() {
            return this.block?.attributes?.payload?.context_menu;
        },
        autoplay() {
            return this.block?.attributes?.payload?.autoplay;
        },
        currentURL() {
            if (this.currentSource === 'studip' && this.currentFile) {
                return this.currentFile.download_url;
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }
            return false;
        },

    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRef: 'file-refs/loadById',
            companionWarning: 'companionWarning',
        }),
        storeBlock() {
            let cmpInfo = false;
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.source = this.currentSource;
            if (this.currentSource === 'studip' && this.currentFile !== undefined && this.currentFileId !== '') {
                attributes.payload.file_id = this.currentFile.id;
                attributes.payload.web_url = '';
            } else if (this.currentSource === 'web' && this.currentWebUrl !== '') {
                attributes.payload.file_id = '';
                attributes.payload.web_url = this.currentWebUrl;
            } else {
                cmpInfo = this.$gettext('Bitte wählen Sie ein Video aus.');
            }
            attributes.payload.aspect = this.currentAspect;
            attributes.payload.context_menu = this.currentContextMenu;
            attributes.payload.autoplay = this.currentAutoplay;

            if (cmpInfo) {
                this.companionWarning({ info: cmpInfo });
                return false;
            }

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        async initCurrentData() {
            this.currentSource = this.source;
            this.currentTitle = this.title;
            this.currentWebUrl = this.webUrl;
            this.currentFileId = this.fileId;
            this.currentAspect = this.aspect;
            this.currentContextMenu = this.contextMenu;
            this.currentAutoplay = this.autoplay;
            if (this.fileId !== '') {
                await this.loadFile();
            }
        },
        async loadFile() {
            const id = this.currentFileId;
            await this.loadFileRef({ id });
            const fileRef = this.fileRefById({ id });

            if (fileRef) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
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
        },
        contextHandler(e) {
            if (this.currentContextMenu === 'disabled') {
                e.preventDefault();
            }
        },
    },
};
</script>
