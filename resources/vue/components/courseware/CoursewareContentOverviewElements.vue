<template>
<div v-if="root">
    <ul class="cw-tiles">
        <li
            v-for="child in filteredChildren"
            :key="child.id"
            class="tile"
            :class="[child.attributes.payload.color, filteredChildren.length > 3 ? '':  'cw-tile-margin']"
        >
            <a :href="getElementUrl(child.id)" :title="child.attributes.title">
                <div
                    class="preview-image"
                    :class="[hasImage(child) ? '' : 'default-image']"
                    :style="getChildStyle(child)"
                ></div>
                <div class="description">
                    <header
                        :class="[child.attributes.purpose !== '' ? 'description-icon-' + child.attributes.purpose : '']"
                    >
                        {{ child.attributes.title }}
                    </header>
                    <div class="description-text-wrapper">
                        <p>{{ child.attributes.payload.description }}</p>
                    </div>
                    <footer>
                        {{ countChildren(child) + 1 }}
                        <translate
                            :translate-n="countChildren(child) + 1"
                            translate-plural="Seiten"
                        >
                            Seite
                        </translate>
                    </footer>
                </div>
            </a>
        </li>
    </ul>
    <courseware-companion-box v-if="children.length !== 0 && filteredChildren.length === 0 && purposeFilter !== 'all'" :msgCompanion="text.emptyFilter" mood="pointing"/>
    <div v-if="children.length === 0" class="cw-contents-overview-teaser">
        <div class="cw-contents-overview-teaser-content">
            <header><translate>Ihre persönlichen Lernmaterialien</translate></header>
            <p><translate>Erstellen und Verwalten Sie hier ihre eigenen persönlichen Lernmaterialien in Form von ePorfolios,
                        Vorlagen für Veranstaltungen oder einfach nur persönliche Inhalte für das Studium.
                        Entwickeln Sie ihre eigenen (Lehr-)Materialien für Studium oder die Lehre und teilen diese mit anderen Nutzenden.</translate></p>
            <button class="button" @click="addElement">
                <translate>Neues Lernmaterial anlegen</translate>
            </button>
        </div>
    </div>
    <studip-dialog
        v-if="showOverviewElementAddDialog"
        :title="$gettext('Neues Lernmaterial anlegen')"
        height="600"
        width="500"
        :confirmText="$gettext('Erstellen')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        class="cw-structural-element-dialog"
        @close="closeAddDialog"
        @confirm="createElement"
    >
        <template v-slot:dialogContent>

                <courseware-collapsible-box
                :title="$gettext('Grundeinstellungen')"
                :open="true"
                >
                    <form class="default" @submit.prevent="">
                        <label>
                            <translate>Titel des Lernmaterials</translate><br />
                            <input v-model="newElement.attributes.title" type="text" />
                        </label>
                        <label>
                            <translate>Zusammenfassung</translate><br />
                            <textarea v-model="newElement.attributes.payload.description"></textarea>
                        </label>
                        <label>
                            <translate>Bild</translate>
                            <br>
                            <input ref="upload_image" type="file" accept="image/*" @change="checkUploadFile" />
                            <courseware-companion-box
                                v-if="uploadFileError"
                                :msgCompanion="uploadFileError"
                                mood="sad"
                                class="cw-companion-box-in-form"
                            />
                        </label>
                        <label>
                            <translate>Art des Lernmaterials</translate>
                            <select v-model="newElementPurpose">
                                <option value="content"><translate>Inhalt</translate></option>
                                <option value="template"><translate>Aufgabenvorlage</translate></option>
                                <option value="oer"><translate>OER-Material</translate></option>
                                <option value="portfolio"><translate>ePortfolio</translate></option>
                                <option value="draft"><translate>Entwurf</translate></option>
                                <option value="other"><translate>Sonstiges</translate></option>
                            </select>
                        </label>
                        <label>
                            <translate>Lernmaterialvorlage</translate>
                            <select v-model="newElementTemplate">
                                <option :value="null"><translate>ohne Vorlage</translate></option>
                                <option
                                    v-for="template in selectableTemplates"
                                    :key="template.id"
                                    :value="template"
                                >
                                    {{ template.attributes.name }}
                                </option>
                            </select>
                        </label>
                    </form>
                </courseware-collapsible-box>
                <courseware-collapsible-box :title="$gettext('Vorschau')">
                    <div v-if="currentTemplateStructure" class="cw-template-preview">
                        <div
                            class="cw-template-preview-container-wrapper"
                            v-for="container in currentTemplateStructure.containers"
                            :key="container.id"
                            :class="['cw-template-preview-container-' + container.attributes.payload.colspan]"
                        >
                            <div class="cw-template-preview-container-content">
                                <header class="cw-template-preview-container-title">
                                    {{ container.attributes.title }} | {{ container.attributes.width }}
                                </header>
                                <div class="cw-template-preview-blocks" v-for="block in container.blocks" :key="block.id">
                                    <header class="cw-template-preview-blocks-title">
                                        {{ block.attributes.title }}
                                    </header>
                                </div>
                            </div>
                        </div>
                    </div>
                    <courseware-companion-box
                        v-else
                        :msgCompanion="$gettext('Sie können eine Lernmaterialvorlage auswählen und hier eine Vorschau betrachten. Ohne Vorlage wird eine leere Seite erzeugt.')"
                    />
                </courseware-collapsible-box>
                <courseware-collapsible-box
                    :title="$gettext('Zusatzangaben')"
                >
                    <form class="default" @submit.prevent="">
                        <label>
                            <translate>Lizenztyp</translate>
                            <select v-model="newElement.attributes.payload.license_type">
                                <option v-for="license in licenses" :key="license.id" :value="license.id">
                                    {{ license.name }}
                                </option>
                            </select>
                        </label>
                        <label>
                            <translate>Geschätzter zeitlicher Aufwand</translate>
                            <input type="text" v-model="newElement.attributes.payload.required_time" />
                        </label>
                        <label>
                            <translate>Niveau</translate><br />
                            <translate>von</translate>
                            <select v-model="newElement.attributes.payload.difficulty_start">
                                <option
                                    v-for="difficulty_start in 12"
                                    :key="difficulty_start"
                                    :value="difficulty_start"
                                >
                                    {{ difficulty_start }}
                                </option>
                            </select>
                            <translate>bis</translate>
                            <select v-model="newElement.attributes.payload.difficulty_end">
                                <option
                                    v-for="difficulty_end in 12"
                                    :key="difficulty_end"
                                    :value="difficulty_end"
                                >
                                    {{ difficulty_end }}
                                </option>
                            </select>
                        </label>
                        <label>
                            <translate>Farbe</translate>
                            <v-select
                                v-model="newElement.attributes.payload.color"
                                :options="colors"
                                :reduce="(color) => color.class"
                                label="class"
                            >
                                <template #open-indicator="selectAttributes">
                                    <span v-bind="selectAttributes"
                                        ><studip-icon shape="arr_1down" size="10"
                                    /></span>
                                </template>
                                <template #no-options="{ search, searching, loading }">
                                    <translate>Es steht keine Auswahl zur Verfügung.</translate>
                                </template>
                                <template #selected-option="{ name, hex }">
                                    <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                    ><span>{{ name }}</span>
                                </template>
                                <template #option="{ name, hex }">
                                    <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                    ><span>{{ name }}</span>
                                </template>
                            </v-select>
                        </label>
                    </form>
                </courseware-collapsible-box>

        </template>
    </studip-dialog>
    <courseware-companion-overlay />
