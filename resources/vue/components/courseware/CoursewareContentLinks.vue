<template>
    <div>
        <table class="default">
            <caption>
                <translate>Öffentlich verlinkte Seiten</translate>
            </caption>
            <thead>
                <tr>
                    <th><translate>Seite</translate></th>
                    <th><translate>Link</translate></th>
                    <th><translate>Passwort</translate></th>
                    <th><translate>Ablaufdatum</translate></th>
                    <th class="actions"><translate>Aktionen</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="link in links" :key="link.id">
                    <td><a :href="getElementUrl(link)">{{ getPage(link) }}</a></td>
                    <td>
                        <a :href="getLinkUrl(link)" target="_blank" :title="getLinkUrl(link)">
                            <studip-icon shape="link-extern" role="clickable" />
                        </a>
                    </td>
                    <td>{{ link.attributes.password || '-' }}</td>
                    <td>{{ getReadableDate(link.attributes['expire-date']) }}</td>
                    <td class="actions">
                        <studip-action-menu
                            :items="menuItems"
                            @editLink="editLink(link)"
                            @deleteLink="displayDeleteLink(link)"
                            @copyLinkToClipboard="copyLinkToClipboard(link)"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
        <studip-dialog
            v-if="showDeleteDialog"
            :title="$gettext('Link löschen')"
            :question="$gettext('Möchten Sie diesen Link löschen')"
            height="180"
            width="360"
            @confirm="executeDelete"
            @close="closeDeleteDialog"
        ></studip-dialog>
        <studip-dialog
            v-if="showEditDialog"
            :title="$gettext('Link bearbeiten')"
            :confirmText="$gettext('Speichern')"
            confirmClass="accept"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            @close="closeEditDialog"
            @confirm="storeLink"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Passwort</translate>
                        <input type="text" v-model="currentLink.attributes.password" />
                    </label>
                    <label>
                        <translate>Ablaufdatum</translate>
                        <input v-model="currentLink.attributes['expire-date']" type="date" class="size-l" />
                    </label>
                </form>
            </template>
        </studip-dialog>
    </div>
</template>

<script>
import StudipActionMenu from './../StudipActionMenu.vue';
import { mapActions, mapGetters } from 'vuex';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-content-links',
    components: {
        StudipActionMenu,
        StudipIcon,
    },
    data() {
        return {
            menuItems: [
                { id: 1, label: this.$gettext('Link in Zwischenablage kopieren'), icon: 'clipboard', emit: 'copyLinkToClipboard'}, 
                { id: 2, label: this.$gettext('Link bearbeiten'), icon: 'edit', emit: 'editLink' },
                { id: 3, label: this.$gettext('Link löschen'), icon: 'trash', emit: 'deleteLink' }
            ],
            showEditDialog: false,
            showDeleteDialog: false,
            currentLink: null
        }
    },
     computed: {
        ...mapGetters({
            context: 'context',
            links: 'courseware-public-links/all',
            getElementById: 'courseware-structural-elements/byId',
        }),
    },
    methods: {
        ...mapActions({
            companionSuccess: 'companionSuccess',
            deleteLink: 'deleteLink',
            updateLink: 'updateLink',
            loadAllLinks: 'courseware-public-links/loadAll'
        }),
        getPage(link) {
            let element = this.getElementById({ id: link.relationships['structural-element'].data.id });
            return element.attributes.title;
        },
        getLinkUrl(link) {
            return STUDIP.URLHelper.getURL('dispatch.php/courseware/public/', { link: link.id });
        },
        getElementUrl(link) {
            return STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/courseware#/structural_element/' + link.relationships['structural-element'].data.id);
        },
        displayDeleteLink(link) {
            this.showDeleteDialog = true;
            this.currentLink = link;
        },
        executeDelete() {
            this.deleteLink({linkId: this.currentLink.id});
            this.closeDeleteDialog();
        },
        closeDeleteDialog() {
            this.showDeleteDialog = false;
            this.currentLink = null;
        },
        editLink(link) {
            this.showEditDialog = true;
            this.currentLink = link;
        },
        closeEditDialog() {
            this.showEditDialog = false;
            this.currentLink = null;
        },
        async storeLink() {
            const date = this.currentLink.attributes['expire-date'];
            let attributes = {
                password: this.currentLink.attributes.password,
                'expire-date': date === '' ? new Date(0).toISOString() : new Date(date).toISOString()
            };

            await this.updateLink({
                attributes: attributes,
                linkId: this.currentLink.id
            });
            this.closeEditDialog();
            this.companionSuccess({
                info: this.$gettext('Änderungen wurden gespeichert.'),
            });
        },
        copyLinkToClipboard(link) {
            navigator.clipboard.writeText(this.getLinkUrl(link));
            this.companionSuccess({
                info: this.$gettext('Link wurde in die Zwischenablage kopiert.'),
            });
        },
        getReadableDate(date) {
            if (!date) {
                return '-';
            }
            return new Date(date).toLocaleDateString(navigator.language, { 
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },
    }
}
</script>
