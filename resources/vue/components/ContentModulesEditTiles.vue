<template>
    <draggable v-model="sortedModules" handle=".dragarea">
        <transition-group name="admin_contentmodules"
                          class="admin_contentmodules studip-grid"
                          tag="div"
                          role="listbox"
        >
            <div v-for="module in sortedModules"
                 :key="module.id"
                 role="option"
                 class="studip-grid-element"
                 :class="getModuleCSSClasses(module, activated[module.id])"
                 v-cloak
            >
                <div>
                    <a :class="'upper_part' + (module.active && filterCategory === null ? ' dragarea' : '')" :href="getDescriptionURL(module)" data-dialog>
                        <div>
                            <img :src="module.icon" width="40" height="40" v-if="module.icon">
                        </div>
                        <div>
                            <h3>{{module.displayname}}</h3>
                            {{module.summary}}
                        </div>
                    </a>
                    <div class="down_part">
                        <div>
                            <a class="dragarea"
                               tabindex="0"
                               :title="$gettextInterpolate('Sortierelement für Module %{module}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.', {module: module.displayname})"
                               @keydown="keyboardHandler($event, module)"
                               v-if="module.active && filterCategory === null"
                               :ref="`draghandle-${module.id}`">
                                <span class="drag-handle"></span>
                            </a>
                            <label v-if="!module.mandatory">
                                <input type="checkbox" :checked="activated[module.id]" @click="toggleModule(module)" :ref="'checkbox_' + module.id">
                                {{ module.active ? $gettext('Werkzeug ist aktiv') : $gettext('Werkzeug ist inaktiv') }}
                            </label>
                        </div>

                        <div class="icons_right">
                            <a href="#"
                               class="toggle_visibility"
                               role="checkbox"
                               v-if="module.active && !module.mandatory"
                               :aria-checked="module.visibility !== 'tutor' ? 'true' : 'false'"
                               @click.prevent="toggleModuleVisibility(module)">
                                <studip-icon :shape="module.visibility !== 'tutor' ? 'visibility-visible' : 'visibility-invisible'"
                                             class="text-bottom"
                                             :title="$gettextInterpolate($gettext('Inhaltsmoduls %{ name } für Teilnehmende unsichtbar bzw. sichtbar schalten'), { name: module.displayname})"></studip-icon>
                            </a>
                            <a :href="getRenameURL(module)" data-dialog="size=medium" v-if="module.active">
                                <studip-icon shape="edit"
                                             class="text-bottom"
                                             :title="$gettextInterpolate($gettext('Umbenennen des Inhaltsmoduls %{ name }'), { name: module.displayname })"></studip-icon>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </transition-group>
    </draggable>
</template>
<script>
import Vue from 'vue';
import { mapState } from 'vuex';
import ContentModulesMixin from '../mixins/ContentModulesMixin.js';

export default {
    name: 'ContentModules',
    mixins: [ContentModulesMixin],
    data: () => ({
        activated: {},
        timeouts: {},
    }),
    computed: {
        ...mapState('contentmodules', [
            'modules'
        ]),
    },
    methods: {
        toggleModule(module) {
            Vue.set(this.activated, module.id, !this.activated[module.id]);

            if (this.timeouts[module.id] ?? null) {
                clearTimeout(this.timeouts[module.id] ?? null);
                this.timeouts[module.id] = null;
            }  else {
                this.timeouts[module.id] = setTimeout(() => {
                    this.toggleModuleActivation(module);
                    this.timeouts[module.id] = null;
                }, 700);
            }
        },
    },
    watch: {
        modules: {
            immediate: true,
            handler(current) {
                current.forEach(module => Vue.set(this.activated, module.id, module.active));
            }
        }
    }
}
</script>
<style lang="scss" scoped>
.studip-grid-element {
    display: flex;
    flex-direction: row;
    background-color: var(--white);
    border-left: 1px solid var(--dark-gray-color-60);
    transition: all 500ms ease, border-left-color 300ms ease;
    &.visibility-visible {
        border-left-color: var(--green);
        > div {
            border-left-color: var(--green);
        }
    }
    &.visibility-invisible {
        border-left-color: var(--yellow);
        > div {
            border-left-color: var(--yellow);
        }
    }
    > div {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 500ms ease, border-left-color 300ms ease;
        border-left: 10px solid var(--dark-gray-color-60);
        min-height: 150px;
        width: 100%;

        > .upper_part {
            display: flex;
            > :first-child {
                padding: 10px 5px 10px 15px;
            }
            > :last-child {
                padding: 10px 10px 20px;

                h3 {
                    margin-top: 0;
                    color: var(--base-color);
                }
            }
        }
        > .down_part {
            background-color: var(--content-color-20);
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 30px;
            padding-left: 5px;
            > div {
                display: flex;
                align-items: center;
            }
            .icons_right > a {
                margin-right: 8px;
            }
        }
    }
}
</style>
