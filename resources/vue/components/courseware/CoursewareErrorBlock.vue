<template>
    <div class="cw-block cw-block-error">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :deleteOnly="true"
            :isTeacher="isTeacher"
            :preview="false"
            :defaultGrade="false"
        >
            <template #content>
                <div class="cw-block-error-content">
                    <courseware-companion-box 
                        mood="sad"
                        :msgCompanion="errorMessage"
                    >
                    </courseware-companion-box>
                </div>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { blockMixin } from './block-mixin.js';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-error-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareCompanionBox,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    computed: {
        originalBlockType() {
            return this.block?.attributes?.payload?.original_block_type;
        },
        errorMessage() {
            let message = '<b>'
            message += this.$gettext('Es ist ein Fehler aufgetretten! Der Block-Typ dieses Blocks ist nicht verfügbar.');
            message += '</b><br>'
            message += 'block_type: ' + this.originalBlockType + ' not found';

            return message;
        }
    },

};
</script>
