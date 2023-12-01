<template>
    <div class="cw-toolbar-clipboard">
        <courseware-collapsible-box :title="$gettext('Blöcke')" :open="clipboardBlocks.length > 0">
            <template v-if="clipboardBlocks.length > 0">
                <div class="cw-element-inserter-wrapper">
                    <courseware-clipboard-item
                        v-for="(clipboard, index) in clipboardBlocks"
                        :key="index"
                        :clipboard="clipboard"
                        @inserted="$emit('blockAdded')"
                    />
                </div>
                <button class="button trash" @click="clearClipboard('courseware-blocks')">
                    {{ $gettext('Alle Blöcke aus Merkliste entfernen') }}
                </button>
            </template>
            <courseware-companion-box
                v-else
                mood="pointing"
                :msgCompanion="$gettext('Die Merkliste enthält keine Blöcke.')"
            />
        </courseware-collapsible-box>
        <courseware-collapsible-box :title="$gettext('Abschnitte')" :open="clipboardContainers.length > 0">
            <template v-if="clipboardContainers.length > 0">
                <div class="cw-element-inserter-wrapper">
                    <courseware-clipboard-item
                        v-for="(clipboard, index) in clipboardContainers"
                        :key="index"
                        :clipboard="clipboard"
                    />
                </div>
                <button class="button trash" @click="clearClipboard('courseware-containers')">
                    {{ $gettext('Alle Abschnitte aus Merkliste entfernen') }}
                </button>
            </template>
            <courseware-companion-box
                v-else
                mood="pointing"
                :msgCompanion="$gettext('Die Merkliste enthält keine Abschnitte.')"
            />
        </courseware-collapsible-box>
        <studip-dialog
            v-if="showDeleteClipboardDialog"
            :title="textDeleteClipboardTitle"
            :question="textDeleteClipboardAlert"
            height="200"
            width="500"
            @confirm="executeDeleteClipboard"
            @close="closeDeleteClipboardDialog"
        ></studip-dialog>

    </div>
</template>

<script>
import CoursewareClipboardItem from './CoursewareClipboardItem.vue';
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareCollapsibleBox from '../layouts/CoursewareCollapsibleBox.vue';
import StudipDialog from '../../StudipDialog.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'cw-tools-blockadder',
    components: {
        CoursewareClipboardItem,
        CoursewareCompanionBox,
        CoursewareCollapsibleBox,
        StudipDialog,
    },

    data() {
        return {
            showDeleteClipboardDialog: false,
            deleteClipboardType: null
        };
    },
    computed: {
        ...mapGetters({
            usersClipboards: 'courseware-clipboards/all',
            userId: 'userId'
        }),
        clipboardBlocks() {
            return this.usersClipboards
                .filter(clipboard => clipboard.attributes['object-type'] === 'courseware-blocks')
                .sort((a, b) => b.attributes.mkdate - a.attributes.mkdate);
        },
        clipboardContainers() {
            return this.usersClipboards
                .filter(clipboard => clipboard.attributes['object-type'] === 'courseware-containers')
                .sort((a, b) => b.attributes.mkdate < a.attributes.mkdate);
        },
        textDeleteClipboardTitle() {
            if (this.deleteClipboardType === 'courseware-blocks') {
                return this.$gettext('Merkliste für Blöcke leeren');
            }
            if (this.deleteClipboardType === 'courseware-containers') {
                return this.$gettext('Merkliste für Abschnitte leeren');
            }
            return '';
        },
        textDeleteClipboardAlert() {
            if (this.deleteClipboardType === 'courseware-blocks') {
                return this.$gettext('Möchten Sie die Merkliste für Blöcke unwiderruflich leeren?');
            }
            if (this.deleteClipboardType === 'courseware-containers') {
                return this.$gettext('Möchten Sie die Merkliste für Abschnitte unwiderruflich leeren?');
            }
            return '';
        }
    },
    methods: {
        ...mapActions({
            companionWarning: 'companionWarning',
            deleteUserClipboards: 'deleteUserClipboards'
        }),
        clearClipboard(type) {
            this.deleteClipboardType = type;
            this.showDeleteClipboardDialog = true;
        },
        executeDeleteClipboard() {
            if (this.deleteClipboardType) {
                this.deleteUserClipboards({uid: this.userId, type: this.deleteClipboardType});
            }
            this.closeDeleteClipboardDialog();
        },
        closeDeleteClipboardDialog() {
            this.showDeleteClipboardDialog = false;
            this.deleteClipboardType = null;
        }
    }
};

</script>