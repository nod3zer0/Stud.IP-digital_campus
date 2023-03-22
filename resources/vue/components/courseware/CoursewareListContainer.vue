<template>
    <courseware-default-container
        :container="container"
        containerClass="cw-container-list"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        @storeContainer="storeContainer"
    >
        <template v-slot:containerContent>
            <ul v-if="!showEditMode || currentElementisLink"  class="cw-container-list-block-list">
                <li v-for="block in blocks" :key="block.id" class="cw-block-item">
                    <component :is="component(block)" :block="block" :canEdit="canEdit" :isTeacher="isTeacher" />
                </li>
            </ul>
            <template v-else>
                <template v-if="!processing">
                    <span aria-live="assertive" class="assistive-text">{{ assistiveLive }}</span>
                    <span id="operation" class="assistive-text">
                        {{$gettext('Drücken Sie die Leertaste, um neu anzuordnen.')}}
                    </span>
                    <draggable
                        v-if="showEditMode && canEdit"
                        class="cw-container-list-block-list cw-container-list-sort-mode"
                        tag="ol"
                        role="listbox"
                        v-model="blockList"
                        v-bind="dragOptions"
                        handle=".cw-sortable-handle"
                        group="blocks"
                        @start="isDragging = true"
                        @end="dropBlock"
                        ref="sortables"
                        :containerId="container.id"
                        sectionId="0"
                    >
                        <li
                            v-for="block in blockList"
                            :key="block.id"
                            class="cw-block-item cw-block-item-sortable"
                        >
                            <span
                                :class="{ 'cw-sortable-handle-dragging': isDragging }"
                                class="cw-sortable-handle"
                                tabindex="0"
                                role="option"
                                aria-describedby="operation"
                                :ref="'sortableHandle' + block.id"
                                @keydown="keyHandler($event, block.id)"
                            ></span>
                            <component
                                :is="component(block)"
                                :block="block"
                                :canEdit="canEdit"
                                :isTeacher="isTeacher"
                                :class="{ 'cw-block-item-selected': keyboardSelected === block.id}"
                                :blockId="block.id"
                            />
                        </li>
                    </draggable>
                    <courseware-block-adder-area :container="container" :section="0" />
                </template>
                <div v-else class="progress-wrapper" :style="{ height: contentHeight + 'px' }">
                    <studip-progress-indicator :description="$gettext('Vorgang wird bearbeitet...')" />
                </div>
            </template>
        </template>
    </courseware-default-container>
</template>

