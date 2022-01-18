<template>
    <div class="cw-block cw-block-table-of-contents">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeText"
            @closeEdit="closeEdit"
        >
            <template #content>
                <div v-if="currentStyle !== 'tiles' && currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <ul
                    v-if="currentStyle === 'list-details' || currentStyle === 'list'"
                    :class="['cw-block-table-of-contents-' + currentStyle]"
                >
                    <li v-for="child in childElements" :key="child.id">
                        <router-link :to="'/structural_element/' + child.id">
                            <div class="cw-block-table-of-contents-title-box" :class="[child.attributes.payload.color]">
                                {{ child.attributes.title }}
                                <p v-if="currentStyle === 'list-details'">{{ child.attributes.payload.description }}</p>
                            </div>
                        </router-link>
                    </li>
                </ul>
                <ul
                    v-if="currentStyle === 'tiles'" 
                    class="cw-block-table-of-contents-tiles cw-tiles"
                    :class="[childElements.length > 3 ? 'cw-tiles-space-between' : '']"
                >
                    <li
                        v-for="child in childElements"
                        :key="child.id"
                        class="tile"
                        :class="[child.attributes.payload.color, childElements.length > 3 ? '':  'cw-tile-margin']"
                    >
                        <router-link :to="'/structural_element/' + child.id" :title="child.attributes.title">
                            <div
                                class="preview-image"
                                :class="[hasImage(child) ? '' : 'default-image']"
                                :style="getChildStyle(child)"
                            ></div>
                            <div class="description">
                                <header>{{ child.attributes.title }}</header>
                                <div class="description-text-wrapper">
                                    <p>{{ child.attributes.payload.description }}</p>
                                </div>
                                <footer>
                                    {{ countChildren }}
                                    <translate
                                        :translate-n="countChildren"
                                        translate-plural="Seiten"
                                    >
                                       Seite
                                    </translate>
                                </footer>
                            </div>
                        </router-link>
                    </li>
                </ul>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Layout</translate>
                        <select v-model="currentStyle">
                            <option value="list"><translate>Liste</translate></option>
                            <option value="list-details"><translate>Liste mit Beschreibung</translate></option>
                            <option value="tiles"><translate>Kacheln</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Inhaltsverzeichnis-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-table-of-contents-block',
    components: {
        CoursewareDefaultBlock,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentStyle: '',
        };
    },
    computed: {
        ...mapGetters({
            childrenById: 'courseware-structure/children',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        structuralElement() {
            return this.structuralElementById({ id: this.$route.params.id });
        },
        childElements() {
            return this.childrenById(this.structuralElement.id).map((id) => this.structuralElementById({ id }));
        },
        countChildren() {
            return this.childrenById(this.structuralElement.id).length;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        style() {
            return this.block?.attributes?.payload?.style;
        },
        childSets() {
            let childSets = [];
            let childElements = this.childElements;
            while (childElements.length > 0) {
                let set = [];
                for (let i = 0; i < 4; i++) {
                    let elem = childElements.shift();
                    if (elem !== undefined) {
                        set.push(elem);
                    }
                }
                childSets.push(set);
            }

            return childSets;
        }
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentStyle = this.style;
        },
        closeEdit() {
            this.initCurrentData();
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.style = this.currentStyle;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        getChildStyle(child) {
            let url = child.relationships?.image?.meta?.['download-url'];

            if(url) {
                return {'background-image': 'url(' + url + ')'};
            } else {
                return {};
            }
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },
    },
};
</script>
