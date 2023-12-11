<template>
    <div class="cw-tools cw-tools-contents">
        <component :is="headerComponent" :to="'/structural_element/' + rootElement.id" :class="{'root-is-current': rootIsCurrent, 'root-is-hidden': hideRoot}">
            <div v-if="rootElement" class="cw-tools-contents-header">
                <studip-ident-image v-model="identimage" :baseColor="headerColor.hex" :pattern="rootElement.attributes.title" />
                <div
                    class="cw-tools-contents-header-image"
                    :style="headerImageStyle"
                ></div>
                <div class="cw-tools-contents-header-details">
                    <header>{{ rootElement.attributes.title }}</header>
                    <p>{{ rootElement.attributes.payload.description }}</p>
                </div>
            </div>
        </component>
        <courseware-tree v-if="structuralElements.length" />
    </div>
</template>

<script>
import CoursewareTree from './CoursewareTree.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import StudipIdentImage from './../../StudipIdentImage.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tools-contents',
    mixins: [colorMixin],
    components: {
        CoursewareTree,
        StudipIdentImage,
    },
    data() {
        return {
            identimage: '',
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
            rootLayout: 'rootLayout',
            structuralElements: 'courseware-structural-elements/all',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        rootElement() {
            const root = this.relatedStructuralElement({
                parent: { id: this.courseware.id, type: this.courseware.type },
                relationship: 'root',
            });

            return root;
        },
        headerImageUrl() {
            return this.rootElement.relationships?.image?.meta?.['download-url'];
        },
        headerImageStyle() {
            if (this.headerImageUrl) {
                return { 'background-image': 'url(' + this.headerImageUrl + ')' };
            }
            return { 'background-image': 'url(' + this.identimage + ')' };
        },
        headerColor() {
            const rootColor = this.rootElement?.attributes?.payload?.color ?? 'studip-blue';
            return this.mixinColors.find((color) => color.class === rootColor);
        },
        rootIsCurrent() {
            const id = this.$route?.params?.id;
            return this.rootElement.id === id;
        },
        hideRoot() {
            return this.rootLayout === 'none';
        },
        headerComponent() {
            return this.hideRoot ? 'span' : 'router-link';
        }
    },
};
</script>
<style scoped lang="scss">
.cw-tools-contents-header {
    display: flex;
    flex-direction: row;
    height: 100px;
    margin-top: 8px;
    .cw-tools-contents-header-image {
        height: 100px;
        width: 150px;
        min-width: 150px;
        background-size: 100% auto;
        background-repeat: no-repeat;
        background-position: center;
        background-color: var(--content-color-20);
    }

    .cw-tools-contents-header-details {
        margin: 0 8px;
        display: -webkit-box;
        overflow: hidden;
        height: 100px;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        header {
            margin: 0 0 6px 0;
            font-size: 16px;
            line-height: 16px;
        }
        p {
            margin: 0;
            color: var(--black);
        }
    }
}
.root-is-current {
    .cw-tools-contents-header-details {
        header {
            color: var(--black);
            font-weight: 600;
        }
    }
}
.root-is-hidden {
    .cw-tools-contents-header-details {
        header {
            color: var(--black);
        }
    }
}
</style>
