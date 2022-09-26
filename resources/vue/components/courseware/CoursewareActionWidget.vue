<template>
    <sidebar-widget :title="$gettext('Aktionen')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <li class="cw-action-widget-show-toc">
                    <button @click="toggleTOC">
                        {{ tocText }}
                    </button>
                </li>
                <li class="cw-action-widget-show-consume-mode">
                    <button @click="showConsumeMode">
                        {{ $gettext('Vollbild einschalten') }}
                    </button>
                </li>
                <li v-if="canEdit && !blockedByAnotherUser" class="cw-action-widget-edit">
                    <button @click="editElement">
                        {{ $gettext('Seite bearbeiten') }}
                    </button>
                </li>
                <li v-if="canEdit && blockedByAnotherUser" class="cw-action-widget-remove-lock">
                    <button @click="removeElementLock">
                        {{ $gettext('Sperre aufheben') }}
                    </button>
                </li>
                <li v-if="canEdit && !blockedByAnotherUser" class="cw-action-widget-sort">
                    <button @click="sortContainers">
                        {{ $gettext('Abschnitte sortieren') }}
                    </button>
                </li>
                <li v-if="canEdit" class="cw-action-widget-add">
                    <button @click="addElement">
                        {{ $gettext('Seite hinzufügen') }}
                    </button>
                </li>
                <li class="cw-action-widget-info">
                    <button @click="showElementInfo">
                        {{ $gettext('Informationen anzeigen') }}
                    </button>
                </li>
                <li class="cw-action-widget-star">
                    <button @click="createBookmark">
                        {{ $gettext('Lesezeichen setzen') }}
                    </button>
                </li>
                <li v-if="context.type === 'users'" class="cw-action-widget-link">
                    <button @click="linkElement">
                        {{ $gettext('Öffentlichen Link erzeugen') }}
                    </button>
                </li>
                <li v-if="!isOwner" class="cw-action-widget-oer">
                    <button @click="suggestOER">
                        <translate>Material für den OER Campus vorschlagen</translate>
                    </button>
                </li>
                <li v-if="!isRoot && canEdit && !blockedByAnotherUser" class="cw-action-widget-trash">
                    <button @click="deleteElement">
                        {{ $gettext('Seite löschen') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
import SidebarWidget from '../SidebarWidget.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-action-widget',
    props: ['structuralElement', 'canVisit'],
    components: {
        StudipIcon,
        SidebarWidget,
    },
    mixins: [CoursewareExport],
    computed: {
        ...mapGetters({
            userId: 'userId',
            consumeMode: 'consumeMode',
            showToolbar: 'showToolbar',
            context: 'context',

            blocked: 'currentElementBlocked',
            blockerId: 'currentElementBlockerId',
            blockedByThisUser: 'currentElementBlockedByThisUser',
            blockedByAnotherUser: 'currentElementBlockedByAnotherUser',
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
            showElementLinkDialog: 'showElementLinkDialog',
            showElementRemoveLockDialog: 'showElementRemoveLockDialog',
            updateShowSuggestOerDialog: 'updateShowSuggestOerDialog',
            setStructuralElementSortMode: 'setStructuralElementSortMode',
            companionInfo: 'companionInfo',
            addBookmark: 'addBookmark',
            lockObject: 'lockObject',
            setConsumeMode: 'coursewareConsumeMode',
            setViewMode: 'coursewareViewMode',
            setShowToolbar: 'coursewareShowToolbar',
            setSelectedToolbarItem: 'coursewareSelectedToolbarItem',
            loadStructuralElement: 'loadStructuralElement',
        }),
        async editElement() {
            await this.loadStructuralElement(this.currentId);
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
        async removeElementLock() {
            this.showElementRemoveLockDialog(true);
        },
        async sortContainers() {
            await this.loadStructuralElement(this.currentId);
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });

                return false;
            }
            try {
                await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            } catch (error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Diese Seite wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                return false;
            }
            this.setStructuralElementSortMode(true);
        },
        async deleteElement() {
            await this.loadStructuralElement(this.currentId);
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettextInterpolate('Löschen nicht möglich, da %{blockingUserName} die Seite bearbeitet.', {blockingUserName: this.blockingUserName}) });

                return false;
            }
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
        linkElement() {
            this.showElementLinkDialog(true);
        }
    },
};
</script>
