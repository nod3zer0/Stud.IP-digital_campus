<template>
    <ul class="widget-list widget-links cw-action-widget" v-if="structuralElement">
        <li class="cw-action-widget-show-toc">
            <button @click="toggleTOC">
                {{ tocText }}
            </button>
        </li>
        <li class="cw-action-widget-show-consume-mode">
            <button @click="showConsumeMode">
                <translate>Vollbild einschalten</translate>
            </button>
        </li>
        <li v-if="canEdit" class="cw-action-widget-edit">
            <button @click="editElement">
                <translate>Seite bearbeiten</translate>
            </button>
        </li>
        <li v-if="canEdit" class="cw-action-widget-sort">
            <button @click="sortContainers">
                <translate>Abschnitte sortieren</translate>
            </button>
        </li>
        <li v-if="canEdit" class="cw-action-widget-add">
            <button @click="addElement">
                <translate>Seite hinzufügen</translate>
            </button>
        </li>
        <li class="cw-action-widget-info">
            <button @click="showElementInfo">
                <translate>Informationen anzeigen</translate>
            </button>
        </li>
        <li class="cw-action-widget-star">
            <button @click="createBookmark">
                <translate>Lesezeichen setzen</translate>
            </button>
        </li>
        <li v-if="!isOwner" class="cw-action-widget-oer">
            <button @click="suggestOER">
                <translate>Material für %{oerTitle} vorschlagen</translate>
            </button>
        </li>
        <li v-if="!isRoot && canEdit" class="cw-action-widget-trash">
            <button @click="deleteElement">
                <translate>Seite löschen</translate>
            </button>
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
            return this.structuralElement?.relationships?.['edit-blocker']?.data !== null;
        },
        blockerId() {
            return this.blocked ? this.structuralElement?.relationships?.['edit-blocker']?.data?.id : null;
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
        },
        isOwner() {
            return this.structuralElement.relationships.owner.data.id === this.userId;
        }
    },
    methods: {
        ...mapActions({
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            updateShowSuggestOerDialog: 'updateShowSuggestOerDialog',
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
        suggestOER() {
            this.updateShowSuggestOerDialog(true);
        },
    },
};
</script>
