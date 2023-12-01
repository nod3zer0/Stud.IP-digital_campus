<template>
    <div class="cw-toolbar-wrapper">
        <div id="cw-toolbar" class="cw-toolbar" :style="toolbarStyle">
            <div v-if="showTools" class="cw-toolbar-tools" :class="{ unfold: unfold, hd: isHd, wqhd: isWqhd}">
                <div class="cw-toolbar-button-wrapper">
                    <button
                        class="cw-toolbar-button"
                        :class="{active: activeTool === 'blockAdder'}"
                        :title="$gettext('Blöcke hinzufügen')"
                        @click="activateTool('blockAdder')"
                    >
                        {{ $gettext('Blöcke') }}
                    </button>
                    <button
                        class="cw-toolbar-button"
                        :class="{active: activeTool === 'containerAdder'}"
                        :title="$gettext('Abschnitte hinzufügen')"
                        @click="activateTool('containerAdder')"
                    >
                        {{ $gettext('Abschnitte') }}
                    </button>
                    <button
                        class="cw-toolbar-button"
                        :class="{active: activeTool === 'clipboard'}"
                        :title="$gettext('Block Merkliste')"
                        @click="activateTool('clipboard')"
                    >
                        {{ $gettext('Merkliste') }}
                    </button>
                    <button
                        class="cw-toolbar-button cw-toolbar-button-toggle cw-toolbar-button-toggle-out"
                        :title="$gettext('Werkzeugleiste einklappen')"
                        @click="toggleToolbar"
                    ></button>
                </div>
                <courseware-toolbar-blocks v-if="activeTool === 'blockAdder'" />
                <courseware-toolbar-containers v-if="activeTool === 'containerAdder'" />
                <courseware-toolbar-clipboard v-if="activeTool === 'clipboard'" />
            </div>
            <button
                v-else
                class="cw-toolbar-button cw-toolbar-button-toggle cw-toolbar-button-toggle-in"
                :title="$gettext('Werkzeugleiste ausklappen')"
                @click="toggleToolbar"
            ></button>
            <div class="cw-toolbar-spacer-right"></div>
        </div>
    </div>
</template>

<script>
import CoursewareToolbarBlocks from './CoursewareToolbarBlocks.vue';
import CoursewareToolbarContainers from './CoursewareToolbarContainers.vue';
import CoursewareToolbarClipboard from './CoursewareToolbarClipboard.vue';

export default {
    name: 'courseware-toolbar',
    components: {
        CoursewareToolbarBlocks,
        CoursewareToolbarContainers,
        CoursewareToolbarClipboard
    },
    data() {
        return {
            toolsActive: true,
            unfold: true,
            showTools: true,
            toolbarTop: 0,
            activeTool: 'blockAdder',

            windowWidth: window.outerWidth,
            windowInnerHeight: window.innerHeight
        };
    },
    computed: {
        toolbarStyle() {
            const footerHeight = document.getElementById('main-footer').getBoundingClientRect().height;
            const scrollTopStyles = window.getComputedStyle(document.getElementById('scroll-to-top'));
            const scrollTopHeight = parseInt(scrollTopStyles['height']) + parseInt(scrollTopStyles['padding-top']) + parseInt(scrollTopStyles['padding-bottom']);
            let height = parseInt(
                Math.min(this.windowInnerHeight * 0.9, this.windowInnerHeight - this.toolbarTop - scrollTopHeight - footerHeight)
            );

            return {
                height: height + 'px',
                minHeight: height + 'px',
                top: this.toolbarTop + 'px',
            };
        },
        toolbarHeader() {
            let header = '';
            if (this.activeTool === 'blockAdder') {
                header = this.$gettext('Block hinzufügen');
            }
            if (this.activeTool === 'containerAdder') {
                header = this.$gettext('Abschnitt hinzufügen');
            }

            return header;
        },
        isHd() {
            return this.windowWidth >= 1920;
        },
        isWqhd() {
            return this.windowWidth >= 2560;
        },
    },
    methods: {
        toggleToolbar() {
            this.toolsActive = !this.toolsActive;
        },
        activateTool(tool) {
            this.activeTool = tool;
        },
        updateToolbarTop() {
            const responsiveContentbar = document.getElementById('responsive-contentbar');
            if (responsiveContentbar) {
                const contentbarRect = this.responsiveContentbar.getBoundingClientRect();
                this.toolbarTop = contentbarRect.bottom + 35;
                return;
            }

            const ribbon = document.getElementById('cw-ribbon') ?? document.getElementById('contentbar');
            if (ribbon) {
                const contentbarRect = ribbon.getBoundingClientRect();
                if (ribbon.classList.contains("cw-ribbon-sticky")) {
                    this.toolbarTop = contentbarRect.bottom + 16;
                } else {
                    this.toolbarTop = contentbarRect.bottom + 15;
                }
            }
            
        },
        onResize() {
            this.windowWidth = window.outerWidth;
            this.windowInnerHeight = window.innerHeight;
        }
    },
    mounted() {
        this.updateToolbarTop();
        this.$nextTick(() => {
            window.addEventListener('scroll', this.updateToolbarTop);
            window.addEventListener('resize', this.onResize);
        });
    },
    beforeDestroy() { 
        window.removeEventListener('scroll', this.updateToolbarTop);
        window.removeEventListener('resize', this.onResize); 
    },

    watch: {
        toolsActive(newState, oldState) {
            let view = this;
            if (newState) {
                this.showTools = true;
                setTimeout(() => {
                    view.unfold = true;
                }, 10);
            } else {
                this.unfold = false;
                setTimeout(() => {
                    if (!view.toolsActive) {
                        view.showTools = false;
                    }
                }, 600);
            }
        },
    },
};
</script>