</div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';
import StudipDialog from '../StudipDialog.vue';

export default {
    name: 'courseware-content-overview-elements',
    components: {
        CoursewareCollapsibleBox,
        CoursewareCompanionOverlay,
        CoursewareCompanionBox,
        StudipDialog
    },
    data() {
        return {
            text: {
                emptyFilter: this.$gettext('Für diese Auswahl wurden keine Lernmaterialien gefunden.'),
                empty: this.$gettext('Es wurden keine Lernmaterialien gefunden.'),
            },
            newElement: {
                attributes: {
                    payload: {},
                },
            },
            newElementPurpose: 'content',
            newElementTemplate: null,
            uploadFileError: '',
        }
    },
    computed: {
        ...mapGetters({
            getElement: 'courseware-structural-elements/byId',
            licenses: 'licenses',
            purposeFilter: 'purposeFilter',
            showOverviewElementAddDialog: 'showOverviewElementAddDialog',
            templates: 'courseware-templates/all',
        }),
        root() {
            return this.getElement({id: STUDIP.COURSEWARE_USERS_ROOT_ID});
        },
        children() {
            let view = this;
            let children = [];
            if(this.root?.relationships?.children?.data) {
                this.root.relationships.children.data.forEach(function(child){
                    let element = view.getElement({id: child.id});
                    children.push(element);
                });
            }

            return children;
        },
        filteredChildren() {
            if (this.purposeFilter !== 'all') {
                return this.children.filter(child => { return child.attributes.purpose === this.purposeFilter});
            }
            return this.children;
        },
        colors() {
            const colors = [
                {
                    name: this.$gettext('Schwarz'),
                    class: 'black',
                    hex: '#000000',
                    level: 100,
                    icon: 'black',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Weiß'),
                    class: 'white',
                    hex: '#ffffff',
                    level: 100,
                    icon: 'white',
                    darkmode: false,
                },

                {
                    name: this.$gettext('Blau'),
                    class: 'studip-blue',
                    hex: '#28497c',
                    level: 100,
                    icon: 'blue',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Hellblau'),
                    class: 'studip-lightblue',
                    hex: '#e7ebf1',
                    level: 40,
                    icon: 'lightblue',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Rot'),
                    class: 'studip-red',
                    hex: '#d60000',
                    level: 100,
                    icon: 'red',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Grün'),
                    class: 'studip-green',
                    hex: '#008512',
                    level: 100,
                    icon: 'green',
                    darkmode: true,
                },
                {
                    name: this.$gettext('Gelb'),
                    class: 'studip-yellow',
                    hex: '#ffbd33',
                    level: 100,
                    icon: 'yellow',
                    darkmode: false,
                },
                {
                    name: this.$gettext('Grau'),
                    class: 'studip-gray',
                    hex: '#636a71',
                    level: 100,
                    icon: 'grey',
                    darkmode: true,
                },

                {
                    name: this.$gettext('Holzkohle'),
                    class: 'charcoal',
                    hex: '#3c454e',
                    level: 100,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Königliches Purpur'),
                    class: 'royal-purple',
                    hex: '#8656a2',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Leguangrün'),
                    class: 'iguana-green',
                    hex: '#66b570',
                    level: 60,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Königin blau'),
                    class: 'queen-blue',
                    hex: '#536d96',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Helles Seegrün'),
                    class: 'verdigris',
                    hex: '#41afaa',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Maulbeere'),
                    class: 'mulberry',
                    hex: '#bf5796',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Kürbis'),
                    class: 'pumpkin',
                    hex: '#f26e00',
                    level: 100,
                    icon: false,
                    darkmode: true,
                },
                {
                    name: this.$gettext('Sonnenschein'),
                    class: 'sunglow',
                    hex: '#ffca5c',
                    level: 80,
                    icon: false,
                    darkmode: false,
                },
                {
                    name: this.$gettext('Apfelgrün'),
                    class: 'apple-green',
                    hex: '#8bbd40',
                    level: 80,
                    icon: false,
                    darkmode: true,
                },
            ];
            let elementColors = [];
            colors.forEach((color) => {
                if (color.darkmode) {
                    elementColors.push(color);
                }
            });

            return elementColors;
        },
        selectableTemplates() {
            return this.templates.filter(template => {
                return template.attributes.purpose === this.newElementPurpose
            });
        },
        currentTemplateStructure() {
            if(this.newElementTemplate === null) {
                return null;
            }

            return JSON.parse(this.newElementTemplate.attributes.structure);
        }
    },
    methods: {
        ...mapActions({
            createStructuralElement: 'createStructuralElement',
            createStructuralElementWithTemplate: 'createStructuralElementWithTemplate',
            loadElement: 'courseware-structural-elements/loadById',
            setShowOverviewElementAddDialog: 'setShowOverviewElementAddDialog',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
            companionInfo: 'companionInfo',
        }),
        getChildStyle(child) {
            let url = child.relationships?.image?.meta?.['download-url'];

            if(url) {
                return {'background-image': 'url(' + url + ')'};
            } else {
                return {};
            }
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },
        getElementUrl(element_id) {
            return STUDIP.URLHelper.base_url + 'dispatch.php/contents/courseware/courseware#/structural_element/' + element_id;
        },
        addElement() {
            this.setShowOverviewElementAddDialog(true);
        },
        closeAddDialog() {
            this.setShowOverviewElementAddDialog(false);
            this.initNewElement();
        },
        async createElement() {
            this.setShowOverviewElementAddDialog(false);
            const file = this.$refs?.upload_image?.files[0];
            this.newElement.attributes.purpose = this.newElementPurpose;
            await this.createStructuralElementWithTemplate({
                attributes: this.newElement.attributes,
                templateId: this.newElementTemplate ? this.newElementTemplate.id : null,
                parentId: this.root.id,
                currentId: this.root.id,
            });
            let newStructuralElement = this.$store.getters['courseware-structural-elements/lastCreated'];

            if (file) {
                await this.uploadImageForStructuralElement({
                    structuralElement: newStructuralElement,
                    file,
                }).catch((error) => {
                    console.error(error);
                    this.companionInfo({ info: this.$gettext('Das Bild für das neue Lernmaterial konnte nicht gespeichert werden.') });
                });
                this.loadElement({id: newStructuralElement.id, options: {include: 'children'}});
            }
            this.initNewElement();

        },
        initNewElement() {
            this.newElement = {
                attributes: {
                    payload: {},
                    purpose: '',
                },
                template: ''
            };
        },
        countChildren(element) {
            let data = element.relationships.children.data;
            if (data) {
                return data.length;
            }
            return 0;
        },
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine Datei aus, die kleiner als 2MB groß ist.');
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
            }
        },
    },
    watch: {
        root(newRootObject) {
            let view = this;
            if (newRootObject) {
                newRootObject.relationships.children.data.forEach(function(child) {
                    view.loadElement({id: child.id, options: {include: 'children'}});
                });
            }
        },
        newElementPurpose() {
            this.newElementTemplate = null;
        }
    }
}
</script>
