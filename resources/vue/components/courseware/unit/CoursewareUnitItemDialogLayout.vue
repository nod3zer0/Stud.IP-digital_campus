<template>
        <studip-dialog
        :title="$gettext('Darstellung')"
        :confirmText="$gettext('Speichern')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="470"
        width="870"
        @close="$emit('close')"
        @confirm="storeLayout"
    >
        <template v-slot:dialogContent>
            <div v-if="currentElement && !loadingInstance" class="cw-unit-item-dialog-layout-content">
                <form class="default cw-unit-item-dialog-layout-content-image" @submit.prevent="">
                    <label>{{ $gettext('Vorschaubild') }}</label>
                    <img
                        v-if="showPreviewImage"
                        :src="image"
                        class="cw-structural-element-image-preview"
                        :alt="$gettext('Vorschaubild')"
                    />
                    <label v-if="showPreviewImage">
                        <button class="button" @click="deleteImage">{{ $gettext('Bild löschen') }}</button>
                    </label>
                    <courseware-companion-box
                        v-if="uploadFileError"
                        :msgCompanion="uploadFileError"
                        mood="sad"
                    />
                    <label v-if="!showPreviewImage">
                        <img
                            v-if="currentFile"
                            :src="uploadImageURL"
                            class="cw-structural-element-image-preview"
                            :alt="$gettext('Vorschaubild')"
                        />
                        <div v-else class="cw-structural-element-image-preview-placeholder"></div>
                        {{ $gettext('Bild hochladen') }}
                        <input class="cw-file-input" ref="upload_image" type="file" accept="image/*" @change="checkUploadFile" />
                    </label>
                </form>
                <form class="default cw-unit-item-dialog-layout-content-settings" @submit.prevent="">
                    <label>
                        {{ $gettext('Titel') }}
                        <input type="text" v-model="currentElement.attributes.title"/>
                    </label>
                    <label>
                        {{ $gettext('Beschreibung') }}
                        <textarea
                            v-model="currentElement.attributes.payload.description"
                            class="cw-structural-element-description"
                        />
                    </label>
                    <label>
                        {{ $gettext('Farbe') }}
                        <studip-select
                            v-model="currentElement.attributes.payload.color"
                            :options="colors"
                            :reduce="(color) => color.class"
                            label="class"
                            class="cw-vs-select"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"
                                    ><studip-icon shape="arr_1down" :size="10"
                                /></span>
                            </template>
                            <template #no-options>
                                {{ $gettext('Es steht keine Auswahl zur Verfügung') }}.
                            </template>
                            <template #selected-option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                ><span>{{ name }}</span>
                            </template>
                            <template #option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                ><span>{{ name }}</span>
                            </template>
                        </studip-select>
                    </label>
                    <label>
                        {{ $gettext('Titelseite') }}
                        <select v-model="currentRootLayout">
                            <option value="default">{{ $gettext('Automatisch') }}</option>
                            <option value="toc">{{ $gettext('Automatisch mit Inhaltsverzeichnis') }}</option>
                            <option value="classic">{{ $gettext('Frei bearbeitbar') }}</option>
                            <option value="none">{{ $gettext('Keine') }}</option>
                        </select>
                    </label>
                </form>
            </div>
        </template>
    </studip-dialog>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';

import colorMixin from '@/vue/mixins/courseware/colors.js';
import { mapActions, mapGetters } from 'vuex';


export default {
    name: 'courseware-unit-item-dialog-layout',
    components: {
        CoursewareCompanionBox
    },
    props: {
        unit: Object,
        unitElement: Object
    },
    mixins: [colorMixin],
    data() {
        return {
            currentElement: null,
            deletingPreviewImage: false,
            uploadFileError: '',
            currentFile: null,
            uploadImageURL: null,
            currentRootLayout: 'default',
            loadingInstance: false,
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            instanceById: 'courseware-instances/byId',
            userId: 'userId'
        }),
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        image() {
            return this.currentElement.relationships?.image?.meta?.['download-url'] ?? null;
        },

        showPreviewImage() {
            return this.image !== null && this.deletingPreviewImage === false;
        },
        instance() {
            if (this.inCourseContext) {
                return this.instanceById({id: 'course_' + this.context.id + '_' + this.unit.id});
            } else {
                return this.instanceById({id: 'user_' + this.context.id + '_' + this.unit.id});
            }
            
        },
        inCourseContext() {
            return this.context.type === 'courses';
        }
    },
    methods: {
        ...mapActions({
            loadInstance: 'loadInstance',
            companionSuccess: 'companionSuccess',
            companionWarning: 'companionWarning',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            updateStructuralElement: 'updateStructuralElement',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
            deleteImageForStructuralElement: 'deleteImageForStructuralElement',
            storeCoursewareSettings: 'storeCoursewareSettings',
        }),
        async loadUnitInstance() {
            const context = {type: this.context.type, id: this.context.id, unit: this.unit.id};
            await this.loadInstance(context);
        },
        initData() {
            this.currentElement = _.cloneDeep(this.unitElement);
            this.currentRootLayout = this.instance.attributes['root-layout'];
        },
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine kleinere Datei.');
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
                this.currentFile = file;
                this.uploadImageURL = window.URL.createObjectURL(file);
            }
        },
        deleteImage() {
            if (!this.deletingPreviewImage) {
                this.deletingPreviewImage = true;
            }
        },
        async storeLayout() {
            this.$emit('close');
            await this.loadStructuralElement(this.currentElement.id);
            if (
                this.unitElement.relationships['edit-blocker'].data !== null
                && this.unitElement.relationships['edit-blocker'].data?.id !== this.userId
            ) {
                this.companionWarning({
                    info: this.$gettext('Ihre Änderungen konnten nicht gespeichert werden, die Daten werden bereits von einem anderen Nutzer bearbeitet.')
                });
                return false;
            } else {
                await this.lockObject({ id: this.currentElement.id, type: 'courseware-structural-elements' });
            }

            if (this.currentFile) {
                this.uploadImageForStructuralElement({
                    structuralElement: this.currentElement,
                    file: this.currentFile,
                }).catch((error) => {
                    console.error(error);
                    this.companionWarning({
                        info: this.$gettext('Beim Hochladen der Bilddatei ist ein Fehler aufgetretten.')
                    });
                });
                await this.loadStructuralElement(this.currentElement.id);
            } else if (this.deletingPreviewImage) {
                await this.deleteImageForStructuralElement(this.currentElement);
            }

            await this.updateStructuralElement({
                element: this.currentElement,
                id: this.currentElement.id,
            });
            await this.unlockObject({ id: this.currentElement.id, type: 'courseware-structural-elements' });

            if (this.instance.attributes['root-layout'] !== this.currentRootLayout) {
                let currentInstance = _.cloneDeep(this.instance);
                currentInstance.attributes['root-layout'] = this.currentRootLayout;
                this.storeCoursewareSettings({
                    instance: currentInstance,
                });
            }
        }
    },
    async mounted() {
        this.loadingInstance = true;
        await this.loadUnitInstance();
        this.loadingInstance = false;
        this.initData();
    }
}
</script>
