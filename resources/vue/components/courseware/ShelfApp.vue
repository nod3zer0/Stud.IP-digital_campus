<template>
    <div>
        <div class="cw-shelf">
            <courseware-unit-items />
            <courseware-shared-items v-if="!inCourseContext" />
        </div>
        <courseware-shelf-dialog-add-chooser v-if="showUnitAddDialog"/>
        <courseware-shelf-dialog-add v-if="showUnitNewDialog" />
        <courseware-shelf-dialog-copy v-if="showUnitCopyDialog" />
        <courseware-shelf-dialog-import v-if="showUnitImportDialog" />
        <courseware-shelf-dialog-topics v-if="showUnitTopicsDialog" />
        <MountingPortal v-if="userIsTeacher || !inCourseContext" mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-shelf-action-widget></courseware-shelf-action-widget>
        </MountingPortal>
        <courseware-companion-overlay />
    </div>
</template>

<script>
import CoursewareShelfActionWidget from './widgets/CoursewareShelfActionWidget.vue';
import CoursewareShelfDialogAdd from './unit/CoursewareShelfDialogAdd.vue';
import CoursewareShelfDialogAddChooser from './unit/CoursewareShelfDialogAddChooser.vue';
import CoursewareShelfDialogCopy from './unit/CoursewareShelfDialogCopy.vue';
import CoursewareShelfDialogImport from './unit/CoursewareShelfDialogImport.vue';
import CoursewareShelfDialogTopics from './unit/CoursewareShelfDialogTopics.vue';
import CoursewareUnitItems from './unit/CoursewareUnitItems.vue';
import CoursewareSharedItems from './unit/CoursewareSharedItems.vue';
import CoursewareCompanionOverlay from './layouts/CoursewareCompanionOverlay.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareShelfActionWidget,
        CoursewareShelfDialogAdd,
        CoursewareShelfDialogAddChooser,
        CoursewareShelfDialogCopy,
        CoursewareShelfDialogImport,
        CoursewareShelfDialogTopics,
        CoursewareUnitItems,
        CoursewareSharedItems,
        CoursewareCompanionOverlay,
    },
    computed: {
        ...mapGetters({
            showUnitAddDialog: 'showUnitAddDialog',
            showUnitCopyDialog: 'showUnitCopyDialog',
            showUnitImportDialog: 'showUnitImportDialog',
            showUnitLinkDialog: 'showUnitLinkDialog',
            showUnitNewDialog: 'showUnitNewDialog',
            showUnitTopicsDialog: 'showUnitTopicsDialog',
            licenses: 'licenses',
            context:'context',
            userIsTeacher: 'userIsTeacher',
            userId: 'userId'
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        }

    },
}
</script>
