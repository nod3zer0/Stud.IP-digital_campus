<template>
    <div class="cw-admin-templates">
        <table class="default">
            <caption>
                <translate>Vorlagen</translate>
            </caption>
            <thead>
                <tr>
                    <th><translate>Art des Lernmaterials</translate></th>
                    <th><translate>Name</translate></th>
                    <th class="actions"><translate>Aktionen</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="template in templates" :key="template.id">
                    <td>{{ getPurposeName(template.attributes.purpose) }}</td>
                    <td>{{ template.attributes.name }}</td>
                    <td class="actions">
                        <studip-action-menu
                            :items="menuItems"
                            @editTemplate="editTemplate(template.id)"
                            @deleteTemplate="confimDeleteTemplate(template.id)"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
        <studip-dialog
            v-if="showAddTemplateDialog"
            :title="$gettext('Vorlage hinzufügen')"
            :confirmText="$gettext('Erstellen')"
            confirmClass="accept"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            class="cw-admin-template-dialog"
            height="360"
            @close="closeAddDialog"
            @confirm="createNewTemplate"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Name der neuen Vorlage</translate>
                        <input v-model="newTemplateName" type="text" />
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
                        <translate>Vorlage</translate><br>
                        <button
                            class="button"
                            @click.prevent="chooseFile"
                        >
                            <translate>Vorlage-Archiv auswählen</translate>
                        </button>
                        <div v-if="importZip" class="cw-import-zip">
                            <header>{{ importZip.name }}</header>
                        </div>
                        <input ref="importFile" type="file" accept=".zip" @change="setImport" style="visibility: hidden" />
                    </label>
                </form>
            </template>
        </studip-dialog>
        <studip-dialog
            v-if="showEditTemplateDialog"
            :title="$gettext('Vorlage bearbeiten')"
            :confirmText="$gettext('Speichern')"
            confirmClass="accept"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            class="cw-admin-template-dialog"
            @close="closeEditDialog"
            @confirm="updateCurrentTemplate"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Name der neuen Vorlage</translate>
                        <input v-model="currentTemplate.attributes.name" type="text" />
                    </label>
                    <label>
                        <translate>Art des Lernmaterials</translate>
                        <select v-model="currentTemplate.attributes.purpose">
                            <option value="content"><translate>Inhalt</translate></option>
                            <option value="template"><translate>Aufgabenvorlage</translate></option>
                            <option value="oer"><translate>OER-Material</translate></option>
                            <option value="portfolio"><translate>ePortfolio</translate></option>
                            <option value="draft"><translate>Entwurf</translate></option>
                            <option value="other"><translate>Sonstiges</translate></option>
                        </select>
                    </label>
                </form>
            </template>
        </studip-dialog>
        <studip-dialog
                v-if="showDeleteDialog"
                :title="$gettext('Vorlage löschen')"
                :question="$gettext('Möchten Sie diese Vorlage wirklich löschen?')"
                height="180"
                @confirm="deleteCurrentTemplate"
                @close="closeDeleteDialog"
        ></studip-dialog>
    </div>
</template>

<script>
import StudipActionMenu from '../StudipActionMenu.vue';
import StudipDialog from './../StudipDialog.vue';

import JSZip from 'jszip';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-admin-templates',
    components: {
        StudipActionMenu,
        StudipDialog,
    },
    data() {
        return {
            menuItems: [
                { id: 1, label: this.$gettext('Vorlage bearbeiten'), icon: 'edit', emit: 'editTemplate' },
                { id: 2, label: this.$gettext('Vorlage löschen'), icon: 'trash', emit: 'deleteTemplate' }
            ],
            newTemplateName: '',
            newElementPurpose: '',
            importZip: null,
            zip: null,
            showEditTemplateDialog: false,
            currentTemplate: null,
            showDeleteDialog: false,
        }
    },
    computed: {
        ...mapGetters({
            templateById: 'courseware-templates/byId',
            showAddTemplateDialog: 'showAddTemplateDialog',
            templates: 'courseware-templates/all',
        }),
    },
    methods: {
        ...mapActions({
            createTemplate: 'courseware-templates/create',
            updateTemplate: 'courseware-templates/update',
            deleteTemplate: 'courseware-templates/delete',
            setShowAddTemplateDialog: 'showAddTemplateDialog'
        }),
        closeAddDialog() {
            this.setShowAddTemplateDialog(false);
            this.newTemplateName = '';
            this.newElementPurpose = '';
            this.importZip = null;
            this.zip = null;
        },
        setImport(event) {
            this.importZip = event.target.files[0];
        },
        chooseFile() {
            this.$refs.importFile.click();
        },
        async createNewTemplate() {
            let view = this;
            let data = null;
            this.zip = new JSZip();

            await this.zip.loadAsync(this.importZip).then(async function () {
                data = await view.zip.file('courseware.json').async('string');
            });

            this.createTemplate({
                name: this.newTemplateName,
                purpose: this.newElementPurpose,
                structure: data
            });

            this.closeAddDialog();
        },
        editTemplate(templateId) {
            this.currentTemplate = this.templateById({id: templateId});
            this.showEditTemplateDialog = true;
        },
        closeEditDialog() {
            this.showEditTemplateDialog = false;
            this.currentTemplate = null;
        },
        updateCurrentTemplate() {
            this.updateTemplate({
                id: this.currentTemplate.id,
                name: this.currentTemplate.attributes.name,
                purpose: this.currentTemplate.attributes.purpose,
            });
            this.closeEditDialog();
        },
        confimDeleteTemplate(templateId) {
            this.currentTemplate = this.templateById({id: templateId});
            this.showDeleteDialog = true;
        },
        deleteCurrentTemplate() {
            this.deleteTemplate( {
                id: this.currentTemplate.id
            });
            this.closeDeleteDialog();
        },
        closeDeleteDialog() {
            this.showDeleteDialog = false;
            this.currentTemplate = null;
        },
        getPurposeName(purpose) {
            switch (purpose) {
                case 'content':
                    return this.$gettext('Inhalt');
                case 'template':
                    return this.$gettext('Aufgabenvorlage');
                case 'oer':
                    return this.$gettext('OER-Material');
                case 'portfolio':
                    return this.$gettext('ePortfolio');
                case 'draft':
                    return this.$gettext('Entwurf');
                case 'other':
                    return this.$gettext('Sonstige');
                default:
                    return purpose;
            }
        }
    }

}
</script>