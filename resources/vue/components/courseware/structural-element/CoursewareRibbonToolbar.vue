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
import CoursewareToolsContents from './CoursewareToolsContents.vue';
import { FocusTrap } from 'focus-trap-vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon-toolbar',
    components: {
        CoursewareTabs,
        CoursewareTab,
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
        this.scrollToCurrent();
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
