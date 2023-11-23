<template>
    <div class="cw-tools cw-tools-contents">
        <router-link :to="'/structural_element/' + rootElement.id" :class="{'root-is-current': rootIsCurrent}">
            <div v-if="rootElement" class="cw-tools-contents-header">
                <div
                    class="cw-tools-contents-header-image"
                    :class="[headerImageUrl ? '' : 'default-image']"
                    :style="headerImageStyle"
                ></div>
                <div class="cw-tools-contents-header-details">
                    <header>{{ rootElement.attributes.title }}</header>
                    <p>{{ rootElement.attributes.payload.description }}</p>
                </div>
            </div>
        </router-link>
        <courseware-tree v-if="structuralElements.length" />
    </div>
</template>

<script>
import CoursewareTree from './CoursewareTree.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tools-contents',
    components: {
        CoursewareTree,
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
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
            return {};
        },

        rootIsCurrent() {
            const id = this.$route?.params?.id;
            return this.rootElement.id === id;
        },
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
        &.default-image {
            background-image: url("../images/icons/blue/courseware.svg");
            background-size: 64px;
        }
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
</style>
