<template>
    <studip-dialog
        :title="$gettext('Seite hinzufügen')"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="320"
        width="680"
        @close="showElementAddChooserDialog(false)"
    >
        <template v-slot:dialogContent>
            <div class="square-button-panel">
                <studip-square-button
                    icon="add"
                    :title="$gettext('Neu erstellen')"
                    @click="selectType('new')"
                />
                <studip-square-button
                    icon="copy"
                    :title="$gettext('Bestehendes kopieren')"
                    @click="selectType('copy')"
                />
                <studip-square-button
                    v-if="inCourseContext && userIsTeacher"
                    icon="copy"
                    :title="$gettext('Aus Arbeitsplatz verknüpfen')"
                    @click="selectType('link')"
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
            userIsTeacher: 'userIsTeacher',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        },
    },
    methods: {
        ...mapActions({
            showElementAddDialog: 'showElementAddDialog',
            showElementAddChooserDialog: 'showElementAddChooserDialog',
            showElementImportDialog: 'showElementImportDialog',
            showElementCopyDialog: 'showElementCopyDialog',
            showElementLinkDialog: 'showElementLinkDialog',
        }),
        selectType(type) {
            switch (type) {
                case 'new':
                    this.showElementAddDialog(true);
                    break;
                case 'import':
                    this.showElementImportDialog(true);
                    break;
                case 'copy':
                    this.showElementCopyDialog(true);
                    break;
                case 'link':
                        this.showElementLinkDialog(true)
            }
            this.showElementAddChooserDialog(false);
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
