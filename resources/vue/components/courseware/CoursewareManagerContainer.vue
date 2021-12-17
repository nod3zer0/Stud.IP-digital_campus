<template>
    <div class="cw-manager-container">
        <div
            :class="{ 'cw-manager-container-clickable-title': inserter }"
            class="cw-manager-container-title"
            @click="clickItem"
        >
            <span v-if="inserter">
                <studip-icon shape="arr_2left" size="16" role="sort" />
            </span>
            {{ container.attributes.title }} ({{container.attributes.width}})
            <div v-if="sortContainers" class="cw-manager-container-buttons">
                <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveUp}" shape="arr_2up" role="sort" @click="moveUp" />
                <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveDown}" shape="arr_2down" role="sort" @click="moveDown" />
            </div>
        </div>
        <courseware-collapsible-box :open="false" :title="$gettext('Blöcke')" class="cw-manager-container-blocks">
            <div v-if="canSortChildren">
                <button v-show="!sortBlocksActive && isCurrent" class="button sort" @click="sortBlocks">
                    <translate>Blöcke sortieren</translate>
                </button>
                <button v-show="sortBlocksActive && isCurrent" class="button accept" @click="storeBlocksSort">
                    <translate>Sortieren beenden</translate>
                </button>
                <button v-show="sortBlocksActive && isCurrent" class="button cancel" @click="resetBlocksSort">
                    <translate>Sortieren abbrechen</translate>
                </button>
            </div>
            <p v-if="!hasChildren">
                <translate>Dieser Abschnitt enthält keine Blöcke.</translate>
            </p>
            <div v-else-if="sectionsWithBlocksCurrentState.length === 1">
                <transition-group name="cw-sort-ease" tag="div">
                    <courseware-manager-block
                        v-for="(block, blockIndex) in sectionsWithBlocksCurrentState[0].blocks"
                        :key="block.id"
                        :block="block"
                        :inserter="blockInserter"
                        :sortBlocks="sortBlocksActive"
                        :canMoveUp="blockIndex !== 0"
                        :canMoveDown="blockIndex + 1 !== sectionsWithBlocksCurrentState[0].blocks.length"
                        :elementType="elementType"
                        :sectionId="0"
                        @insertBlock="insertBlock"
                        @moveUp="moveBlockUp"
                        @moveDown="moveBlockDown"
                    />
                </transition-group>
            </div>
            <div v-else>
                <courseware-collapsible-box
                    v-for="(section, index) in sectionsWithBlocksCurrentState"
                    :key="section.id"
                    :open="true"
                    :title="section.name"
                    class="cw-manager-container-blocks"
                >
                    <transition-group name="cw-sort-ease" tag="div">
                        <courseware-manager-block
                            v-for="(block, blockIndex) in sectionsWithBlocksCurrentState[index].blocks"
                            :key="block.id"
                            :block="block"
                            :inserter="blockInserter"
                            :sortBlocks="sortBlocksActive"
                            :canMoveUp="blockIndex !== 0 || index !== 0"
                            :canMoveDown="index + 1 !== sectionsWithBlocksCurrentState.length || blockIndex + 1 !== sectionsWithBlocksCurrentState[index].blocks.length"
                            :elementType="elementType"
                            :sectionId="index"
                            @insertBlock="insertBlock"
                            @moveUp="moveBlockUp"
                            @moveDown="moveBlockDown"
                        />
                    </transition-group>
                </courseware-collapsible-box>
            </div>
            <courseware-manager-filing
                v-if="isCurrent && !sortContainers && !sortBlocksActive"
                :parentId="container.id"
                :parentItem="container"
                itemType="block"
                @deactivated="reloadContainer"
            />
        </courseware-collapsible-box>
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareManagerBlock from './CoursewareManagerBlock.vue';
import CoursewareManagerFiling from './CoursewareManagerFiling.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-manager-container',
    components: {
        CoursewareCollapsibleBox,
        CoursewareManagerBlock,
        CoursewareManagerFiling,
    },
    props: {
        container: Object,
        isCurrent: Boolean,
        inserter: Boolean,
        blockInserter: Boolean,
        sortContainers: Boolean,
        elementType: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean
    },
    data() {
        return {
            sortBlocksActive: false,
            sectionsWithBlocksCurrentState: [],
        };
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
        }),
        hasChildren() {
            return this.getBlocksCount >= 1;
        },
        canSortChildren() {
             return this.getBlocksCount > 1;
        },
        containerType() {
            return this.container.attributes['container-type'];
        },
        hasSections() {
            return this.containerType === 'tabs' || this.containerType === 'accordion';
        },
        getBlocksCount() {
            if (this.sectionsWithBlocksCurrentState === null) {
                return 0;
            } else {
                let blocks = 0;

                this.sectionsWithBlocksCurrentState.forEach((section) => {
                    if (section.blocks !== undefined) {
                        blocks += section.blocks.length;
                    }
                });

                // If there are more that one section and only one block,
                // we make sotring of that block possible, by just assuming that there are 2 blocks.
                // By doing this, we only provide the sorting feature when there are more than one section (section).
                if (this.sectionsWithBlocksCurrentState.length > 1 && blocks == 1) {
                    blocks++;
                }

                return blocks;
            }
         }
    },
    mounted() {
        this.initSections();
    },
    methods: {
        ...mapActions({
            sortBlocksInContainer: 'sortBlocksInContainer',
            updateContainer: 'updateContainer',
            loadContainer: 'loadContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject'
        }),
        reloadContainer() {
            this.loadContainer(this.container.id);
        },
        clickItem() {
            if (this.inserter) {
                this.$emit('insertContainer', {container: this.container, source: this.elementType});
            }
        },
        getSectionsWithBlocks() {
            if (!this.container) {
                return [];
            }
            if (!this.container.attributes.payload.sections) {
                return [];
            }

            const blockSections = _.cloneDeep(this.container.attributes.payload.sections);

            blockSections.forEach((section) => {
                if(section.blocks !== undefined) {
                    section.blocks = section.blocks.flatMap(
                        (id) => {
                            return this.blockById({ id }) ?? [] //remove blocks which could not be loaded
                        }
                    );
                }
            });

            return blockSections;
        },
        initSections() {
            this.sectionsWithBlocksCurrentState = this.getSectionsWithBlocks();
        },
        insertBlock(data) {
            this.$emit('insertBlock', data);
            this.initSections();
        },
        sortBlocks() {
            this.sortBlocksActive = true;
        },
        async storeBlocksSort() {
            const container = JSON.parse(JSON.stringify(this.container));

            this.sectionsWithBlocksCurrentState.forEach((section, index)=> {
                if (section.blocks !== undefined) {
                    container.attributes.payload.sections[index].blocks = section.blocks.map(({ id }) => ( id ));
                }
            });
            await this.lockObject({id: container.id, type: 'courseware-containers'});
            await this.updateContainer({ container: container, structuralElementId: this.container.relationships['structural-element'].data.id });
            await this.unlockObject({id: container.id, type: 'courseware-containers'});

            this.sortBlocksActive = false;
        },
        resetBlocksSort() {
            this.sectionsWithBlocksCurrentState = this.getSectionsWithBlocks();
            this.sortBlocksActive = false;
        },
        moveUp() {
            if (this.sortContainers) {
                this.$emit('moveUp', this.container.id);
            }
        },
        moveDown() {
            if (this.sortContainers) {
                this.$emit('moveDown', this.container.id);
            }
        },
        moveBlockUp(blockId, sectionId) {
            let view = this;
            this.sectionsWithBlocksCurrentState[sectionId].blocks.every((block, index) => {
                if (block.id === blockId) {
                     if (index === 0) {
                        if (sectionId !== 0) {
                            view.sectionsWithBlocksCurrentState[sectionId-1].blocks.push(view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index, 1)[0]);
                        }
                        return false;
                    }
                    view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index - 1, 0, view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index, 1)[0]);
                    return false;
                } else {
                    return true;
                }
            });
        },
        moveBlockDown(blockId, sectionId) {
            let view = this;
            this.sectionsWithBlocksCurrentState[sectionId].blocks.every((block, index) => {
                if (block.id === blockId) {
                    if (index === view.sectionsWithBlocksCurrentState[sectionId].blocks.length - 1) {
                        if (sectionId !== view.sectionsWithBlocksCurrentState.length - 1) {
                            view.sectionsWithBlocksCurrentState[sectionId + 1].blocks.unshift(view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index, 1)[0]);
                        }
                        return false;
                    }
                    view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index + 1, 0, view.sectionsWithBlocksCurrentState[sectionId].blocks.splice(index, 1)[0]);
                    return false;
                } else {
                    return true;
                }
            });
        },
    },
    watch: {
        container: {
            handler() {
                this.initSections();
            },
            deep: true
        }
    },
};
</script>
