<template>
    <focus-trap v-model="trap" :initial-focus="() => initialFocusElement" :clickOutsideDeactivates="true" :fallbackFocus ="() => fallbackFocusElement">
        <div
            class="cw-ribbon-tools"
            :class="{ unfold: toolsActive, 'cw-ribbon-tools-consume': consumeMode }"
            @keydown.esc="$emit('deactivate')"
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
                                id="cw-ribbon-tool-blockadder"
                                :stickyRibbon="stickyRibbon"
                            />
                        </courseware-tab>
                        <courseware-tab
                            v-if="displaySettings"
                            :name="$gettext('Einstellungen')"
                            :selected="showAdmin"
                            alias="admin"
                            :index="2"
                        >
                            <courseware-tools-admin
                                id="cw-ribbon-tool-admin"
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
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareToolsAdmin from './CoursewareToolsAdmin.vue';
import CoursewareToolsBlockadder from './CoursewareToolsBlockadder.vue';
import CoursewareToolsContents from './CoursewareToolsContents.vue';
import { FocusTrap } from 'focus-trap-vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon-toolbar',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareToolsAdmin,
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
            showAdmin: false,
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
        }),
        showEditMode() {
            return this.viewMode === 'edit';
        },
        displayAdder() {
            if (this.disableAdder) {
                return false;
            } else {
                return !this.consumeMode && this.showEditMode && this.canEdit;
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
            return this.$refs.tabs.getTabButtonByAlias(this.selectedToolbarItem);
        }
    },
    methods: {
        ...mapActions({
            setToolbarItem: 'coursewareSelectedToolbarItem'
        }),
        selectTool(alias) {
            this.showContents = false;
            this.showAdmin = false;
            this.showBlockAdder = false;

            switch (alias) {
                case 'contents':
                    this.showContents = true;
                    this.disableContainerAdder();
                    this.scrollToCurrent();
                    break;
                case 'admin':
                    this.showAdmin = true;
                    this.disableContainerAdder();
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
                this.$store.dispatch('coursewareContainerAdder', false);
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
            if (newValue) {
                setTimeout(() => {
                    this.initialFocusElement = this.$refs.tabs.getTabButtonByAlias(this.selectedToolbarItem);
                    this.trap = true;
                }, 300);
            }
        },
    },
};
</script>
