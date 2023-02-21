<template>
    <div>
        <div class="cw-shelf">
            <courseware-unit-items />
            <courseware-shared-items v-if="!inCourseContext" />
        </div>
        <courseware-shelf-dialog-add v-if="showUnitAddDialog" />
        <courseware-shelf-dialog-copy v-if="showUnitCopyDialog" />
        <courseware-shelf-dialog-import v-if="showUnitImportDialog" />
        <MountingPortal v-if="userIsTeacher || !inCourseContext" mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-shelf-action-widget></courseware-shelf-action-widget>
        </MountingPortal>
        <MountingPortal v-if="userIsTeacher || !inCourseContext" mountTo="#courseware-import-widget" name="sidebar-imports">
            <courseware-shelf-import-widget></courseware-shelf-import-widget>
        </MountingPortal>
        <courseware-companion-overlay />
    </div>
</template>

<script>
import CoursewareShelfActionWidget from './CoursewareShelfActionWidget.vue';
import CoursewareShelfImportWidget from './CoursewareShelfImportWidget.vue';
import CoursewareShelfDialogAdd from './CoursewareShelfDialogAdd.vue';
import CoursewareShelfDialogCopy from './CoursewareShelfDialogCopy.vue';
import CoursewareShelfDialogImport from './CoursewareShelfDialogImport.vue';
import CoursewareUnitItems from './CoursewareUnitItems.vue';
import CoursewareSharedItems from './CoursewareSharedItems.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareShelfActionWidget,
        CoursewareShelfImportWidget,
        CoursewareShelfDialogAdd,
        CoursewareShelfDialogCopy,
        CoursewareShelfDialogImport,
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
            licenses: 'licenses',
            context:'context',
            userIsTeacher: 'userIsTeacher',
            userId: 'userId'
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        }

    },
    methods: {
        ...mapActions({
            setShowUnitAddDialog: 'setShowUnitAddDialog',
            setShowUnitCopyDialog: 'setShowUnitCopyDialog',
            setShowUnitImportDialog: 'setShowUnitImportDialog',
            setShowUnitLinkDialog: 'setShowUnitLinkDialog',
        }),
    },
}
</script>
