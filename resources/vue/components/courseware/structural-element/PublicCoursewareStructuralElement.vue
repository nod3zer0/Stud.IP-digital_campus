<template>
    <focus-trap v-model="consumModeTrap">
        <div
            :class="{ 'cw-structural-element-consumemode': consumeMode }"
            class="cw-structural-element"
        >
            <div v-if="structuralElement" class="cw-structural-element-content">
                <courseware-ribbon :canEdit="false" :disableSettings="true" :disableAdder="true">
                    <template #buttons>
                        <router-link v-if="prevElement" :to="'/structural_element/' + prevElement.id">
                            <div class="cw-ribbon-button cw-ribbon-button-prev" :title="textRibbon.perv" />
                        </router-link>
                        <div v-else class="cw-ribbon-button cw-ribbon-button-prev-disabled" :title="$gettext('keine vorherige Seite')"/>
                        <router-link v-if="nextElement" :to="'/structural_element/' + nextElement.id">
                            <div class="cw-ribbon-button cw-ribbon-button-next" :title="textRibbon.next" />
                        </router-link>
                        <div v-else class="cw-ribbon-button cw-ribbon-button-next-disabled" :title="$gettext('keine nächste Seite')"/>
                    </template>
                    <template #breadcrumbList>
                        <li
                            v-for="ancestor in ancestors"
                            :key="ancestor.id"
                            :title="ancestor.attributes.title"
                            class="cw-ribbon-breadcrumb-item"
                        >
                            <span>
                                <router-link :to="'/structural_element/' + ancestor.id">{{ ancestor.attributes.title || "–" }}</router-link>
                            </span>
                        </li>
                        <li
                            class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current"
                            :title="structuralElement.attributes.title"
                        >
                            <span>{{ structuralElement.attributes.title || "–" }}</span>
                        </li>
                    </template>
                    <template #breadcrumbFallback>
                        <li
                            class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current"
                            :title="structuralElement.attributes.title"
                        >
                            <span>{{ structuralElement.attributes.title }}</span>
                        </li>
                    </template>
                </courseware-ribbon>

                <div
                    class="cw-container-wrapper"
                    :class="{
                        'cw-container-wrapper-consume': consumeMode,
                    }"
                >
                    <div v-if="structuralElementLoaded" class="cw-companion-box-wrapper">
                        <courseware-empty-element-box
                            v-if="noContainers"
                            :noContainers="noContainers"
                        />
                    </div>
                    <component
                        v-for="container in containers"
                        :key="container.id"
                        :is="containerComponent(container)"
                        :container="container"
                        :canEdit="false"
                        :canAddElements="false"
                        :isTeacher="false"
                        class="cw-container-item"
                    />
                </div>
            </div>
        </div>
    </focus-trap>
</template>

<script>
import ContainerComponents from '../containers/container-components.js';
import StructuralElementComponents from './structural-element-components.js';
import CoursewarePluginComponents from '../plugin-components.js';
import CoursewareCompanionOverlay from '../layouts/CoursewareCompanionOverlay.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'public-courseware-structural-element',

    components: Object.assign(StructuralElementComponents, {
        CoursewareCompanionOverlay,
    }),

    props: ['orderedStructuralElements', 'structuralElement'],

    data() {
        return {
            consumModeTrap: false,
            textRibbon: {
                perv: this.$gettext('zurück'),
                next: this.$gettext('weiter'),
            },
        }
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
            consumeMode: 'consumeMode',
            containerById: 'courseware-containers/byId',
            pluginManager: 'pluginManager',
            relatedContainers: 'courseware-containers/related',
            relatedStructuralElements: 'courseware-structural-elements/related',
            structuralElementById: 'courseware-structural-elements/byId',
        }),

        currentId() {
            return this.structuralElement?.id;
        },

        image() {
            return this.structuralElement.relationships?.image?.meta?.['download-url'] ?? null;
        },

        structuralElementLoaded() {
            return this.structuralElement !== null && this.structuralElement !== {};
        },

        ancestors() {
            if (!this.structuralElement) {
                return [];
            }

            const finder = (parent) => {
                const parentId = parent.relationships?.parent?.data?.id;
                if (!parentId) {
                    return null;
                }
                const element = this.structuralElementById({ id: parentId });

                return element;
            };

            const visitAncestors = function* (node) {
                const parent = finder(node);
                if (parent) {
                    yield parent;
                    yield* visitAncestors(parent);
                }
            };

            return [...visitAncestors(this.structuralElement)].reverse();
        },

        prevElement() {
            const currentIndex = this.orderedStructuralElements.indexOf(this.structuralElement.id);
            if (currentIndex <= 0) {
                return null;
            }
            const previousId = this.orderedStructuralElements[currentIndex - 1];
            const previous = this.structuralElementById({ id: previousId });

            return previous;
        },

        nextElement() {
            const currentIndex = this.orderedStructuralElements.indexOf(this.structuralElement.id);
            const lastIndex = this.orderedStructuralElements.length - 1;
            if (currentIndex === -1 || currentIndex === lastIndex) {
                return null;
            }
            const nextId = this.orderedStructuralElements[currentIndex + 1];
            const next = this.structuralElementById({ id: nextId });

            return next;
        },

        empty() {
            if (this.containers === null) {
                return true;
            } else {
                return !this.containers.some((container) => container.relationships.blocks.data.length > 0);
            }
        },

        containers() {
            let containers = [];
            let relatedContainers = this.structuralElement?.relationships?.containers?.data;

            if (relatedContainers) {
                for (const container of relatedContainers) {
                    containers.push(this.containerById({ id: container.id}));
                }
            }

            return containers;
        },

        noContainers() {
            if (this.containers === null) {
                return true;
            } else {
                return this.containers.length === 0;
            }
        },

        isRoot() {
            return this.structuralElement.id === this.context.rootId;
        },
    },

    methods: {
        ...mapActions({
            companionError: 'companionError',
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
        }),
        containerComponent(container) {
            return 'courseware-' + container.attributes['container-type'] + '-container';
        },
    },

    created() {
        this.pluginManager.registerComponentsLocally(this);
    },

    watch: {
        consumeMode(newState) {
            this.consumModeTrap = newState;
        },
    },

    // this line provides all the components to courseware plugins
    provide: () => ({
        containerComponents: ContainerComponents,
        coursewarePluginComponents: CoursewarePluginComponents,
    }),
};
</script>
