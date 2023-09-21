<template>
    <form id="module-list" :class="{'table-display': !isTilesDisplay }">
        <div class="infopanel" v-if="highlighted.length > 0">
            <div class="top"></div>
            <div class="navigation_wrapper">
                <a href="#"
                   v-if="highlightIndex > 0"
                   @click.prevent="highlightIndex--"
                   title="$gettext('Vorheriges Inhaltselement')">
                    <studip-icon shape="arr_1left" :size="20"></studip-icon>
                </a>
                <div v-else></div>

                <a :href="getDescriptionURL(highlightedModule)" data-dialog v-cloak class="contentmodule">
                    <div class="iconwrapper">
                        <img :src="highlightedModule.icon" width="60" height="60" v-cloak>
                    </div>
                    <div class="text">
                        <div class="title" v-cloak>{{ highlightedModule.toolname }}</div>
                        <div v-if="highlightedModule.highlight_text" v-cloak>
                            {{ highlightedModule.highlight_text }}
                        </div>
                    </div>
                </a>

                <a href="#"
                   @click.prevent="highlightIndex++"
                   v-if="highlightIndex < highlighted.length - 1"
                   :title="$gettext('Nächstes Inhaltselement')">
                    <studip-icon shape="arr_1right" :size="20"></studip-icon>
                </a>
                <div v-else></div>
            </div>
        </div>

        <component :is="displayComponent"
                   :modules="modules"
                   :filtercategory="filterCategory"
        ></component>

        <MountingPortal mount-to="#tool-view-switch .sidebar-widget-content .widget-list" name="sidebar-switch">
            <ul class="widget-list widget-links sidebar-views">
                <li :class="{ active: view === 'tiles' }">
                    <a href="#" @click.prevent="changeView('tiles')">
                        {{ $gettext('Kachelansicht') }}
                    </a>
                </li>
                <li :class="{ active: view === 'table' }">
                    <a href="#" @click.prevent="changeView('table')">
                        {{ $gettext('Tabellarische Ansicht') }}
                    </a>
                </li>
            </ul>
        </MountingPortal>

        <MountingPortal mount-to="#tool-filter-category .sidebar-widget-content .widget-list" name="sidebar-filter">
            <ul class="widget-list widget-options">
                <li>
                    <a class="options-radio"
                       :class="filterCategory === null ? 'options-checked' : 'options-unchecked'"
                       role="radio"
                       :aria-checked="filterCategory === null ? 'true' : 'false'"
                       href="#"
                       @click.prevent="setFilterCategory(null)"
                    >
                        {{ $gettext('Alle Kategorien') }}
                    </a>
                </li>
                <li v-for="category in categories" :key="category">
                    <a class="options-radio"
                       :class="filterCategory === category ? 'options-checked' : 'options-unchecked'"
                       href="#"
                       role="radio"
                       :aria-checked="filterCategory === category ? 'true' : 'false'"
                       @click.prevent="setFilterCategory(category)"
                    >
                        {{ category }}
                    </a>
                </li>
            </ul>
        </MountingPortal>
    </form>
</template>
<script>
import ContentModulesEditTable from './ContentmodulesEditTable.vue';
import ContentModulesEditTiles from './ContentModulesEditTiles.vue';
import ContentModulesMixin from '../mixins/ContentModulesMixin.js';
import { mapMutations, mapState } from 'vuex';

export default {
    name: 'ContentModules',
    mixins: [ContentModulesMixin],
    data: () => ({
        highlightIndex: 0,
    }),
    computed: {
        ...mapState('contentmodules', [
            'highlighted',
        ]),
        isTilesDisplay() {
            return this.view === 'tiles';
        },
        displayComponent() {
            return this.isTilesDisplay ? ContentModulesEditTiles : ContentModulesEditTable;
        },
        highlightedModule() {
            const id = this.highlighted[this.highlightIndex];
            return this.$store.getters['contentmodules/getModuleById'](id);
        },
    },
    methods: {
        ...mapMutations('contentmodules', [
            'setFilterCategory',
        ]),
    },
};
</script>
<style lang="scss">
.admin_contentmodules {
    .drag-handle {
        display: inline-block;

        width: 6px;
        height: 20px;
        margin-top: 5px;

        background-size: auto 20px;
    }
}

.admin_contentmodules-move, /* apply transition to moving elements */
.admin_contentmodules-enter-active,
.admin_contentmodules-leave-active {
}

.admin_contentmodules-enter-from,
.admin_contentmodules-leave-to {
    opacity: 0;
    transform: translateX(30px) translateY(30px);
}

/* ensure leaving items are taken out of layout flow so that moving
   animations can be calculated correctly. */
.admin_contentmodules-leave-active {
    position: absolute;
}
</style>
<style lang="scss" scoped>
.infopanel {
    padding: 10px;
    background-color: var(--content-color-20);
    width: 840px;
    max-width: 100%;
    box-sizing: border-box;
    height: 200px;
    max-height: 200px;
    overflow: hidden;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    margin-bottom: 10px;

    .table-display & {
        width: unset;
    }

    > .top {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        > h2 {
            font-weight: normal;
            margin-top: 5px;
        }
    }

    > .navigation_wrapper {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        > * {
            min-width: 20px;
            min-height: 20px;
        }
        > .contentmodule {
            display: flex;
            flex-direction: row;
            .iconwrapper {
                background-color: var(--white);
                border-radius: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 90px;
                height: 90px;
                margin-right: 20px;
            }
            .title {
                margin-top: 10px;
                font-size: 1.3em;
                font-weight: bold;
            }
        }
    }


    .back-button {
        float: left;
        position: relative;
        top: 20px;
    }
}
</style>
