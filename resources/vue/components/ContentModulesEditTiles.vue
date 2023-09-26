<template>
    <div class="content-modules-wrapper">
        <draggable v-model="sortedModules" handle=".dragarea">
            <transition-group
                name="admin_contentmodules"
                class="admin_contentmodules studip-grid"
                tag="div"
                role="listbox"
            >
                <div
                    v-for="module in activeModules"
                    :key="module.id"
                    role="option"
                    class="studip-grid-element"
                    :class="getModuleCSSClasses(module, activated[module.id])"
                    v-cloak
                >
                    <div>
                        <a class="upper_part dragarea" :href="getDescriptionURL(module)" data-dialog>
                            <div>
                                <img :src="module.icon" width="40" height="40" v-if="module.icon" />
                            </div>
                            <div>
                                <h3>{{ module.displayname }}</h3>
                                {{ module.summary }}
                            </div>
                        </a>
                        <div class="down_part">
                            <div>
                                <a
                                    class="dragarea"
                                    tabindex="0"
                                    :aria-label="
                                        $gettextInterpolate(
                                            $gettext(
                                                'Sortierelement für Werkzeug %{module}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.'
                                            ),
                                            { module: module.displayname }
                                        )
                                    "
                                    @keydown="keyboardHandler($event, module)"
                                    v-if="filterCategory === null"
                                    :ref="`draghandle-${module.id}`"
                                >
                                    <span class="drag-handle"></span>
                                </a>
                                <label v-if="!module.mandatory">
                                    <input
                                        type="checkbox"
                                        :checked="activated[module.id]"
                                        @click="toggleModule(module)"
                                        :ref="'checkbox_' + module.id"
                                    />
                                    {{ $gettext('Werkzeug ist aktiv') }}
                                </label>
                            </div>

                            <div class="icons_right">
                                <a
                                    href="#"
                                    class="toggle_visibility"
                                    role="checkbox"
                                    v-if="!module.mandatory"
                                    :aria-checked="module.visibility !== 'tutor' ? 'true' : 'false'"
                                    @click.prevent="toggleModuleVisibility(module)"
                                >
                                    <studip-icon
                                        :shape="
                                            module.visibility !== 'tutor'
                                                ? 'visibility-visible'
                                                : 'visibility-invisible'
                                        "
                                        class="text-bottom"
                                        :title="
                                            $gettextInterpolate(
                                                $gettext(
                                                    'Inhaltsmoduls %{ name } für Teilnehmende unsichtbar bzw. sichtbar schalten'
                                                ),
                                                { name: module.displayname }
                                            )
                                        "
                                    ></studip-icon>
                                </a>
                                <a :href="getRenameURL(module)" data-dialog="size=medium">
                                    <studip-icon
                                        shape="edit"
                                        class="text-bottom"
                                        :title="
                                            $gettextInterpolate(
                                                $gettext(
                                                    'Umbenennen des Inhaltsmoduls %{ name }'
                                                ),
                                                { name: module.displayname }
                                            )
                                        "
                                    ></studip-icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </transition-group>
        </draggable>
        <transition-group
            name="admin_contentmodules"
            class="admin_contentmodules studip-grid inactive-modules"
            tag="div"
            role="listbox"
        >
            <div
                v-for="module in inactiveModules"
                :key="module.id"
                role="option"
                class="studip-grid-element"
                :class="getModuleCSSClasses(module, activated[module.id])"
                v-cloak
            >
                <div>
                    <a class="upper_part" :href="getDescriptionURL(module)" data-dialog>
                        <div>
                            <img :src="module.icon" width="40" height="40" v-if="module.icon" />
                        </div>
                        <div>
                            <h3>{{ module.displayname }}</h3>
                            {{ module.summary }}
                        </div>
                    </a>
                    <div class="down_part">
                        <div>
                            <label v-if="!module.mandatory">
                                <input
                                    type="checkbox"
                                    :checked="activated[module.id]"
                                    @click="toggleModule(module)"
                                    :ref="'checkbox_' + module.id"
                                />
                                {{ $gettext('Werkzeug ist inaktiv') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </transition-group>
    </div>
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
        ...mapState('contentmodules', ['modules']),
    },
    methods: {
        toggleModule(module) {
            Vue.set(this.activated, module.id, !this.activated[module.id]);
            this.toggleModuleActivation(module);
        },
    },
    watch: {
        modules: {
            immediate: true,
            handler(current) {
                current.forEach((module) => Vue.set(this.activated, module.id, module.active));
            },
        },
    },
};
</script>

<style lang="scss" scoped>
.content-modules-wrapper {
    max-width: 1410px;
}
.inactive-modules {
    margin-top: 1em;
    border-top: solid thin var(--content-color-40);
    padding-top: 1em;
}
.studip-grid-element {
    display: flex;
    flex-direction: row;
    background-color: var(--white);
    border-left: 1px solid var(--dark-gray-color-60);
    margin: 2px 0;

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

    &.sortable-ghost {
        border: dashed 2px var(--content-color-40);
        margin: 0;
        * {
            opacity: 0;
        }
    }
    &.pulse:not(.sortable-ghost) {
        box-shadow: 0 0 0 0 rgb(255, 189, 51, 1);
        animation: pulse 2s;
        animation-iteration-count: 1;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 189, 51, 1);
        }
        25% {
            box-shadow: 0 0 0 5px rgba(255, 189, 51, 0.8);
        }
        50% {
            box-shadow: 0 0 0 5px rgba(255, 189, 51, 0.6);
        }
        75% {
            box-shadow: 0 0 0 5px rgba(255, 189, 51, 0.4);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(255, 189, 51, 0);
        }
    }

    > div {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all var(--transition-duration) ease;
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
