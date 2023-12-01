<template>
    <div :class="{ 'cw-ribbon-wrapper-consume': consumeMode }" :id="isContentBar ? 'contentbar' : null" >
        <div v-show="stickyRibbon" class="cw-ribbon-sticky-top"></div>
        <header :id="isContentBar ? 'cw-ribbon' : null" class="cw-ribbon" :class="{ 'cw-ribbon-sticky': stickyRibbon, 'cw-ribbon-consume': consumeMode }">
            <div class="cw-ribbon-wrapper-left">
                <nav class="cw-ribbon-nav" :class="buttonsClass">
                    <slot name="buttons" />
                </nav>
                <nav class="cw-ribbon-breadcrumb">
                    <ul>
                        <slot v-if="breadcrumbFallback" name="breadcrumbFallback" />
                        <slot v-else name="breadcrumbList" />
                    </ul>
                </nav>
            </div>
            <div class="cw-ribbon-wrapper-right">
                <button
                    v-if="showToolbarButton"
                    class="cw-ribbon-button cw-ribbon-button-menu"
                    :title="textRibbon.toolbar"
                    @click.prevent="activeToolbar"
                >
                </button>
                <slot name="menu" />
            </div>
            <div v-if="consumeMode" class="cw-ribbon-consume-bottom"></div>
            <courseware-ribbon-toolbar
                v-if="showTools"
                :toolsActive="unfold"
                :stickyRibbon="stickyRibbon"
                :class="{ 'cw-ribbon-tools-sticky': stickyRibbon }"
                :style="{ height: toolbarHeight + 'px' }"
                :canEdit="canEdit"
                @deactivate="deactivateToolbar"
                @blockAdded="$emit('blockAdded')"
            />
        </header>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-bottom"></div>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-spacer"></div>
    </div>
</template>

<script>
import CoursewareRibbonToolbar from './CoursewareRibbonToolbar.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon',
    components: {
        CoursewareRibbonToolbar,
    },
    props: {
        canEdit: Boolean,
        showToolbarButton: {
            default: true,
            type: Boolean
        },
        showModeSwitchButton: {
            default: true,
            type: Boolean
        },
        buttonsClass: String,
        isContentBar: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            readModeActive: false,
            stickyRibbon: false,
            textRibbon: {
                toolbar: this.$gettext('Inhaltsverzeichnis'),
                fullscreen_on: this.$gettext('Fokusmodus einschalten'),
                fullscreen_off: this.$gettext('Fokusmodus ausschalten'),
            },
            unfold: false,
            showTools: false,
        };
    },
    computed: {
        ...mapGetters({
            consumeMode: 'consumeMode',
            toolsActive: 'showToolbar'
        }),
        breadcrumbFallback() {
            return window.outerWidth < 1200;
        },
        toolbarHeight() {
            if (this.stickyRibbon) {
                return parseInt(window.innerHeight * 0.75);
            } else {
                return parseInt(Math.min(window.innerHeight * 0.75, window.innerHeight - 197));
            }
        }
    },
    methods: {
        ...mapActions({
            coursewareConsumeMode: 'coursewareConsumeMode',
            coursewareSelectedToolbarItem: 'coursewareSelectedToolbarItem',
            coursewareViewMode: 'coursewareViewMode',
            coursewareShowToolbar: 'coursewareShowToolbar'

        }),
        toggleConsumeMode() {
            STUDIP.eventBus.emit('toggle-focus-mode', !this.consumeMode);
            if (!this.consumeMode) {
                document.body.classList.add('consuming_mode');
                this.coursewareConsumeMode(true);
                this.coursewareSelectedToolbarItem('contents');
                this.coursewareViewMode('read');
            } else {
                this.coursewareConsumeMode(false);
                document.body.classList.remove('consuming_mode');
            }
        },
        activeToolbar() {
            this.coursewareShowToolbar(true);
        },
        deactivateToolbar() {
            this.coursewareShowToolbar(false);
        },
        handleScroll() {
            if (window.outerWidth > 767) {
                this.stickyRibbon = window.scrollY > 128 && !this.consumeMode;
            } else {
                this.stickyRibbon = window.scrollY > 75 && !this.consumeMode;
            }
        },
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
        if (this.isContentBar) {
            STUDIP.eventBus.emit('courseware-contentbar-mounted', this);
        }

        this.globalOn('switch-focus-mode', (state) => {
            if (state !== this.consumeMode) {
                this.toggleConsumeMode();
            }
        });
    },
    beforeDestroy() {
        STUDIP.eventBus.off('switch-focus-mode');
    },
    watch: {
        toolsActive(newState, oldState) {
            let view = this;
            if(newState) {
                this.showTools = true;
                setTimeout(() => {view.unfold = true}, 10);
            } else {
                this.unfold = false;
                setTimeout(() => {
                    if(!view.toolsActive) {
                        view.showTools = false;
                    }
                }, 800);
            }
        },
        consumeMode(newState) {
            if (newState) {
                document.body.classList.add('consuming_mode');
            } else {
                document.body.classList.remove('consuming_mode');
            }
        }
    }
};
</script>
