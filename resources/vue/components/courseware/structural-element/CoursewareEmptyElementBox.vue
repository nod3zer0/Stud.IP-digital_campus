<template>
    <div class="cw-welcome-screen">
        <courseware-companion-box :msgCompanion="this.$gettext('Es wurden bisher noch keine Inhalte eingepflegt.')">
            <template v-slot:companionActions>
                <button v-if="canEdit && noContainers" class="button" @click="addContainer">
                    {{ $gettext('Einen Abschnitt hinzufügen') }}
                </button>
            </template>
        </courseware-companion-box>
    </div>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-empty-element-box',
    components: {
        CoursewareCompanionBox,
    },
    props: {
        canEdit: Boolean,
        noContainers: Boolean,
    },
    methods: {
        ...mapActions({
            coursewareConsumeMode: 'coursewareConsumeMode',
            coursewareContainerAdder: 'coursewareContainerAdder',
            coursewareSelectedToolbarItem: 'coursewareSelectedToolbarItem',
            coursewareShowToolbar: 'coursewareShowToolbar',
        }),
        addContainer() {
            this.coursewareConsumeMode(false);
            this.coursewareContainerAdder(true);
            this.coursewareSelectedToolbarItem('blockadder');
            this.coursewareShowToolbar(true);
        },
    },
};
</script>
