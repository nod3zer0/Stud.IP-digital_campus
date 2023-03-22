<template>
    <courseware-default-container
        :container="container"
        containerClass="cw-container-tabs"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        @showEdit="setShowEdit"
        @storeContainer="storeContainer"
        @closeEdit="initCurrentData"
    >
        <template v-slot:containerContent>
            <template v-if="showEditMode && canEdit && !currentElementisLink">
                <span aria-live="assertive" class="assistive-text">{{ assistiveLive }}</span>
                <span id="operation" class="assistive-text">
                    {{$gettext('Drücken Sie die Leertaste, um neu anzuordnen.')}}
                </span>
            </template>
            <courseware-tabs>
                <courseware-tab
                    v-for="(section, index) in currentSections"
                    :key="index"
                    :index="index"
                    :name="section.name"
                    :icon="section.icon"
                    :selected="sortInTab === index"
                >
                    <ul v-if="!showEditMode || currentElementisLink" class="cw-container-tabs-block-list">
                        <li v-for="block in section.blocks" :key="block.id" class="cw-block-item">
                            <component
                                :is="component(block)"
                                :block="block"
                                :canEdit="canEdit"
                                :isTeacher="isTeacher"
                            />
                        </li>
                    </ul>
                    <template v-else>
                        <template v-if="canEdit">
                            <draggable
                                class="cw-container-list-block-list cw-container-list-sort-mode"
                                :class="[section.blocks.length === 0 ? 'cw-container-list-sort-mode-empty' : '']"
                                tag="ol"
                                role="listbox"
                                v-model="section.blocks"
                                v-bind="dragOptions"
                                handle=".cw-sortable-handle"
                                group="blocks"
                                @start="isDragging = true"
                                @end="dropBlock"
                                :containerId="container.id"
                                :sectionId="index"
                            >
                                <li v-for="block in section.blocks" :key="block.id" class="cw-block-item cw-block-item-sortable">
                                    <span
                                        :class="{ 'cw-sortable-handle-dragging': isDragging }"
                                        class="cw-sortable-handle"
                                        tabindex="0"
                                        role="option"
                                        aria-describedby="operation"
                                        :ref="'sortableHandle' + block.id"
                                        @keydown="keyHandler($event, block.id, index)"
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
                            <template v-if="canAddElements">
                                <courseware-block-adder-area :container="container" :section="index" @updateContainerContent="updateContent"/>
                            </template>
                        </template>
                    </template>
                </courseware-tab>
            </courseware-tabs>
        </template>
        <template v-slot:containerEditDialog>
            <form class="default cw-container-dialog-edit-form" @submit.prevent="">
                <fieldset v-for="(section, index) in currentContainer.attributes.payload.sections.filter(section => !section.locked)" :key="index">
                    <label>
                        <translate>Title</translate>
                        <input type="text" v-model="section.name" />
                    </label>
                    <label>
                        <translate>Icon</translate>
                        <studip-select :options="icons" v-model="section.icon">
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options>
                                <translate>Es steht keine Auswahl zur Verfügung.</translate>
                            </template>
                            <template #selected-option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                            <template #option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                        </studip-select>
                    </label>
                    <label
                        class="cw-container-section-delete"
                        v-if="currentContainer.attributes.payload.sections.length > 1"
                    >
                    <button class="button trash" @click="deleteSection(index)"><translate>Tab löschen</translate></button>
                    </label>
                </fieldset>
            </form>
            <button class="button add" @click="addSection"><translate>Tab hinzufügen</translate></button>
        </template>
    </courseware-default-container>
</template>

<script>
import ContainerComponents from './container-components.js';
import containerMixin from '../../mixins/courseware/container.js';
import contentIcons from './content-icons.js';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import StudipIcon from './../StudipIcon.vue';

