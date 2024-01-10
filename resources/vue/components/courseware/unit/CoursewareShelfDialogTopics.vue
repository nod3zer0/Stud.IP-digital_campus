<template>
    <studip-dialog
        :title="$gettext('Lernmaterial aus Ablaufplan-Themen erstellen')"
        :confirmText="$gettext('Erstellen')"
        confirmClass="accept"
        :closeText="$gettext('Abbrechen')"
        closeClass="cancel"
        height="450"
        width="500"
        @close="setShowUnitTopicsDialog(false)"
        @confirm="createUnit"
    >
        <template v-slot:dialogContent>
            <form class="default" @submit.prevent="">
                <courseware-collapsible-box :title="$gettext('Grundeinstellungen')" :open="true" >
                    <label class="studiprequired">
                        {{ text.title }}
                        <span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
                        <input type="text" v-model="title" required />
                    </label>
                    <label class="studiprequired">
                        {{ text.description }}
                        <span :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true" class="asterisk">*</span>
                        <textarea v-model="description" required />
                    </label>
                </courseware-collapsible-box>
                <courseware-collapsible-box :title="$gettext('Darstellung')" >
                    <label>
                        {{ $gettext('Bild hochladen') }}
                        <br>
                        <input
                            class="cw-file-input"
                            ref="upload_image"
                            type="file"
                            accept="image/*"
                            @change="checkUploadFile"
                        >
                        <CoursewareCompanionBox
                            v-if="uploadFileError"
                            :msgCompanion="uploadFileError"
                            mood="sad"
                            class="cw-companion-box-in-form"
                        />
                    </label>
                    <template v-if="selectedStockImage">
                        <StockImageSelectableImageCard :stock-image="selectedStockImage" />
                        <label>
                            <button class="button" type="button" @click="selectedStockImage = null">
                                {{ $gettext('Bild entfernen') }}
                            </button>
                        </label>
                    </template>
                    <label v-else>
                        {{ $gettext('oder') }}
                        <br>
                        <button class="button" type="button" @click="showStockImageSelector = true">
                            {{ $gettext('Aus dem Bilderpool auswählen') }}
                        </button>
                        <StockImageSelector
                            v-if="showStockImageSelector"
                            @close="showStockImageSelector = false"
                            @select="onSelectStockImage"
                        />
                    </label>
                    <label>
                        {{ $gettext('Farbe') }}
                        <studip-select v-model="color" :options="colors" :reduce="(color) => color.class" label="class">
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10" /></span>
                            </template>
                            <template #no-options>
                                {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                            </template>
                            <template #selected-option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span>
                                <span>{{ name }}</span>
                            </template>
                            <template #option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                ><span>{{ name }}</span>
                            </template>
                        </studip-select>
                    </label>
                </courseware-collapsible-box>
            </form>
        </template>
    </studip-dialog>
</template>

<script>
import CoursewareCollapsibleBox from '../layouts/CoursewareCollapsibleBox.vue';
import StockImageSelectableImageCard from '../../stock-images/SelectableImageCard.vue';
import StockImageSelector from '../../stock-images/SelectorDialog.vue';
import StudipSelect from '../../StudipSelect.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-shelf-dialog-topics',
    mixins: [colorMixin],
    components: {
        CoursewareCollapsibleBox,
        StockImageSelectableImageCard,
        StockImageSelector,
        StudipSelect,
    },
    data() {
        return {
            title: '',
            description: '',
            color: 'studip-blue',
            uploadFileError: '',
            showStockImageSelector: false,
            selectedStockImage: null,

            text: {
                title: this.$gettext('Titel des Lernmaterials'),
                description: this.$gettext('Beschreibung'),
            },
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            lastCreateCoursewareUnit: 'courseware-units/lastCreated',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        colors() {
            return this.mixinColors.filter((color) => color.darkmode);
        },
    },
    methods: {
        ...mapActions({
            companionError: 'companionError',
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            createCoursewareUnit: 'courseware-units/create',
            setShowUnitTopicsDialog: 'setShowUnitTopicsDialog',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
        }),
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext(
                    'Diese Datei ist zu groß. Bitte wählen Sie eine Datei aus, die kleiner als 2MB ist.'
                );
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
                this.selectedStockImage = null;
            }
        },
        async createUnit() {
            if (this.title === '') {
                this.companionError({
                    info: this.$gettext('Bitte geben Sie einen Titel ein.'),
                });
                return false;
            }
            if (this.description === '') {
                this.companionError({
                    info: this.$gettext('Bitte geben Sie eine Beschreibung ein.'),
                });
                return false;
            }
            const file = this.$refs?.upload_image?.files[0];
            const unit = {
                attributes: {
                    title: this.title,
                    purpose: 'content',
                    payload: {
                        description: this.description,
                        color: this.color,
                        license_type: '',
                        required_time: '',
                        difficulty_start: '',
                        difficulty_end: '',
                    },
                },
                relationships: {
                    range: {
                        data: {
                            type: this.context.type,
                            id: this.context.id,
                        },
                    },
                },
                template: {
                    type: 'topics',
                },
            };
            await this.createCoursewareUnit(unit, { root: true });
            this.setShowUnitTopicsDialog(false);

            const newElementId = this.lastCreateCoursewareUnit.relationships['structural-element'].data.id;
            await this.loadStructuralElementById({ id: newElementId });
            let newStructuralElement = this.structuralElementById({ id: newElementId });

            try {
                if (file) {
                    await this.uploadImageForStructuralElement({
                        structuralElement: newStructuralElement,
                        file,
                    });
                } else if (this.selectedStockImage) {
                    await this.setStockImageForStructuralElement({
                        structuralElement: newStructuralElement,
                        stockImage: this.selectedStockImage,
                    });
                }

                this.loadStructuralElementById({ id: newStructuralElement.id, options: { include: 'children' } });
            } catch (error) {
                console.error(error);
                this.companionError({
                    info: this.$gettext('Das Bild für das neue Lernmaterial konnte nicht gespeichert werden.'),
                });
            }
        },
        onSelectStockImage(stockImage) {
            if (this.$refs?.upload_image) {
                this.$refs.upload_image.value = null;
            }
            this.selectedStockImage = stockImage;
            this.showStockImageSelector = false;
        },
    },
};
</script>
