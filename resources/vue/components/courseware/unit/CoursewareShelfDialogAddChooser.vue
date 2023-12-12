<template>
    <studip-dialog
        :title="$gettext('Lernmaterial hinzufügen')"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="320"
        width="680"
        @close="setShowUnitAddDialog(false)"
    >
        <template v-slot:dialogContent>
            <div class="square-button-panel">
                <studip-square-button
                    icon="add"
                    :title="$gettext('Neu erstellen')"
                    @click="selectType('new')"
                />
                <studip-square-button
                    v-if="inCourseContext"
                    icon="schedule"
                    :title="$gettext('Neu, Struktur aus Ablaufplan')"
                    @click="selectType('topics')"
                />
                <studip-square-button
                    icon="copy"
                    :title="$gettext('Bestehendes kopieren')"
                    @click="selectType('copy')"
                />
                <studip-square-button
                    icon="import"
                    :title="$gettext('Aus Datei importieren')"
                    @click="selectType('import')"
                />
            </div>
        </template>
    </studip-dialog>
</template>

<script>
import StudipSquareButton from './../../StudipSquareButton.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-shelf-dialog-add-chooser',
    components: {
        StudipSquareButton,
    },
    computed: {
        ...mapGetters({
            context: 'context',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        },
    },
    methods: {
        ...mapActions({
            setShowUnitAddDialog: 'setShowUnitAddDialog',
            setShowUnitCopyDialog: 'setShowUnitCopyDialog',
            setShowUnitImportDialog: 'setShowUnitImportDialog',
            setShowUnitLinkDialog: 'setShowUnitLinkDialog',
            setShowUnitNewDialog: 'setShowUnitNewDialog',
            setShowUnitTopicsDialog: 'setShowUnitTopicsDialog',
        }),
        selectType(type) {
            switch (type) {
                case 'new':
                    this.setShowUnitNewDialog(true);
                    break;
                case 'import':
                    this.setShowUnitImportDialog(true);
                    break;
                case 'copy':
                    this.setShowUnitCopyDialog(true);
                    break;
                case 'topics':
                    this.setShowUnitTopicsDialog(true);
            }
            this.setShowUnitAddDialog(false);
        },
    },
};
</script>
<style scoped lang="scss">
.square-button-panel {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    justify-content: center;
}
</style>
