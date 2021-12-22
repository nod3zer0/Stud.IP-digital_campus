<template>
    <div
        class="cw-ribbon-tools"
        :class="{ unfold: toolsActive, 'cw-ribbon-tools-consume': consumeMode }"
    >
        <div class="cw-ribbon-tool-content">
            <div class="cw-ribbon-tool-content-nav">
                <ul>
                    <li
                        tabindex="0"
                        ref="focusPoint"
                        :class="{ active: showContents }"
                        @click="displayTool('contents')"
                    >
                        <translate>Inhaltsverzeichnis</translate>
                    </li>
                    <li
                        v-if="!consumeMode && showEditMode && canEdit"
                        tabindex="0"
                        :class="{ active: showBlockAdder }"
                        @click="displayTool('blockadder')"
                    >
                        <translate>Elemente hinzufügen</translate>
                    </li>
                    <li
                        v-if="!consumeMode && displaySettings"
                        tabindex="0"
                        :class="{ active: showAdmin }"
                        @click="displayTool('admin')"
                    >
                        <translate>Einstellungen</translate>
                    </li>
                </ul>
                <button :title="textClose" class="cw-tools-hide-button" @click="$emit('deactivate')"></button>
            </div>
            <div class="cw-ribbon-tool" ref="ribbonContent" @scroll="handleScroll">
                <courseware-tools-contents v-if="showContents" />
                <courseware-tools-blockadder v-if="showBlockAdder" @scrollTop="scrollTop('blockadder')"/>
                <courseware-tools-admin v-if="showAdmin" />
            </div>
        </div>
    </div>
</template>
<script>
import CoursewareToolsAdmin from './CoursewareToolsAdmin.vue';
import CoursewareToolsBlockadder from './CoursewareToolsBlockadder.vue';
import CoursewareToolsContents from './CoursewareToolsContents.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon-toolbar',
    components: {
        CoursewareToolsAdmin,
        CoursewareToolsBlockadder,
        CoursewareToolsContents,
    },
    props: {
        toolsActive: Boolean,
        canEdit: Boolean,
    },
    data() {
        return {
            showContents: true,
            showAdmin: false,
            showBlockAdder: false,
            textClose: this.$gettext('schließen'),
            scrollPos: {
                contents: 0,
                admin: 0,
                blockadder: 0
            }
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
        }),
        showEditMode() {
            return this.viewMode === 'edit';
        },
        displaySettings() {
            let user = this.userById({ id: this.userId });
            return this.context.type === 'courses' && (this.isTeacher || ['root', 'admin'].includes(user.attributes.permission));
        },
        isTeacher() {
            return this.userIsTeacher;
        },
    },
    methods: {
        displayTool(tool) {
            this.showContents = false;
            this.showAdmin = false;
            this.showBlockAdder = false;

            switch (tool) {
                case 'contents':
                    this.showContents = true;
                    this.disableContainerAdder();
                    break;
                case 'admin':
                    this.showAdmin = true;
                    this.disableContainerAdder();
                    break;
                case 'blockadder':
                    this.showBlockAdder = true;
                    break;
            }
            this.updateScrollPos();
        },
        disableContainerAdder() {
            if (this.containerAdder !== false) {
                this.$store.dispatch('coursewareContainerAdder', false);
            }
        },
        scrollTop(tool) {
            switch (tool) {
                case 'contents':
                    this.$set(this.scrollPos, 'contents', 0);
                    break;
                case 'admin':
                    this.$set(this.scrollPos, 'admin', 0);
                    break;
                case 'blockadder':
                    this.$set(this.scrollPos, 'blockadder', 0);
                    break;
            }
            this.updateScrollPos();
        },
        handleScroll: function() {
            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            this.timeout = setTimeout(() => {
                var currentScrollPos = this.$refs.ribbonContent.scrollTop;
                if (this.showContents) {
                    this.$set(this.scrollPos, 'contents', currentScrollPos);
                }
                if (this.showAdmin) {
                    this.$set(this.scrollPos, 'admin', currentScrollPos);
                }
                if (this.showBlockAdder) {
                    this.$set(this.scrollPos, 'blockadder', currentScrollPos);
                }
            }, 100);
        },
        updateScrollPos() {
            var scrollPos = 0;
            if (this.showContents) {
                scrollPos = this.scrollPos.contents;
            }
            if (this.showAdmin) {
                scrollPos = this.scrollPos.admin;
            }
            if (this.showBlockAdder) {
                scrollPos = this.scrollPos.blockadder;
            }
            this.$nextTick(function() {
                 $(this.$refs.ribbonContent).stop().animate({
                    scrollTop: scrollPos
                }, 100);
            });
        }
    },
    mounted () {
        this.updateScrollPos();
    },
    watch: {
        adderStorage(newValue) {
            if (Object.keys(newValue).length !== 0) {
                this.displayTool('blockadder');
            }
        },
        consumeMode(newValue) {
            if (newValue) {
                this.displayTool('contents');
            }
        },
        containerAdder(newValue) {
            if (newValue === true) {
                this.displayTool('blockadder');
            }
        },
        showEditMode(newValue) {
            if (!newValue) {
                this.displayTool('contents');
            }
        }
    },
};
</script>