import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-tabs-container',
    mixins: [containerMixin],
    components: Object.assign(ContainerComponents, {
        CoursewareTabs,
        CoursewareTab,
        CoursewareCollapsibleBox,
        StudipIcon,
    }),
    props: {
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
        canAddElements: Boolean,
    },
    data() {
        return {
            showEdit: false,
            currentContainer: null,
            currentSections: [],
            unallocatedBlocks: [],
            textDeleteSection: this.$gettext('Sektion entfernen'),
            selectAttributes: {'ref': 'openIndicator', 'role': 'presentation', 'class': 'vs__open-indicator'},
            sortMode: false,
            isDragging: false,
            dragOptions: {
                animation: 0,
                group: this.container.id,
                disabled: false,
                ghostClass: "block-ghost"
            },
            processing: false,
            keyboardSelected: null,
            sortInTab: 0,
            assistiveLive: ''
        };
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
            viewMode: 'viewMode',
            currentElementisLink: 'currentElementisLink'
        }),
        showEditMode() {
            return this.viewMode === 'edit';
        },
        blocks() {
            if (!this.container) {
                return [];
            }

            return this.container.relationships.blocks.data.map(({ id }) => this.blockById({ id })).filter((a) => a);
        },
        icons() {
            return contentIcons;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateContainer: 'updateContainer',
            loadContainer: 'courseware-containers/loadById',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
        }),
        initCurrentData() {
            this.currentContainer = _.cloneDeep(this.container);

            let view = this;
            let sections = this.currentContainer.attributes.payload.sections;

            const unallocated = new Set(this.blocks.map(({ id }) => id));

            for (let section of sections) {
                section.locked = false;
                section.blocks = section.blocks.map((id) =>  view.blockById({id})).filter(Boolean);
                for (let sectionBlock of section.blocks) {
                    if (sectionBlock?.id && unallocated.has(sectionBlock.id)) {
                        unallocated.delete(sectionBlock.id);
                    }
                }
            }

            if (unallocated.size > 0) {
                this.unallocatedBlocks = [...unallocated].map((id) => view.blockById({ id }));
                sections.push({
                    blocks: this.unallocatedBlocks,
                    name: this.$gettext('nicht zugewiesene Inhalte'),
                    icon: 'decline',
                    locked: true
                });
            }

            this.currentSections = sections;
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
        addSection() {
            this.currentContainer.attributes.payload.sections.push({ name: '', icon: '', blocks: [] });
        },
        deleteSection(index) {
            if (this.currentContainer.attributes.payload.sections.length === 1) {
                return;
            }
            if (this.currentContainer.attributes.payload.sections[index].blocks.length > 0) {
                if (index === 0) {
                    this.currentContainer.attributes.payload.sections[
                        index + 1
                    ].blocks = this.currentContainer.attributes.payload.sections[index + 1].blocks.concat(
                        this.currentContainer.attributes.payload.sections[index].blocks
                    );
                } else {
                    this.currentContainer.attributes.payload.sections[
                        index - 1
                    ].blocks = this.currentContainer.attributes.payload.sections[index - 1].blocks.concat(
                        this.currentContainer.attributes.payload.sections[index].blocks
                    );
                }
            }
            this.currentContainer.attributes.payload.sections.splice(index, 1);
        },
        async storeContainer() {
            const timeout = setTimeout(() => this.processing = true, 800);
            this.currentContainer.attributes.payload.sections = this.currentContainer.attributes.payload.sections.filter(section => !section.locked);
            this.currentContainer.attributes.payload.sections.forEach(section => {
                section.blocks = section.blocks.map((block) => {return block.id;});
                delete section.locked;
            });
            await this.updateContainer({
                container: this.currentContainer,
                structuralElementId: this.currentContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            await this.loadContainer({id : this.container.id });
            this.initCurrentData();
            clearTimeout(timeout);
            this.processing = false;
        },
        async storeSort() {
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });
                this.loadContainer({id : this.container.id });
                return false;
            }
            await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            this.storeContainer();
        },
        component(block) {
            if (block.attributes) {
                return 'courseware-' + block.attributes["block-type"] + '-block';
            }
            return null;
        },
        updateContent(blockAdder) {
            if(blockAdder.container !== undefined && blockAdder.container.id === this.container.id) {
                this.initCurrentData();
            }
        },
        keyHandler(e, blockId, sectionIndex) {
            switch (e.keyCode) {
                case 27: // esc
                    this.abortKeyboardSorting(blockId, sectionIndex);
                    break;
                case 32: // space
                    e.preventDefault();
                    if (this.keyboardSelected) {
                        this.storeKeyboardSorting(blockId, sectionIndex);
                    } else {
                        this.keyboardSelected = blockId;
                        const block = this.blockById({id: blockId});
                        const currentIndex = this.currentSections[sectionIndex].blocks.findIndex(block => block.id === blockId);
                        this.assistiveLive =
                            this.$gettextInterpolate(
                                this.$gettext('%{blockTitle} Block ausgewählt. Aktuelle Position in der Liste: %{pos} von %{listLength}. Drücken Sie die Aufwärts- und Abwärtspfeiltasten, um die Position zu ändern, die Leertaste zum Ablegen, die Escape-Taste zum Abbrechen.')
                                , {blockTitle: block.attributes.title, pos: currentIndex + 1, listLength: this.currentSections[sectionIndex].blocks.length}
                            );
                    }
                    break;
            }
            if (this.keyboardSelected) {
                switch (e.keyCode) {
                    case 9: //tab
                        this.abortKeyboardSorting(blockId, sectionIndex);
                        break;
                    case 38: // up
                        e.preventDefault();
                        this.moveItemUp(blockId, sectionIndex);
                        break;
                    case 40: // down
                        e.preventDefault();
                        this.moveItemDown(blockId, sectionIndex);
                        break;
                }
            }
        },
        moveItemUp(blockId, sectionIndex) {
            const currentIndex = this.currentSections[sectionIndex].blocks.findIndex(block => block.id === blockId);
            const block = this.blockById({id: blockId});
            if (currentIndex !== 0) {
                const newPos = currentIndex - 1;
                this.currentSections[sectionIndex].blocks.splice(newPos, 0, this.currentSections[sectionIndex].blocks.splice(currentIndex, 1)[0]);
                this.assistiveLive =
                    this.$gettextInterpolate(
                        this.$gettext('%{blockTitle} Block. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {blockTitle: block.attributes.title, pos: newPos + 1, listLength: this.currentSections[sectionIndex].blocks.length}
                    );
            } else if (sectionIndex !== 0) {
                const newSectionIndex = sectionIndex - 1;
                this.sortInTab = newSectionIndex;
                this.currentSections[newSectionIndex].blocks.push(this.currentSections[sectionIndex].blocks.splice(currentIndex, 1)[0]);
            }
        },
        moveItemDown(blockId, sectionIndex) {
            const currentIndex = this.currentSections[sectionIndex].blocks.findIndex(block => block.id === blockId);
            const block = this.blockById({id: blockId});
            if (this.currentSections[sectionIndex].blocks.length - 1 > currentIndex) {
                const newPos = currentIndex + 1;
                this.currentSections[sectionIndex].blocks.splice(newPos, 0, this.currentSections[sectionIndex].blocks.splice(currentIndex, 1)[0]);
                this.assistiveLive =
                    this.$gettextInterpolate(
                        this.$gettext('%{blockTitle} Block. Aktuelle Position in der Liste: %{pos} von %{listLength}.')
                        , {blockTitle: block.attributes.title, pos: newPos + 1, listLength: this.currentSections[sectionIndex].blocks.length}
                    );
            } else if (this.currentSections.length - 1 > sectionIndex) {
                const newSectionIndex = sectionIndex + 1;
                this.sortInTab = newSectionIndex;
                this.currentSections[newSectionIndex].blocks.splice(0, 0, this.currentSections[sectionIndex].blocks.splice(currentIndex, 1)[0]);
            }
        },
        abortKeyboardSorting(blockId, sectionIndex) {
            const block = this.blockById({id: blockId});
            this.keyboardSelected = null;
            this.assistiveLive =
                this.$gettextInterpolate(
                    this.$gettext('%{blockTitle} Block, Neuordnung abgebrochen.')
                    , {blockTitle: block.attributes.title}
                );
            this.initCurrentData();
        },
        storeKeyboardSorting(blockId, sectionIndex) {
            const block = this.blockById({id: blockId});
            const currentIndex = this.currentSections[sectionIndex].blocks.findIndex(block => block.id === blockId);
            this.keyboardSelected = null;
            this.assistiveLive =
                this.$gettextInterpolate(
                    this.$gettext('%{blockTitle} Block, abgelegt. Endgültige Position in der Liste: %{pos} von %{listLength}.')
                    , {blockTitle: block.attributes.title, pos: currentIndex + 1, listLength: this.currentSections[sectionIndex].blocks.length}
                );
            this.storeSort();
        }
    },
    watch: {
        blocks(newBlocks, oldBlocks) {
            if (!this.showEdit && !this.checkSimpleArrayEquality(newBlocks, oldBlocks)) {
                this.$nextTick(() => {
                    setTimeout(() =>  this.initCurrentData(), 250);
                });
            }
        },
        currentSections: {
            handler() {
                if (this.keyboardSelected) {
                    this.$nextTick(() => {
                        this.$refs['sortableHandle' + this.keyboardSelected][0].focus();
                    });
                }
            },
            deep: true
        }
    }
};
</script>
