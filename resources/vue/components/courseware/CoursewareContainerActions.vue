<template>
    <div v-if="canEdit" class="cw-container-actions">
        <studip-action-menu
            :items="menuItems"
            :context="container.attributes.title"
            @editContainer="editContainer"
            @changeContainer="changeContainer"
            @deleteContainer="deleteContainer"
            @removeLock="removeLock"
        />
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
export default {
    name: 'courseware-container-actions',
    props: {
        canEdit: Boolean,
        container: Object,
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            userIsTeacher: 'userIsTeacher',
        }),
        blocked() {
            return this.container?.relationships?.['edit-blocker']?.data !== null;
        },
        blockerId() {
            return this.blocked ? this.container?.relationships?.['edit-blocker']?.data?.id : null;
        },
        blockedByThisUser() {
            return this.blocked && this.userId === this.blockerId;
        },
        blockedByAnotherUser() {
            return this.blocked && this.userId !== this.blockerId;
        },
        menuItems() {
            let menuItems = [];
            if (!this.blockedByAnotherUser) {
                if (this.container.attributes["container-type"] !== 'list') {
                    menuItems.push({ id: 1, label: this.$gettext('Abschnitt bearbeiten'), icon: 'edit', emit: 'editContainer' });
                }
                menuItems.push({ id: 2, label: this.$gettext('Abschnitt verändern'), icon: 'settings', emit: 'changeContainer' });
                menuItems.push({ id: 3, label: this.$gettext('Abschnitt löschen'), icon: 'trash', emit: 'deleteContainer' });
            }

            if (this.blocked && this.blockedByAnotherUser && this.userIsTeacher) {
                menuItems.push({
                    id: 4,
                    label: this.$gettext('Sperre aufheben'),
                    icon: 'lock-unlocked',
                    emit: 'removeLock',
                });
            }

            menuItems.sort((a, b) => {
                return a.id > b.id ? 1 : b.id > a.id ? -1 : 0;
            });
            return menuItems;
        },

    },
    methods: {
        menuAction(action) {
            this[action]();
        },
        editContainer() {
            this.$emit('editContainer');
        },
        changeContainer() {
            this.$emit('changeContainer');
        },
        deleteContainer() {
            this.$emit('deleteContainer');
        },
        removeLock() {
            this.$emit('removeLock');
        }
    },
};
</script>
