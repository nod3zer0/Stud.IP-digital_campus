<template>
    <focus-trap v-model="trap" :initial-focus="() => initialFocusElement" :clickOutsideDeactivates="true" :fallbackFocus ="() => fallbackFocusElement">
        <div
            class="cw-ribbon-tools"
            :class="{ unfold: toolsActive, 'cw-ribbon-tools-consume': consumeMode }"
        >
            <div class="cw-ribbon-tool-content">
                <div class="cw-ribbon-tool-content-nav">
                    <courseware-tabs
                        class="cw-ribbon-tool-content-tablist"
                        ref="tabs"
                        @selectTab="selectTool($event.alias)"
                    >
                        <courseware-tab
                            :name="$gettext('Inhaltsverzeichnis')"
                            :selected="showContents"
                            alias="contents"
                            ref="contents"
                            :index="0"
                        >
                            <courseware-tools-contents
                                id="cw-ribbon-tool-contents"
                            />
                        </courseware-tab>
                        <courseware-tab
                            v-if="displayAdder"
                            :name="$gettext('Elemente hinzufügen')"
                            :selected="showBlockAdder"
                            alias="blockadder"
                            class="cw-ribbon-tool-blockadder-tab"
                            :index="1"
                        >
                            <courseware-tools-blockadder
                                v-if="showBlockAdder"
                                id="cw-ribbon-tool-blockadder"
                                :stickyRibbon="stickyRibbon"
                                @blockAdded="$emit('blockAdded')"
                            />
                        </courseware-tab>
                    </courseware-tabs>
                    <button
                        :title="$gettext('schließen')"
                        class="cw-tools-hide-button"
                        ref="closeTools"
                        @click="$emit('deactivate')">
                    </button>
                </div>
            </div>
        </div>
    </focus-trap>
</template>
<script>
import CoursewareTabs from '../layouts/CoursewareTabs.vue';
import CoursewareTab from '../layouts/CoursewareTab.vue';
import CoursewareToolsBlockadder from './CoursewareToolsBlockadder.vue';
import CoursewareToolsContents from './CoursewareToolsContents.vue';
import { FocusTrap } from 'focus-trap-vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon-toolbar',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareToolsBlockadder,
        CoursewareToolsContents,
        FocusTrap,
    },
    props: {
        toolsActive: Boolean,
        canEdit: Boolean,
        disableSettings: {
            type: Boolean,
            default: false,
        },
        disableAdder: {
            type: Boolean,
            default: false,
        },
        stickyRibbon: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            showContents: true,
            showBlockAdder: false,
            trap: false,
            initialFocusElement: null
        };
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
            consumeMode: 'consumeMode',
            containerAdder: 'containerAdder',
            adderStorage: 'blockAdder',
            viewMode: 'viewMode',
            context: 'context',
            userById: 'users/byId',
            userId: 'userId',
            selectedToolbarItem: 'selectedToolbarItem',
            currentElementisLink: 'currentElementisLink',
        }),
        showEditMode() {
            return this.viewMode === 'edit';
        },
        displayAdder() {
            if (this.disableAdder) {
                return false;
            } else {
                return !this.consumeMode && this.showEditMode && this.canEdit && !this.currentElementisLink;
            }
        },
        displaySettings() {
            if (this.disableSettings) {
                return false;
            } else {
                let user = this.userById({ id: this.userId });
                return !this.consumeMode && this.context.type === 'courses' && (this.isTeacher || ['root', 'admin'].includes(user.attributes.permission));
            }
            
        },
        isTeacher() {
            return this.userIsTeacher;
        },
        fallbackFocusElement(){
            return this.$refs.contents;
        }
    },
    methods: {
        ...mapActions({
            setToolbarItem: 'coursewareSelectedToolbarItem',
            coursewareContainerAdder: 'coursewareContainerAdder'
        }),
        selectTool(alias) {
            this.showContents = false;
            this.showBlockAdder = false;

            switch (alias) {
                case 'contents':
                    this.showContents = true;
                    this.disableContainerAdder();
                    this.scrollToCurrent();
                    break;
                case 'blockadder':
                    this.showBlockAdder = true;
                    break;
            }

            if (this.selectedToolbarItem !== alias) {
                this.setToolbarItem(alias);
            }
        },
        disableContainerAdder() {
            if (this.containerAdder !== false) {
                this.coursewareContainerAdder(false);
            }
        },
        scrollToCurrent() {
            setTimeout(() => {
                let contents = this.$refs.contents.$el; 
                let current = contents.querySelector('.cw-tree-item-link-current');
                if (current) {
                    contents.scroll({ top: current.offsetTop - 4, behavior: 'smooth' });
                }
            }, 360);
        },
    },
    mounted () {
        this.selectTool(this.selectedToolbarItem);
    },
    watch: {
        adderStorage(newValue) {
            if (Object.keys(newValue).length !== 0) {
                this.selectTool('blockadder');
            }
        },
        consumeMode(newValue) {
            if (newValue) {
                this.selectTool('contents');
            }
        },
        containerAdder(newValue) {
            if (newValue === true) {
                this.selectTool('blockadder');
            }
        },
        showEditMode(newValue) {
            if (!newValue) {
                this.selectTool('contents');
            }
        },
        toolsActive(newValue) {
            const focusElement = this.$refs.tabs.getTabButtonByAlias(this.selectedToolbarItem);
            if (newValue && focusElement) {
                setTimeout(() => {
                    this.initialFocusElement = focusElement;
                    this.trap = true;
                }, 300);
            }
        },
    },
};
</script>
