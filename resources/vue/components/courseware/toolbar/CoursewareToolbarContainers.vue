<template>
    <div class="cw-toolbar-containers">
        <div class="cw-container-style-selector" role="group" aria-labelledby="cw-containeradder-style">
            <p class="sr-only" id="cw-containeradder-style">{{ $gettext('Abschnitt-Stil') }}</p>
            <template v-for="style in containerStyles">
                <input
                    :key="style.key + '-input'"
                    type="radio"
                    name="container-style"
                    :id="'style-' + style.colspan"
                    v-model="selectedContainerStyle"
                    :value="style.colspan"
                />
                <label
                    :key="style.key + '-label'"
                    :for="'style-' + style.colspan"
                    :class="[
                        selectedContainerStyle === style.colspan ? 'cw-container-style-selector-active' : '',
                        style.colspan,
                    ]"
                >
                    {{ style.title }}
                </label>
            </template>
        </div>
        <draggable
            class="cw-containeradder-item-list"
            tag="div"
            role="listbox"
            v-model="containerTypes"
            handle=".cw-sortable-handle-containeradder"
            :group="{ name: 'description', pull: 'clone', put: 'false' }"
            :clone="cloneContainer"
            :sort="false"
            :emptyInsertThreshold="20"
            @start="dragContainerStart($event)"
            @end="dropNewContainer($event)"
            ref="containerSortables"
        >
            <courseware-container-adder-item
                v-for="container in containerTypes"
                :key="container.type"
                :title="container.title"
                :type="container.type"
                :colspan="selectedContainerStyle"
                :description="container.description"
                :firstSection="firstSection"
                :secondSection="secondSection"
                :newPosition="newContainerPosition"
            ></courseware-container-adder-item>
        </draggable>
    </div>
</template>

<script>
import CoursewareContainerAdderItem from './CoursewareContainerAdderItem.vue';
import containerMixin from '@/vue/mixins/courseware/container.js';
import draggable from 'vuedraggable';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-toolbar-containers',
    mixins: [containerMixin],
    components: {
        CoursewareContainerAdderItem,
        draggable,
    },
    data() {
        return {
            selectedContainerStyle: 'full',
            isDragging: false,
        };
    },
    computed: {
        ...mapGetters({
            containerTypes: 'containerTypes',
            structuralElementById: 'courseware-structural-elements/byId',
            relatedContainers: 'courseware-containers/related',

        }),
        containerStyles() {
            return [
                { key: 0, title: this.$gettext('Volle Breite'), colspan: 'full' },
                { key: 1, title: this.$gettext('Halbe Breite'), colspan: 'half' },
                { key: 2, title: this.$gettext('Halbe Breite (zentriert)'), colspan: 'half-center' },
            ];
        },
        containers() {
            return this.relatedContainers({
                parent: this.structuralElementById({id: this.$route.params.id}), 
                relationship: 'containers'    
            });
        },
        newContainerPosition() {
            return this.containers?.length || 0;
        },
        firstSection() {
            return this.$gettext('erstes Element');
        },
        secondSection() {
            return this.$gettext('zweites Element');
        },
    },
    methods: {
        cloneContainer(original) {
            original.newContainer = true;
            original.attributes = {};
            original.attributes['container-type'] = original.type;
            original.attributes.payload = {};
            original.relationships = {};
            original.relationships.blocks = {};
            original.relationships.blocks.data = {};
            original.firstSection = this.firstSection;
            original.secondSection = this.secondSection;
            original.containerStyle = this.selectedContainerStyle;
            return original;
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
        dragContainerStart(e) {
            this.isDragging = true;
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
                        secondSection: item.secondSection
                    },
                newPosition: e.newIndex
                };
                this.addContainer(data);
            }
        },
    }
};
</script>
