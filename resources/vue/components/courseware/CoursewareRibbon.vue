<template>
    <div :class="{ 'cw-ribbon-wrapper-consume': consumeMode }">
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-top"></div>
        <header class="cw-ribbon" :class="{ 'cw-ribbon-sticky': stickyRibbon, 'cw-ribbon-consume': consumeMode }">
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
                <button
                    v-if="showModeSwitchButton"
                    ref="consumeModeSwitch"
                    class="cw-ribbon-button"
                    :class="[consumeMode ? 'cw-ribbon-button-zoom-out' : 'cw-ribbon-button-zoom']"
                    :title="consumeMode ? textRibbon.fullscreen_off : textRibbon.fullscreen_on"
                     @click="toggleConsumeMode"
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
                :style="{ maxHeight: maxHeight + 'px' }"
                :canEdit="canEdit"
                @deactivate="deactivateToolbar"
            />
        </header>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-bottom"></div>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-spacer"></div>
    </div>
</template>

<script>
import CoursewareRibbonToolbar from './CoursewareRibbonToolbar.vue';

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
    },
    data() {
        return {
            readModeActive: false,
            stickyRibbon: false,
            textRibbon: {
                toolbar: this.$gettext('Inhaltsverzeichnis'),
                fullscreen_on: this.$gettext('Vollbild einschalten'),
                fullscreen_off: this.$gettext('Vollbild ausschalten'),
            },
            unfold: false,
            showTools: false,
        };
    },
    computed: {
        consumeMode() {
            return this.$store.getters.consumeMode;
        },
        toolsActive() {
            return this.$store.getters.showToolbar;
        },
        breadcrumbFallback() {
            return window.outerWidth < 1200;
        },
        maxHeight() {
            if (this.stickyRibbon) {
                return parseInt(window.innerHeight * 0.75);
            } else {
                return parseInt(Math.min(window.innerHeight * 0.75, window.innerHeight - 197));
            }
        }
    },
    methods: {
        toggleConsumeMode() {
            if (!this.consumeMode) {
                this.$store.dispatch('coursewareConsumeMode', true);
                this.$store.dispatch('coursewareSelectedToolbarItem', 'contents');
                this.$store.dispatch('coursewareViewMode', 'read');
            } else {
                this.$store.dispatch('coursewareConsumeMode', false);
            }
        },
        activeToolbar() {
            this.$store.dispatch('coursewareShowToolbar', true);
        },
        deactivateToolbar() {
            this.$store.dispatch('coursewareShowToolbar', false);
        },
        handleScroll() {
            if (window.outerWidth > 767) {
                this.stickyRibbon = window.scrollY > 130 && !this.consumeMode;
            } else {
                this.stickyRibbon = window.scrollY > 75 && !this.consumeMode;
            }
        },
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
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
            this.$refs.consumeModeSwitch.focus();
            if (newState) {
                document.body.classList.add('consume');
            } else {
                document.body.classList.remove('consume');
            }
        }
    }
};
</script>
