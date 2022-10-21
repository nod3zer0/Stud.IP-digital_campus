<template>
   <div>
        <table class="default">
            <caption>
                <translate>Von mir geteilte Lerninhalte</translate>
            </caption>
            <thead>
                <tr>
                    <th><translate>Seite</translate></th>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Lesen & Schreiben</translate></th>
                    <th class="actions"><translate>Aktionen</translate></th>
                </tr>
            </thead>
             <tbody>
                <tr v-for="element in releasedElements" :key="element.id">
                    <td>
                        <a :href="getElementUrl(element)">
                            {{ element.attributes.title }}
                        </a>
                    </td>
                    <td>
                        <span
                            v-if="element.attributes['read-approval'].users.length > 0"
                            role="checkbox"
                            aria-checked="true"
                            aria-disabled="true"
                        >
                            <studip-icon shape="accept" role="info" />
                        </span>
                    </td>
                    <td>
                        <span
                            v-if="element.attributes['write-approval'].users.length > 0"
                            role="checkbox"
                            aria-checked="true"
                            aria-disabled="true"
                        >
                            <studip-icon shape="accept" role="info" />
                        </span>
                    </td>
                    <td class="actions">
                        <studip-action-menu
                            :items="menuItems"
                            @editReleases="displayEditReleases(element)"
                            @clearReleases="displayClearReleases(element)"
                        />
                    </td>
                </tr>
             </tbody>
        </table>

        <studip-dialog
            v-if="showEditReleases"
            :title="$gettext('Freigabe bearbeiten')"
            :confirmText="$gettext('Speichern')"
            confirmClass="accept"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            height="480"
            width="720"
            @confirm="storeReleases"
            @close="closeEditReleases"
        >
            <template v-slot:dialogContent>
                <courseware-content-permissions
                    :element="selectedElement"
                    @updateReadApproval="updateReadApproval"
                    @updateWriteApproval="updateWriteApproval"
                />
            </template>
        </studip-dialog>

        <studip-dialog
            v-if="showClearReleases"
            :title="$gettext('Löschen der Freigabe')"
            :question="$gettextInterpolate($gettext('Möchten Sie die Freigabe für %{ pageTitle} wirklich löschen?'), {pageTitle: this.selectedElement.attributes.title})"
            height="220"
            @confirm="clearReleases"
            @close="closeClearReleases"
        ></studip-dialog>
   </div>
</template>

<script>
import CoursewareContentPermissions from './CoursewareContentPermissions.vue';
import { mapActions, mapGetters } from 'vuex';
import StudipActionMenu from './../StudipActionMenu.vue';
import StudipDialog from '../StudipDialog.vue';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-content-shared',
    components: {
        CoursewareContentPermissions,
        StudipActionMenu,
        StudipDialog,
        StudipIcon,
    },
    data() {
        return {
            menuItems: [
                { id: 1, label: this.$gettext('Freigabe bearbeiten'), icon: 'edit', emit: 'editReleases' },
                { id: 2, label: this.$gettext('Freigabe löschen'), icon: 'trash', emit: 'clearReleases' }
            ],
            showClearReleases: false,
            showEditReleases: false,
            selectedElement: null
        }
    },
    computed: {
        ...mapGetters({
            releasedElements: 'courseware-structural-elements-released/all',
        }),
     },
      methods: {
        ...mapActions({
            updateStructuralElement: 'updateStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            relaodSharedElements: 'courseware-structural-elements-released/loadAll'
        }),
        getElementUrl(element) {
            return STUDIP.URLHelper.base_url + 'dispatch.php/contents/courseware/courseware#/structural_element/' + element.id;
        },
        updateReadApproval(approval) {
            this.selectedElement.attributes['read-approval'] = approval;
        },
        updateWriteApproval(approval) {
            this.selectedElement.attributes['write-approval'] = approval;
        },
        displayEditReleases(element) {
            this.selectedElement = element;
            this.showEditReleases = true;
        },
        async storeReleases() {
            const currentId = this.selectedElement.id;
            await this.lockObject({ id: currentId, type: 'courseware-structural-elements' });
            await this.updateStructuralElement({
                element: this.selectedElement,
                id: currentId,
            });
            await this.unlockObject({ id: currentId, type: 'courseware-structural-elements' });
            this.closeEditReleases();
        },
        closeEditReleases() {
            this.showEditReleases = false;
            this.selectedElement = null;
        },
        displayClearReleases(element) {
            this.selectedElement = element;
            this.showClearReleases = true;
        },
        async clearReleases() {
            const currentId = this.selectedElement.id;
            this.selectedElement.attributes['read-approval'].users = [];
            this.selectedElement.attributes['write-approval'].users = [];
            await this.lockObject({ id: currentId, type: 'courseware-structural-elements' });
            await this.updateStructuralElement({
                element: this.selectedElement,
                id: currentId,
            });
            await this.unlockObject({ id: currentId, type: 'courseware-structural-elements' });
            this.closeClearReleases();
            this.relaodSharedElements();
        },
        closeClearReleases() {
            this.showClearReleases = false;
            this.selectedElement = null;
        },
      },
}
</script>
