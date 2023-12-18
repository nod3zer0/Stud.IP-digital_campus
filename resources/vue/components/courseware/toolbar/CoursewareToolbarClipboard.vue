<template>
    <div class="cw-toolbar-clipboard">
        <courseware-collapsible-box :title="$gettext('Blöcke')" :open="clipboardBlocks.length > 0">
            <template v-if="clipboardBlocks.length > 0">
                <div class="cw-element-inserter-wrapper">
                    <draggable
                        class="cw-element-inserter-wrapper"
                        tag="div"
                        role="listbox"
                        v-model="clipboardBlocks"
                        handle=".cw-sortable-handle-clipboard"
                        :group="{ name: 'blocks', pull: 'clone', put: 'false' }"
                        :sort="false"
                        :clone="cloneClipboard"
                        :emptyInsertThreshold="20"
                        @end="dropClipboardBlock($event)"
                        ref="clipboardSortables"
                        sectionId="0"
                    >
                        <courseware-clipboard-item
                            v-for="(clipboard, index) in clipboardBlocks"
                            :key="index"
                            :clipboard="clipboard"
                            @inserted="$emit('blockAdded')"
                        />
                    </draggable>
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
                    <draggable
                        class="cw-element-inserter-wrapper"
                        tag="div"
                        role="listbox"
                        v-model="clipboardContainers"
                        handle=".cw-sortable-handle-clipboard"
                        :group="{ name: 'description', pull: 'clone', put: 'false' }"
                        :sort="false"
                        :emptyInsertThreshold="20"
                        :clone="cloneClipboardContainer"
                        @end="dropNewContainer($event)"
                        ref="clipboardContainerSortables"
                    >
                        <courseware-clipboard-item
                            v-for="(clipboard, index) in clipboardContainers"
                            :key="index"
                            :clipboard="clipboard"
                        />
                    </draggable>
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
import containerMixin from '@/vue/mixins/courseware/container.js';
import clipboardMixin from '@/vue/mixins/courseware/clipboard.js';
import draggable from 'vuedraggable';
import StudipDialog from '../../StudipDialog.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'cw-tools-blockadder',
    mixins: [containerMixin, clipboardMixin],
    components: {
        CoursewareClipboardItem,
        CoursewareCompanionBox,
        CoursewareCollapsibleBox,
        StudipDialog,
        draggable,
    },

    data() {
        return {
            showDeleteClipboardDialog: false,
            deleteClipboardType: null,
            isDragging: false,
        };
    },
    computed: {
        ...mapGetters({
            containerById: 'courseware-containers/byId',
            usersClipboards: 'courseware-clipboards/all',
            userId: 'userId',
        }),
        clipboardBlocks() {
            return this.usersClipboards
                .filter((clipboard) => clipboard.attributes['object-type'] === 'courseware-blocks')
                .sort((a, b) => b.attributes.mkdate - a.attributes.mkdate);
        },
        clipboardContainers() {
            return this.usersClipboards
                .filter((clipboard) => clipboard.attributes['object-type'] === 'courseware-containers')
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
        },
    },
    methods: {
        ...mapActions({
            companionWarning: 'companionWarning',
            deleteUserClipboards: 'deleteUserClipboards',
        }),
        clearClipboard(type) {
            this.deleteClipboardType = type;
            this.showDeleteClipboardDialog = true;
        },
        executeDeleteClipboard() {
            if (this.deleteClipboardType) {
                this.deleteUserClipboards({ uid: this.userId, type: this.deleteClipboardType });
            }
            this.closeDeleteClipboardDialog();
        },
        closeDeleteClipboardDialog() {
            this.showDeleteClipboardDialog = false;
            this.deleteClipboardType = null;
        },
        cloneClipboard(original) {
            original.attributes['block-type'] = original.attributes['object-kind'];
            original.attributes.payload = {};
            original.relationships = {
                'user-data-field': {
                    data: { id: null },
                },
                block: {},
            };
            return original;
        },
        async dropClipboardBlock(e) {
            const target = e.to.__vue__.$attrs;
            // only execute if dropped in destined list
            if (!target.containerId) {
                return;
            }
            // set chosen container and section and insert the clipboard block
            this.setAdderStorage({
                container: this.containerById({ id: target.containerId }),
                section: target.sectionId,
                position: e.newIndex,
            });
            await this.insertItem(e.item.__vue__._data.currentClipboard);
            this.resetAdderStorage();
        },
        cloneClipboardContainer(original) {
            original.newContainer = true;
            original.clipContainer = true;
            original.attributes['container-type'] = original.attributes['object-kind'];
            original.type = 'courseware-containers';
            original.attributes.payload = {};
            original.relationships = {};
            original.relationships.container = {};
            original.relationships.blocks = {};
            original.relationships.blocks.data = {};
            return original;
        },
        dropNewContainer(e) {
            // if the container is dropped back to its original list, do nothing / cancel the operation
            if (e.to.className === 'cw-containeradder-item-list' || e.to.className === 'cw-element-inserter-wrapper') {
                this.isDragging = false;
                return;
            }

            const item = e.item._underlying_vm_;

            // if the container is from the clipboard, insert it via clipboard mixin, else add it via container mixin
            if (item.clipContainer) {
                this.insertItem(e.item.__vue__._data.currentClipboard, e.newIndex);
            } else {
                const data = {
                    type: item.attributes['container-type'],
                    colspan: item.containerStyle,
                    sections: {
                        firstSection: item.firstSection,
                        secondSection: item.secondSection,
                    },
                    newPosition: e.newIndex,
                };
                this.addContainer(data);
            }
        },
    },
};
</script>