<script>
import ContainerComponents from './container-components.js';
import containerMixin from '../../mixins/courseware/container.js';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import draggable from 'vuedraggable';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-list-container',
    mixins: [containerMixin],
    components: Object.assign(ContainerComponents, {
        draggable,
        StudipProgressIndicator
    }),
    props: {
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
        canAddElements: Boolean,
    },
    data() {
        return {
            isDragging: false,
            dragOptions: {
                animation: 0,
                group: this.container.id,
                disabled: false,
                ghostClass: "block-ghost"
            },
            blockList: [],
            processing: false,
            contentHeight: 0,
            keyboardSelected: null,
            assistiveLive: ''
        };
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
            containerById: 'courseware-containers/byId',
            viewMode: 'viewMode',
            currentElementisLink: 'currentElementisLink'
        }),
        blocked() {
            return this.container?.relationships?.['edit-blocker']?.data !== null;
        },
        blockerId() {
            return this.blocked ? this.container?.relationships?.['edit-blocker']?.data?.id : null;
        },
        blockedByThisUser() {
            return this.blocked && this.userId === this.blockerId;
        },
        blockedByAnotherUser() {
            return this.blocked && this.userId !== this.blockerId;
        },
        showEditMode() {
            return this.viewMode === 'edit';
        },
        blocks() {
            if (!this.container) {
                return [];
            }
            let containerBlocks = this.container.relationships.blocks.data.map(({ id }) => this.blockById({ id })).filter(Boolean);
            let unallocated = new Set(containerBlocks.map(({ id }) => id));
            let sortedBlocks = this.container.attributes.payload.sections[0].blocks.map((id) => this.blockById({ id })).filter(Boolean);
            sortedBlocks.forEach(({ id }) => unallocated.delete(id));
            let unallocatedBlocks = [...unallocated].map((id) => this.blockById({ id }));

            return sortedBlocks.concat(unallocatedBlocks);
        },
    },
    methods: {
        ...mapActions({
            updateContainer: 'updateContainer',
            loadContainer: 'courseware-containers/loadById',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            companionInfo: 'companionInfo'
        }),
        storeContainer(data) {
        },
        initCurrentData() {
            this.blockList = this.blocks;
        },
        async storeSort() {
            this.contentHeight = this.$refs.sortables.$el.offsetHeight;
            const timeout = setTimeout(() => this.processing = true, 800);
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });
                clearTimeout(timeout);
                this.processing = false;
                this.loadContainer({id : this.container.id });
                return false;
            }
            await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            let currentContainer = this.container;
            currentContainer.attributes.payload.sections[0].blocks = this.blockList.map(block => {return block.id});
            await this.updateContainer({
                container: currentContainer,
                structuralElementId: currentContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            await this.loadContainer({id : this.container.id });
            this.initCurrentData();
            clearTimeout(timeout);
            this.processing = false;
            this.assistiveLive = '';
        },
        component(block) {
            if (block.attributes["block-type"] !== undefined) {
                return 'courseware-' + block.attributes["block-type"] + '-block';
            }
            return null;
        },
        keyHandler(e, blockId) {
            switch (e.keyCode) {
                case 27: // esc
                    this.abortKeyboardSorting(blockId);
                    break;
                case 32: // space
                    e.preventDefault();
                    if (this.keyboardSelected) {
                        this.storeKeyboardSorting(blockId);
                    } else {
                        this.keyboardSelected = blockId;
                        const block = this.blockById({id: blockId});
                        const index = this.blockList.findIndex(b => b.id === block.id);
                        this.assistiveLive = 
                            this.$gettextInterpolate(
                                this.$gettext('%{blockTitle} Block ausgewählt. Aktuelle Position in der Liste: %{pos} von %{listLength}. Drücken Sie die Aufwärts- und Abwärtspfeiltasten, um die Position zu ändern, die Leertaste zum Ablegen, die Escape-Taste zum Abbrechen.')
                                , {blockTitle: block.attributes.title, pos: index + 1, listLength: this.blockList.length}
                            );
                    }
                    break;
            }
            if (this.keyboardSelected) {
                switch (e.keyCode) {
                    case 9: //tab
                        this.abortKeyboardSorting(blockId);
                        break;
                    case 38: // up
                        e.preventDefault();
                        this.moveItemUp(blockId);
                        break;
                    case 40: // down
                        e.preventDefault();
                        this.moveItemDown(blockId);
                        break;
                }
            }
        },
        moveItemUp(blockId) {
            const currentIndex = this.blockList.findIndex(block => block.id === blockId);
            if (currentIndex !== 0) {
                const block = this.blockById({id: blockId});
                const newPos = currentIndex - 1;
                this.blockList.splice(newPos, 0, this.blockList.splice(currentIndex, 1)[0]);
                this.assistiveLive = 
                    this.$gettextInterpolate(
                        this.$gettext('%{blockTitle} Block. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {blockTitle: block.attributes.title, pos: newPos + 1, listLength: this.blockList.length}
                    );
            }
        },
        moveItemDown(blockId) {
            const currentIndex = this.blockList.findIndex(block => block.id === blockId);
            if (this.blockList.length - 1 > currentIndex) {
                const block = this.blockById({id: blockId});
                const newPos = currentIndex + 1;
                this.blockList.splice(newPos, 0, this.blockList.splice(currentIndex, 1)[0]);
                this.assistiveLive = 
                    this.$gettextInterpolate(
                        this.$gettext('%{blockTitle} Block. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {blockTitle: block.attributes.title, pos: newPos + 1, listLength: this.blockList.length}
                    );
            }
        },
        abortKeyboardSorting(blockId) {
            const block = this.blockById({id: blockId});
            this.keyboardSelected = null;
            this.assistiveLive = 
                this.$gettextInterpolate(
                    this.$gettext('%{blockTitle} Block, Neuordnung abgebrochen.')
                    , {blockTitle: block.attributes.title}
                );
            this.initCurrentData();
        },
        storeKeyboardSorting(blockId) {
            const block = this.blockById({id: blockId});
            const currentIndex = this.blockList.findIndex(block => block.id === blockId);
            this.keyboardSelected = null;
            this.assistiveLive = 
                this.$gettextInterpolate(
                    this.$gettext('%{blockTitle} Block, abgelegt. Endgültige Position in der Liste: %{pos} von %{listLength}.')
                    , {blockTitle: block.attributes.title, pos: currentIndex + 1, listLength: this.blockList.length}
                );
            this.storeSort();
        }
    },
    mounted() {
        this.initCurrentData();
    },
    watch: {
        blocks() {
            this.initCurrentData();
        },
        blockList() {
            if (this.keyboardSelected) {
                this.$nextTick(() => {
                    const selected = this.$refs['sortableHandle' + this.keyboardSelected][0];
                    selected.focus();
                    selected.scrollIntoView({behavior: "smooth", block: "center"});
                });
            }
        }
    }
};
</script>
