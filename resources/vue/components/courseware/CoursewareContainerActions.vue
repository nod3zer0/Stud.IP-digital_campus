<template>
    <div v-if="canEdit" class="cw-container-actions">
        <studip-action-menu
            :items="menuItems"
            :context="container.attributes.title"
            @editContainer="editContainer"
            @deleteContainer="deleteContainer"
            @sortBlocks="sortBlocks"
        />
    </div>
</template>

<script>
export default {
    name: 'courseware-container-actions',
    props: {
        canEdit: Boolean,
        container: Object,
    },
    computed: {
        menuItems() {
            if (this.container.attributes["container-type"] === 'list') {
                return [
                    { id: 1, label: this.$gettext('Blöcke sortieren'), icon: 'arr_1sort', emit: 'sortBlocks' },
                    { id: 2, label: this.$gettext('Abschnitt löschen'), icon: 'trash', emit: 'deleteContainer' }
                ];
            } else {
                return [
                    { id: 1, label: this.$gettext('Abschnitt bearbeiten'), icon: 'edit', emit: 'editContainer' },
                    { id: 2, label: this.$gettext('Blöcke sortieren'), icon: 'arr_1sort', emit: 'sortBlocks' },
                    { id: 3, label: this.$gettext('Abschnitt löschen'), icon: 'trash', emit: 'deleteContainer' },
                ];
            }
        },
    },
    methods: {
        menuAction(action) {
            this[action]();
        },
        editContainer() {
            this.$emit('editContainer');
        },
        deleteContainer() {
            this.$emit('deleteContainer');
        },
        sortBlocks() {
            this.$emit('sortBlocks');
        }
    },
};
</script>
