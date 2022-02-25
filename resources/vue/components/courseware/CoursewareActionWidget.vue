<template>
    <ul class="widget-list widget-links cw-action-widget" v-if="structuralElement">
        <li class="cw-action-widget-show-toc">
            <a href="#" @click="toggleTOC">
                {{ tocText }}
            </a>
        </li>
        <li class="cw-action-widget-show-consume-mode">
            <a href="#" @click="showConsumeMode">
                <translate>Vollbild einschalten</translate>
            </a>
        </li>
        <li v-if="canEdit" class="cw-action-widget-edit">
            <a href="#" @click="editElement">
                <translate>Seite bearbeiten</translate>
            </a>
        </li>
        <li v-if="canEdit" class="cw-action-widget-sort">
            <a href="#" @click="sortContainers">
                <translate>Abschnitte sortieren</translate>
            </a>
        </li>
        <li v-if="canEdit" class="cw-action-widget-add">
            <a href="#" @click="addElement">
                <translate>Seite hinzufügen</translate>
            </a>
        </li>
        <li class="cw-action-widget-info">
            <a href="#" @click="showElementInfo">
                <translate>Informationen anzeigen</translate>
            </a>
        </li>
        <li class="cw-action-widget-star">
            <a href="#" @click="createBookmark">
                <translate>Lesezeichen setzen</translate>
            </a>
        </li>
        <li v-if="!isRoot && canEdit" class="cw-action-widget-trash">
            <a href="#" @click="deleteElement">
                <translate>Seite löschen</translate>
            </a>
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
            userId: 'userId',
            consumeMode: 'consumeMode',
            showToolbar: 'showToolbar',
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
        isTask() {
            return this.structuralElement?.relationships.task.data !== null;
        }
    },
    methods: {
        ...mapActions({
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            setStructuralElementSortMode: 'setStructuralElementSortMode',
            companionInfo: 'companionInfo',
            addBookmark: 'addBookmark',
            lockObject: 'lockObject',
            setConsumeMode: 'coursewareConsumeMode',
            setViewMode: 'coursewareViewMode',
            setShowToolbar: 'coursewareShowToolbar',
            setSelectedToolbarItem: 'coursewareSelectedToolbarItem'
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
        showElementInfo() {
            this.showElementInfoDialog(true);
        },
        createBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt.') });
        },
        toggleTOC() {
            this.setShowToolbar(!this.showToolbar);
        },
        showConsumeMode() {
            this.setViewMode('read');
            this.setSelectedToolbarItem('contents');
            this.setConsumeMode(true);
        },
    },
};
</script>
