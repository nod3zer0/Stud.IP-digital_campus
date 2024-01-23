<template>
    <studip-dialog
        :title="$gettext('Aufgabe löschen')"
        :question="$gettext('Möchten Sie die Aufgabe wirklich löschen?')"
        height="200"
        @close="onClose"
        @confirm="onConfirm"
    >
    </studip-dialog>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    props: ['taskGroup'],
    methods: {
        ...mapActions({
            deleteTaskGroup: 'courseware-task-groups/delete',
            setShowTaskGroupsDeleteDialog: 'tasks/setShowTaskGroupsDeleteDialog'
        }),
        onClose() {
            this.setShowTaskGroupsDeleteDialog(false);
        },
        onConfirm() {
            this.deleteTaskGroup(this.taskGroup).then(() => {
                this.onClose();
                this.$router.push({ name: 'task-groups-index' });
            });
        },
    },
};
</script>
