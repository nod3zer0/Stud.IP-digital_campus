<template>
    <div v-if="realContentbarSource === ''">
        <MountingPortal mount-to="#responsive-contentbar-container" append>
            <portal-target name="layout-page"></portal-target>
        </MountingPortal>
        <portal to="layout-page">
            <div id="responsive-contentbar" class="contentbar" ref="contentbar">
                <div v-if="hasSidebar" class="contentbar-nav" ref="leftNav">
                    <button :class="sidebarIconClasses" @click.prevent="toggleSidebar" id="toggle-sidebar"
                            :title="$gettext('Sidebar öffnen')">
                        <studip-icon shape="sidebar3" size="24" ref="sidebarIcon"
                                     alt=""></studip-icon>
                    </button>
                </div>
                <div class="contentbar-wrapper-left">
                    <studip-icon :shape="icon" size="24" role="info" class="text-bottom contentbar-icon"></studip-icon>
                    <nav class="contentbar-breadcrumb" ref="breadcrumbs">{{ title }}</nav>
                </div>
                <div class="contentbar-wrapper-right" ref="wrapperRight"></div>
            </div>
        </portal>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'ResponsiveContentBar',
    components: { StudipIcon },
    props: {
        icon: {
            type: String,
            default: 'seminar'
        },
        title: {
            type: String,
            default: ''
        },
        hasSidebar: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            realContentbar: null,
            realContentbarSource: null,
            realContentbarIconContainer: null,
            realContentbarType: null,
            sidebarOpen: false
        }
    },
    computed: {
        sidebarIconClasses() {
            let classes = ['styleless', 'contentbar-button', 'contentbar-button-sidebar'];
            if (this.sidebarOpen) {
                classes.push('contentbar-button-sidebar-open');
            }
            return classes;
        }
    },
    methods: {
        toggleSidebar() {

            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content-wrapper');
            const pageTitle = document.getElementById('page-title-container');
            const html = document.querySelector('html');
            if (this.sidebarOpen) {
                sidebar.classList.add('responsive-hide');
                sidebar.classList.remove('responsive-show');

                if (html.classList.contains('responsive-display') && !html.classList.contains('fullscreen-mode')) {
                    content.style.display = null;
                    pageTitle.style.display = null;
                }

                this.sidebarOpen = false;
            } else {
                sidebar.classList.add('responsive-show');
                sidebar.classList.remove('responsive-hide');
                if (html.classList.contains('responsive-display') && !html.classList.contains('fullscreen-mode')) {
                    // Set a timeout here so that the content "disappears" after slide-in aninmation is finished.
                    setTimeout(() => {
                        content.style.display = 'none';
                        pageTitle.style.display = 'none';
                    }, 300);
                }
                this.sidebarOpen = true;
            }

            // Adjust toggle sidebar button title
            document.getElementById('toggle-sidebar').title = this.sidebarOpen
                ? this.$gettext('Sidebar schließen')
                : this.$gettext('Sidebar öffnen');
        },
        adjustExistingContentbar(responsiveMode) {
            if (this.realContentbar) {
                if (responsiveMode) {
                    this.realContentbar.id = 'responsive-contentbar';
                    this.realContentbar.classList.add('contentbar');
                    if (!this.realContentbar.querySelector('#toggle-sidebar')) {
                        this.realContentbar.querySelector(this.realContentbarIconContainer)
                            .prepend(this.createSidebarIcon());
                    }
                    if (this.realContentbarType === 'courseware') {
                        this.realContentbar.querySelector('.cw-ribbon-wrapper-left')
                            .classList.add('contentbar-wrapper-left');
                        this.realContentbar.querySelector('.cw-ribbon-wrapper-right')
                            .classList.add('contentbar-wrapper-right');
                    }

                    document.getElementById('responsive-contentbar-container').prepend(this.realContentbar);
                } else {
                    this.realContentbar.id = 'contentbar';
                    document.getElementById('toggle-sidebar').remove();

                    if (this.realContentbarType === 'courseware') {
                        this.realContentbar.classList.remove('contentbar');
                        this.realContentbar.querySelector('.cw-ribbon-wrapper-left')
                            .classList.remove('contentbar-wrapper-left');
                        this.realContentbar.querySelector('.cw-ribbon-wrapper-right')
                            .classList.remove('contentbar-wrapper-right');
                    }

                    document.querySelector(this.realContentbarSource).prepend(this.realContentbar);
                }
            }
        },
        createSidebarIcon() {
            const button = document.createElement('button');

            this.sidebarIconClasses.map(className => {
                button.classList.add(className);
            });
            button.id = 'toggle-sidebar';
            button.title = this.$gettext('Sidebar einblenden');
            button.addEventListener('click', (event) => {
                button.classList.toggle('contentbar-button-sidebar-open');
                event.preventDefault();
                this.toggleSidebar();
            })
            const sidebarIcon = document.createElement('img');
            sidebarIcon.src = STUDIP.ASSETS_URL + '/images/icons/blue/sidebar3.svg';
            sidebarIcon.height = 24;
            sidebarIcon.width = 24;
            button.appendChild(sidebarIcon);

            return button;
        }
    },
    mounted() {
        // There's already a PHP contentbar on this page, use it.
        this.$nextTick(() => {
            const realContentbar = document.querySelector('.contentbar:not(#responsive-contentbar)');
            if (realContentbar) {
                this.realContentbar = realContentbar;
                this.realContentbarSource = '#content';
                this.realContentbarIconContainer = '.contentbar-nav';
                this.realContentbarType = 'wiki';
                this.adjustExistingContentbar(true);
            } else {
                this.realContentbarSource = '';

                const cwContentbar = document.querySelector('#contentbar header');
                if (cwContentbar) {
                    this.realContentbar = cwContentbar;
                    this.realContentbarSource = '.cw-structural-element-content > div';
                    this.realContentbarIconContainer = '.cw-ribbon-nav';
                    this.realContentbarType = 'courseware';
                    this.adjustExistingContentbar(true);
                }
            }

            // Add click listener to sidebar so that it can be hidden on clicking an item
            document.querySelectorAll('.sidebar-widget a').forEach(item => {
                item.addEventListener('click', () => this.toggleSidebar());
            });
        })

        // Use courseware contentbar instead of this Vue component.
        STUDIP.Vue.on('courseware-contentbar-mounted', element => {
            this.realContentbar = element.$el.querySelector('header');
            this.realContentbarSource = '.cw-structural-element-content > div';
            this.realContentbarIconContainer = '.cw-ribbon-nav';
            this.realContentbarType = 'courseware';
            this.adjustExistingContentbar(true);

            document.querySelectorAll('.sidebar-widget button span').forEach(item => {
                item.addEventListener('click', () => this.toggleSidebar());
            });
        })

        STUDIP.Vue.on('toggle-focus-mode', (state) => {
            const html = document.querySelector('html');
            if (html.classList.contains('responsive-display') || html.classList.contains('fullscreen-mode')) {
                this.adjustExistingContentbar(!state);
            }
        })

    },
    beforeDestroy() {
        if (this.realContentbar) {
            this.adjustExistingContentbar(false);
        }
    }
}
</script>
