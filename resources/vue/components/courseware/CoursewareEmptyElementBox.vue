<template>
    <div class="cw-welcome-screen">
        <courseware-companion-box :msgCompanion="this.$gettext('Es wurden bisher noch keine Inhalte eingepflegt.')">
            <template v-slot:companionActions>
                <button v-if="canEdit && noContainers" class="button" @click="addContainer"><translate>Einen Abschnitt hinzufügen</translate></button>
                <button v-if="canEdit && !noContainers && !editMode" class="button" @click="switchToEditView"><translate>Seite bearbeiten</translate></button>
            </template>
        </courseware-companion-box>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-empty-element-box',
    components: {
        CoursewareCompanionBox,
    },
    props: {
        canEdit: Boolean,
        noContainers: Boolean
    },
    data() {
        return{}
    },
    computed: {
        ...mapGetters({
            viewMode: 'viewMode'
        }),
        editMode() {
            return this.viewMode === 'edit';
        }
    },
    methods: {
        ...mapActions({
            coursewareViewMode: 'coursewareViewMode',
            coursewareConsumeMode: 'coursewareConsumeMode',
            coursewareContainerAdder: 'coursewareContainerAdder',
            coursewareSelectedToolbarItem: 'coursewareSelectedToolbarItem',
            coursewareShowToolbar: 'coursewareShowToolbar'
        }),
        addContainer() {
            this.coursewareViewMode('edit');
            this.coursewareConsumeMode(false);
            this.coursewareContainerAdder(true);
            this.coursewareSelectedToolbarItem('blockadder');
            this.coursewareShowToolbar(true);
        },
        switchToEditView() {
            this.coursewareViewMode('edit');
            this.coursewareConsumeMode(false);
        }
    }

}
</script>