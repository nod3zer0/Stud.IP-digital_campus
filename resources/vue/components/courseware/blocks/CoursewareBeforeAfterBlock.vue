<template>
    <div class="cw-block cw-block-before-after">
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
                <TwentyTwenty v-if="!isEmpty" :before="currentBeforeUrl" :after="currentAfterUrl" />
                <courseware-companion-box
                    v-if="isEmpty && canEdit"
                    :msgCompanion="$gettext('Bitte wählen Sie ein Vorher- und ein Nachher-Bild aus.')"
                    mood="pointing"
                />
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab :index="0" :name="$gettext('Vorher')" :selected="true">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Quelle') }}
                                <select v-model="currentBeforeSource">
                                    <option value="studip">{{ $gettext('Dateibereich') }}</option>
                                    <option value="web">{{ $gettext('Web-Adresse') }}</option>
                                </select>
                            </label>
                            <label v-if="currentBeforeSource === 'web'">
                                {{ $gettext('URL') }}
                                <input type="text" v-model="currentBeforeWebUrl" />
                            </label>
                            <label v-if="currentBeforeSource === 'studip'">
                                {{ $gettext('Bilddatei') }}
                                <studip-file-chooser v-model="currentBeforeFileId" selectable="file" :courseId="context.id" :userId="userId" :isImage="true" :excludedCourseFolderTypes="excludedCourseFolderTypes"/>
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="1" :name="$gettext('Nachher')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Quelle') }}
                                <select v-model="currentAfterSource">
                                    <option value="studip">{{ $gettext('Dateibereich') }}</option>
                                    <option value="web">{{ $gettext('Web-Adresse') }}</option>
                                </select>
                            </label>
                            <label v-if="currentAfterSource === 'web'">
                                {{ $gettext('URL') }}
                                <input type="text" v-model="currentAfterWebUrl" />
                            </label>
                            <label v-if="currentAfterSource === 'studip'">
                                {{ $gettext('Bilddatei') }}
                                <studip-file-chooser v-model="currentAfterFileId" selectable="file" :courseId="context.id" :userId="userId" :isImage="true"/>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>{{ $gettext('Informationen zum Bildvergleich-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import TwentyTwenty from 'vue-twentytwenty';
import 'vue-twentytwenty/dist/vue-twentytwenty.css';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-before-after-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {
        TwentyTwenty,
    }),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentBeforeSource: '',
            currentBeforeFileId: '',
            currentBeforeFile: {},
            currentBeforeWebUrl: '',
            currentAfterSource: '',
            currentAfterFileId: '',
            currentAfterFile: {},
            currentAfterWebUrl: '',
            afterFile: null,
            beforeFile: null,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
        }),
        beforeSource() {
            return this.block?.attributes?.payload?.before_source;
        },
        beforeFileId() {
            return this.block?.attributes?.payload?.before_file_id;
        },
        beforeWebUrl() {
            return this.block?.attributes?.payload?.before_web_url;
        },
        afterSource() {
            return this.block?.attributes?.payload?.after_source;
        },
        afterFileId() {
            return this.block?.attributes?.payload?.after_file_id;
        },
        afterWebUrl() {
            return this.block?.attributes?.payload?.after_web_url;
        },
        currentBeforeUrl() {
            if (this.currentBeforeSource === 'studip' && this.currentBeforeFile?.meta) {
                return this.currentBeforeFile.meta['download-url'];
            } else if (this.currentBeforeSource === 'web') {
                return this.currentBeforeWebUrl;
            } else {
                return '';
            }
        },
        currentAfterUrl() {
            if (this.currentAfterSource === 'studip' && this.currentAfterFile?.meta) {
                return this.currentAfterFile.meta['download-url'];
            } else if (this.currentAfterSource === 'web') {
                return this.currentAfterWebUrl;
            } else {
                return '';
            }
        },
        isEmpty() {
            return this.currentBeforeUrl === '' || this.currentAfterUrl === '';
        },
    },
    mounted() {
        if (this.block.id) {
            this.loadFileRefs(this.block.id).then((response) => {
                for (let i = 0; i < response.length; i++) {
                    if (response[i].id === this.beforeFileId) {
                        this.beforeFile = response[i];
                    }

                    if (response[i].id === this.afterFileId) {
                        this.afterFile = response[i];
                    }
                }

                this.currentBeforeFile = this.beforeFile;
                this.currentAfterFile  = this.afterFile;
            });

            this.loadImages();
        }
        
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRefs: 'loadFileRefs',
            companionWarning: 'companionWarning',
        }),
        loadImages() {
            this.loadFileRefs(this.block.id).then((response) => {
                for (let i = 0; i < response.length; i++) {
                    if (response[i].id === this.beforeFileId) {
                        this.beforeFile = response[i];
                    }

                    if (response[i].id === this.afterFileId) {
                        this.afterFile = response[i];
                    }
                }

                this.currentBeforeFile = this.beforeFile;
                this.currentAfterFile = this.afterFile;
            });
        },

        initCurrentData() {
            this.currentBeforeSource = this.beforeSource;
            this.currentBeforeFileId = this.beforeFileId;
            this.currentBeforeWebUrl = this.beforeWebUrl;
            this.currentAfterSource = this.afterSource;
            this.currentAfterFileId = this.afterFileId;
            this.currentAfterWebUrl = this.afterWebUrl;
        },
        storeBlock() {
            let cmpInfo = false;
            let cmpInfoBefore = this.$gettext('Bitte wählen Sie ein Vorherbild aus.');
            let cmpInfoAfter = this.$gettext('Bitte wählen Sie ein Nachherbild aus.');
            let attributes = {};
            attributes.payload = {};
            attributes.payload.before_source = this.currentBeforeSource;
            attributes.payload.after_source = this.currentAfterSource;
            if (this.currentAfterSource === 'studip') {
                if (this.currentAfterFile === null) {
                    cmpInfo = cmpInfoAfter;
                } else {
                    attributes.payload.after_file_id = this.currentAfterFile.id;
                    attributes.payload.after_web_url = '';
                }
            } else if (this.currentAfterSource === 'web') {
                if (this.currentAfterWebUrl === '') {
                    cmpInfo = cmpInfoAfter;
                } else {
                    attributes.payload.after_file_id = '';
                    attributes.payload.after_web_url = this.currentAfterWebUrl;
                }
            } else {
                cmpInfo = cmpInfoAfter;
            }
            if (this.currentBeforeSource === 'studip') {
                if (this.currentBeforeFile === null) {
                    cmpInfo = cmpInfoBefore;
                } else {
                    attributes.payload.before_file_id = this.currentBeforeFile.id;
                    attributes.payload.before_web_url = '';
                }
            } else if (this.currentBeforeSource === 'web') {
                if (this.currentBeforeWebUrl === '') {
                    cmpInfo = cmpInfoBefore;
                } else {
                    attributes.payload.before_file_id = '';
                    attributes.payload.before_web_url = this.currentBeforeWebUrl;
                }
            } else {
                cmpInfo = cmpInfoBefore;
            }

            if (cmpInfo) {
                this.companionWarning({
                    info: cmpInfo,
                });
                return false;
            } else {
                this.updateBlock({
                    attributes: attributes,
                    blockId: this.block.id,
                    containerId: this.block.relationships.container.data.id,
                });
            }
        },
    },
    watch: {
        currentBeforeFileId(newId) {
            if (newId) {
                this.currentBeforeFile = this.fileRefById({ id: newId });
            }
        },
        currentAfterFileId(newId) {
            if (newId) {
                this.currentAfterFile = this.fileRefById({ id: newId });
            }
        }
    }
};
</script>
<style scoped lang="scss">
.cw-block-before-after {
    .twentytwenty-container {
        width: 100% !important;
        z-index: 19;
        .twentytwenty-handle {
            z-index: 18;
        }
        .twentytwenty-overlay {
            z-index: 17;
        }
        img {
            width: 100%;
            z-index: 16;
        }
    }
}
</style>
