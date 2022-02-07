<template>
    <ul class="widget-list widget-links cw-action-widget" v-if="structuralElement">
        <li class="cw-action-widget-show-toc" @click="toggleTOC">
            {{ tocText }}
        </li>
        <li class="cw-action-widget-show-consume-mode" @click="showConsumeMode">
            <translate>Vollbild einschalten</translate>
        </li>
        <li v-if="canEdit" class="cw-action-widget-edit" @click="editElement">
            <translate>Seite bearbeiten</translate>
        </li>
        <li v-if="canEdit" class="cw-action-widget-sort" @click="sortContainers">
            <translate>Abschnitte sortieren</translate>
        </li>
        <li v-if="canEdit" class="cw-action-widget-add" @click="addElement">
            <translate>Seite hinzufügen</translate>
        </li>
        <li class="cw-action-widget-info" @click="showElementInfo"><translate>Informationen anzeigen</translate></li>
        <li class="cw-action-widget-star" @click="createBookmark"><translate>Lesezeichen setzen</translate></li>
        <li v-if="canExport" @click="exportElement" class="cw-action-widget-export">
            <translate>Seite exportieren</translate>
        </li>
        <li v-if="(canEdit || userIsTeacher) && canVisit" class="cw-action-widget-export-pdf">
            <a :href="pdfExportURL">
                <translate>Seite als pdf-Dokument exportieren</translate>
            </a>
        </li>
        <li v-if="canEdit && oerEnabled && userIsTeacher" @click="oerElement" class="cw-action-widget-oer">
            <translate>Seite auf %{oerTitle} veröffentlichen</translate>
        </li>
        <li v-if="!isRoot && canEdit && !isTask" class="cw-action-widget-trash" @click="deleteElement">
            <translate>Seite löschen</translate>
        </li>
    </ul>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-action-widget',
    props: ['structuralElement', 'canVisit'],
    components: {
        StudipIcon,
    },
    mixins: [CoursewareExport],
    computed: {
        ...mapGetters({
            context: 'context',
            oerEnabled: 'oerEnabled',
            oerTitle: 'oerTitle',
            userId: 'userId',
            consumeMode: 'consumeMode',
            showToolbar: 'showToolbar',
            userIsTeacher: 'userIsTeacher',
            consumeMode: 'consumeMode',
            showToolbar: 'showToolbar',
            userIsTeacher: 'userIsTeacher',
        }),
        isRoot() {
            if (!this.structuralElement) {
                return true;
            }

            return this.structuralElement.relationships.parent.data === null;
        },
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
        currentId() {
            return this.structuralElement?.id;
        },
        blocked() {
            return this.structuralElement?.relationships['edit-blocker'].data !== null;
        },
        blockerId() {
            return this.blocked ? this.structuralElement?.relationships['edit-blocker'].data?.id : null;
        },
        blockedByThisUser() {
            return this.blocked && this.userId === this.blockerId;
        },
        blockedByAnotherUser() {
            return this.blocked && this.userId !== this.blockerId;
        },
        tocText() {
            return this.showToolbar ? this.$gettext('Inhaltsverzeichnis ausblenden') : this.$gettext('Inhaltsverzeichnis anzeigen');
        },
        pdfExportURL() {
            if (this.context.type === 'users') {
                return STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id);
            }
            if (this.context.type === 'courses') {
                return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id);
            }

            return '';
        },
        isTask() {
            return this.structuralElement?.relationships.task.data !== null;
        },
        canExport() {
            if (this.context.type === 'users') {
                return true;
            }

            return this.canEdit && this.userIsTeacher;
        },
        tocText() {
            return this.showToolbar ? this.$gettext('Inhaltsverzeichnis ausblenden') : this.$gettext('Inhaltsverzeichnis anzeigen');
        },
        pdfExportURL() {
            if (this.context.type === 'users') {
                return STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id);
            }
            if (this.context.type === 'courses') {
                return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id);
            }

            return '';
        },
        isTask() {
            return this.structuralElement?.relationships.task.data !== null;
        },
        canExport() {
            if (this.context.type === 'users') {
                return true;
            }

            return this.canEdit && this.userIsTeacher;
        }
    },
    methods: {
        ...mapActions({
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            showElementExportDialog: 'showElementExportDialog',
            showElementOerDialog: 'showElementOerDialog',
            setStructuralElementSortMode: 'setStructuralElementSortMode',
            companionInfo: 'companionInfo',
            addBookmark: 'addBookmark',
            lockObject: 'lockObject',
            setConsumeMode: 'coursewareConsumeMode',
            setViewMode: 'coursewareViewMode',
            setShowToolbar: 'coursewareShowToolbar'
        }),
        async editElement() {
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });

                return false;
            }
            try {
                await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            } catch(error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                return false;
            }
            this.showElementEditDialog(true);
        },
        sortContainers() {
            this.setStructuralElementSortMode(true);
        },
        async deleteElement() {
            await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.showElementDeleteDialog(true);
        },
        addElement() {
            this.showElementAddDialog(true);
        },
        exportElement() {
            this.showElementExportDialog(true);
        },
        showElementInfo() {
            this.showElementInfoDialog(true);
        },
        createBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt.') });
        },
        oerElement() {
            this.showElementOerDialog(true);
        },
        toggleTOC() {
            this.setShowToolbar(!this.showToolbar);
        },
        showConsumeMode() {
            this.setViewMode('read');
            this.setConsumeMode(true);
        },
    },
};
</script>
