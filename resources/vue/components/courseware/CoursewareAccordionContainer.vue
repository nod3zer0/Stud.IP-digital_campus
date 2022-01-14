<template>
    <courseware-default-container
        :container="container"
        containerClass="cw-container-accordion"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        @storeContainer="storeContainer"
        @closeEdit="initCurrentData"
    >
        <template v-slot:containerContent>
            <courseware-collapsible-box
                v-for="(section, index) in currentSections"
                :key="index"
                :title="section.name"
                :icon="section.icon"
                :open="index === 0"
            >
                <ul class="cw-container-accordion-block-list">
                    <li v-for="block in section.blocks" :key="block.id" class="cw-block-item">
                        <component
                            :is="component(block)"
                            :block="block"
                            :canEdit="canEdit"
                            :isTeacher="isTeacher"
                        />
                    </li>
                    <li v-if="showEditMode">
                        <courseware-block-adder-area :container="container" :section="index" @updateContainerContent="updateContent"/>
                    </li>
                </ul>
            </courseware-collapsible-box>
        </template>
        <template v-slot:containerEditDialog>
            <form class="default cw-container-dialog-edit-form" @submit.prevent="">
                <fieldset v-for="(section, index) in currentContainer.attributes.payload.sections" :key="index">
                    <label>
                        <translate>Title</translate>
                        <input type="text" v-model="section.name" />
                    </label>
                    <label>
                        <translate>Icon</translate>
                        <v-select :options="icons" v-model="section.icon" class="cw-vs-select">
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                <translate>Es steht keine Auswahl zur Verfügung.</translate>
                            </template>
                            <template #selected-option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                            <template #option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                        </v-select>
                    </label>
                    <label
                        class="cw-container-section-delete"
                        v-if="currentContainer.attributes.payload.sections.length > 1"
                    >
                    <button class="button trash" @click="deleteSection(index)"><translate>Fach löschen</translate></button>
                    </label>
                </fieldset>
            </form>
            <button class="button add" @click="addSection"><translate>Fach hinzufügen</translate></button>
        </template>
    </courseware-default-container>
</template>

<script>
import ContainerComponents from './container-components.js';
import containerMixin from '../../mixins/courseware/container.js';
import contentIcons from './content-icons.js';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import StudipIcon from './../StudipIcon.vue';

import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-accordion-container',
    mixins: [containerMixin],
    components: Object.assign(ContainerComponents, {
        CoursewareCollapsibleBox,
        StudipIcon,
    }),
    props: {
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentContainer: {},
            currentSections: [],
        };
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
        }),
        blocks() {
            if (!this.container) {
                return [];
            }

            return this.container.relationships.blocks.data.map(({ id }) => this.blockById({ id })).filter((a) => a);
        },
        showEditMode() {
            return this.$store.getters.viewMode === 'edit';
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
            unlockObject: 'unlockObject',
        }),
        initCurrentData() {
            this.currentContainer = _.cloneDeep(this.container);

            let view = this;
            let sections = this.currentContainer.attributes.payload.sections;
            sections.forEach(section => {
                section.blocks = section.blocks.map((id) =>  view.blockById({id})).filter((a) => a);
            });

            this.currentSections = sections;
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
            this.currentContainer.attributes.payload.sections.forEach(section => {
                section.blocks = section.blocks.map((block) => {return block.id;});
            });
            await this.updateContainer({
                container: this.currentContainer,
                structuralElementId: this.currentContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            this.initCurrentData();
        },
        component(block) {
            if (block.attributes) {
                return 'courseware-' + block.attributes["block-type"] + '-block';
            }
            return null;
        },
        updateContent(blockAdder) {
            if(blockAdder.container.id === this.container.id) {
                this.initCurrentData();
            }
        }
    },
    watch: {
        blocks() {
            this.initCurrentData();
        }
    }
};
</script>
