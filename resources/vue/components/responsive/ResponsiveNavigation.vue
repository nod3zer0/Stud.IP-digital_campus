<template>
    <div role="navigation" ref="container" v-if="menuNeeded">
        <div class="responsive-navigation-header">
            <transition name="slide" appear>
                <button id="responsive-navigation-button" class="styleless"
                        :aria-label="showMenu
                            ? $gettext('Navigation schließen')
                            : $gettext('Navigation öffnen')"
                        @click.prevent="toggleMenu"
                        @keydown.prevent.space="toggleMenu"
                        @keydown.prevent.enter="toggleMenu">
                    <studip-icon shape="hamburger" role="info_alt"
                                 :alt="showMenu ? $gettext('Navigation schließen') : $gettext('Navigation öffnen')"
                                 :size="iconSize" :class="showMenu ? 'menu-open' : 'menu-closed'">
                    </studip-icon>
                </button>
            </transition>
        </div>
        <transition name="appear" appear>
            <nav v-show="showMenu" id="responsive-navigation-items" class="responsive" ref="navigation"
                 :aria-expanded="showMenu">
                <header v-if="me.username !== 'nobody'">
                    <template v-if="!avatarMenuOpen">
                        <section class="profile-info">
                            <div class="profile-pic">
                                <img :src="me.avatar"
                                     @click.prevent="toggleAvatarMenu"
                                     :title="$gettext('Profilnavigation öffnen')"
                                     :aria-label="$gettext('Profilnavigation öffnen')">
                            </div>
                            <div class="profile-data">
                                <div>{{ me.fullname }}</div>
                                <div>{{ me.email }} ({{ me.perm }})</div>
                            </div>
                        </section>
                        <section class="open-avatarmenu">
                            <button class="styleless" ref="openAvatarmenu"
                                    @keydown.prevent.enter="toggleAvatarMenu"
                                    @keydown.prevent.space="toggleAvatarMenu"
                                    @click.prevent="toggleAvatarMenu"
                                    :title="$gettext('Profilnavigation öffnen')"
                                    :aria-label="$gettext('Profilnavigation öffnen')">
                                <studip-icon shape="arr_1right" role="info_alt" :size="iconSize" alt=""></studip-icon>
                            </button>
                        </section>
                    </template>
                    <template v-else>
                        <focus-trap :active="true" :return-focus-on-deactivate="true"
                                    :click-outside-deactivates="true">
                            <div>
                                <div class="close-avatarmenu">
                                    <button class="styleless" ref="closeAvatarmenu"
                                            @keydown.prevent.enter="toggleAvatarMenu"
                                            @keydown.prevent.space="toggleAvatarMenu"
                                            @click="toggleAvatarMenu"
                                            :title="$gettext('Profilnavigation schließen')"
                                            :aria-label="$gettext('Profilnavigation schließen')">
                                        <studip-icon shape="arr_1left" role="info_alt" :size="iconSize"></studip-icon>
                                    </button>
                                </div>
                                <ul class="avatar-navigation">
                                    <navigation-item v-for="(item, index) in avatarNavigation.children" :key="index"
                                                     :item="item"></navigation-item>
                                </ul>
                            </div>
                        </focus-trap>
                    </template>
                </header>
                <ul class="main-navigation">
                    <li v-if="currentParent != null" class="navigation-item navigation-up">
                        <div class="navigation-title" :title="$gettext('Zum Start')"
                             :aria-label="$gettext('Zum Start')">
                            <button class="styleless" @click="moveTo('/')" @keydown.prevent.enter="moveTo('/')"
                                    @keydown.prevent.space="moveTo('/')">
                                <div class="navigation-icon">
                                    <studip-icon shape="arr_2up" role="info_alt" :size="iconSize - 4"></studip-icon>
                                </div>
                                <div class="navigation-text">
                                    {{ $gettext('Start') }}
                                </div>
                            </button>
                        </div>
                    </li>
                    <li v-if="currentParent != null" class="navigation-item navigation-current">
                        <div class="navigation-title">
                            <button class="styleless"
                                    @click="moveTo(currentParent.path)"
                                    @keydown.prevent.enter="moveTo(currentParent.path)"
                                    @keydown.prevent.space="moveTo(currentParent.path)"
                                    :title="$gettext('Eine Ebene höher')"
                                    :aria-label="$gettext('Eine Ebene höher')">
                                <div class="navigation-icon">
                                    <studip-icon shape="arr_1left" role="info_alt" :size="iconSize - 4"></studip-icon>
                                </div>
                                <div class="navigation-text">
                                    {{ $gettext('Eine Ebene höher') }}
                                </div>
                            </button>
                        </div>
                    </li>
                    <navigation-item v-for="(item, index) in currentNavigation.children" :key="index"
                                     :item="item" :active="item.active"></navigation-item>
                </ul>
            </nav>
        </transition>
        <responsive-content-bar v-if="(isResponsive || isFullscreen) && !isFocusMode"
                                :has-sidebar="hasSidebar" :title="contentbarTitle" :aria-label="contentbarTitle"
                                ref="contentbar"></responsive-content-bar>
        <responsive-skip-links v-if="(isResponsive || isFullscreen) && hasSkiplinks" :links="skipLinks"></responsive-skip-links>
    </div>
</template>

<script>
import NavigationItem from './NavigationItem.vue';
import StudipIcon from '../StudipIcon.vue';
import ResponsiveContentBar from './ResponsiveContentBar.vue';
import ResponsiveSkipLinks from './ResponsiveSkipLinks.vue';
import { FocusTrap } from 'focus-trap-vue';

export default {
    name: 'ResponsiveNavigation',
    components: { ResponsiveContentBar, StudipIcon, NavigationItem, ResponsiveSkipLinks, FocusTrap },
    props: {
        me: {
            type: Object,
            required: true
        },
        context: {
            type: String,
            default: ''
        },
        hasSidebar: {
            type: Boolean,
            default: true
        },
        navigation: {
            type: Object,
            required: true,
        }
    },
    data() {
        let studipNavigation = this.sanitizeNavigation(this.navigation);

        return {
            studipNavigation,
            isResponsive: false,
            isFullscreen: false,
            isFocusMode: false,
            headerMagic: false,
            iconSize: 28,
            showMenu: false,
            activeItem: this.navigation.activated.at(-1) ?? 'start',
            currentNavigation: this.findItem(this.navigation.activated.at(0) ?? 'start', studipNavigation),
            initialNavigation: {},
            initialTitle: '',
            isAdmin: ['root','admin'].includes(this.me.perm),
            courses: [],
            avatarNavigation: studipNavigation.avatar,
            avatarMenuOpen: false,
            observer: null,
            hasSkiplinks: document.querySelector('#skiplink_list') !== null,
            hasContentbar: false,
            contentbarTitle: ''
        }
    },
    computed: {
        // Current navigation title, supplemented by context title if available
        currentTitle() {
            return this.context !== '' && this.currentNavigation.path.indexOf('my_courses/') !== -1 ?
                this.context : '';
        },
        // The parent element of the current navigation item
        currentParent() {
            return this.currentNavigation.parent
                 ? this.findItem(this.currentNavigation.parent, this.studipNavigation)
                 : null;
        },
        /*
         * Is the responsive navigation menu needed (because we are in responsive or fullscreen mode)?
         */
        menuNeeded() {
            return !this.isFocusMode
                && (this.isResponsive || this.isFullscreen || this.headerMagic);
        },

        skipLinks() {
            let links = [
                { url: '#responsive-navigation-button', label: this.$gettext('Hauptnavigation') }
            ];

            if (this.isFullscreen) {
                if (this.hasSidebar) {
                    let name = '';
                    if (document.getElementById('sidebar').classList.contains('responsive-show')) {
                        name = this.$gettext('Sidebar ausblenden');
                    } else {
                        name = this.$gettext('Sidebar anzeigen');
                    }
                    links.push({ url: '#toggle-sidebar', label: name });
                }
            }

            return links;
        }
    },
    methods: {
        /**
         * Find a navigation item specified by given path in the given navigation structure
         * @param path
         * @param navigation
         * @returns {{parent: null, path: string, visible: boolean, children, icon: null, active: boolean, title, url}|null}
         */
        findItem(path, navigation) {
            // Some "pseudo" navigation directly at root level.
            if (path === '/' || path === 'start') {
                return {
                    active: true,
                    children: navigation,
                    icon: null,
                    parent: null,
                    path: '/',
                    title: navigation.start.title,
                    url: navigation.start.url,
                    visible: true
                };
            } else {
                // Found requested item at current level.
                if (navigation[path]) {
                    return navigation[path];
                } else {
                    // Special handling for first navigation level, we have no "children" attribute here.
                    if (navigation.start) {

                        let found = null;
                        for (const sub in navigation) {
                            found = this.findItem(path, navigation[sub]);
                            if (found) {
                                break;
                            }
                        }
                        return found;

                    } else if (navigation.children) {

                        // Found requested item as child of current one.
                        if (navigation.children[path]) {
                            return navigation.children[path];

                            // Recurse deeper.
                        } else {

                            let found = null;
                            for (const sub in navigation.children) {
                                found = this.findItem(path, navigation.children[sub]);
                                if (found) {
                                    break;
                                }
                            }
                            return found;

                        }
                        // No children left to search through, we are doomed.
                    } else {
                        return null;
                    }

                }
            }
        },
        /**
         * Open or close the navigation menu
         */
        toggleMenu() {

            this.showMenu = !this.showMenu;

            if (!this.showMenu && this.avatarMenuOpen) {
                this.toggleAvatarMenu();
            }

            this.$nextTick(() => {
                if (this.showMenu && !this.headerMagic) {
                    this.currentNavigation = this.initialNavigation;

                    if (this.isResponsive) {
                        this.$refs.navigation.style.height = (document.documentElement.clientHeight - 42) + 'px';
                    }
                    document.getElementById('header-links').style.display = 'none';
                } else {
                    document.getElementById('header-links').style.display = null;
                }
            })
        },
        /**
         * Turn compact navigation mode on or off
         * @param state
         */
        setCompactNavigation(state) {
            const sidebar = document.getElementById('sidebar');
            const cache = STUDIP.Cache.getInstance('responsive.');

            if (state) {
                if (this.hasContentbar) {
                    document.getElementById('responsive-toggle-focusmode').style.display = 'block';
                }
                document.documentElement.classList.add('fullscreen-mode');
                cache.set('fullscreen-mode', true);

                const siteTitle = document.getElementById('site-title');
                if (siteTitle) {
                    siteTitle.dataset.originalTitle = siteTitle.textContent.trim();
                    siteTitle.textContent = this.initialTitle;
                }

                sidebar.ariaHidden = 'true';

            } else {
                document.documentElement.classList.remove('fullscreen-mode');
                sidebar?.classList.remove('responsive-show', 'fullscreen-mode');
                this.showMenu = false;
                cache.remove('fullscreen-mode');
                document.getElementById('responsive-toggle-focusmode').style.display = 'none';
                document.body.style.display = null;

                const siteTitle = document.getElementById('site-title');
                if (siteTitle.dataset.originalTitle) {
                    siteTitle.textContent = siteTitle.dataset.originalTitle.trim();
                }

                sidebar.ariaHidden = '';
            }

            this.isFullscreen = state;

            if (!this.isResponsive) {
                this.moveHelpbar();
            }
        },
        getUrl(url) {
            return STUDIP.URLHelper.getURL(url, {}, true);
        },
        /**
         * Move to another item in navigation structure, specified by path
         * @param path
         */
        moveTo(path) {
            this.avatarMenuOpen = false;
            this.currentNavigation = this.findItem(path ? path : '/', this.studipNavigation);
            this.$nextTick(() => {
                const current = document.querySelector('.navigation-current')
                    ? document.querySelector('.navigation-current .navigation-title button')
                    : document.querySelector('.navigation-item .navigation-title a');
                current.focus();
            })
        },
        /**
         * Relocate the helpbar icon to another DOM location
         * as it is part of top bar in responsive view.
         */
        moveHelpbar() {
            let tag = 'div';
            let target = '.tabs_wrapper';
            let before = '#non-responsive-toggle-fullscreen';
            if (this.isFullscreen || this.isResponsive) {
                tag = 'li';
                target = '#header-links ul';
                before = '#responsive-toggle-fullscreen';
            }

            let helpBar = document.createElement(tag);
            const realHelpBar = document.querySelector('.helpbar-container');

            const helpbarIcon = document.querySelector('#helpbar_icon');

            if (helpbarIcon) {
                const realIcon = helpbarIcon.querySelector('img.icon-shape-question-circle');
                realIcon.src = (this.isFullscreen || this.isResponsive)
                             ? realIcon.src.replace('blue', 'white')
                             : realIcon.src.replace('white', 'blue');

                helpBar.appendChild(helpbarIcon);
                helpBar.appendChild(document.querySelector('div.helpbar'));
                helpBar.classList.add('helpbar-container');
                document.querySelector(target).insertBefore(helpBar, document.querySelector(before));
                realHelpBar.remove();
            }
        },
        /**
         * Show or hide avatar navigation menu.
         */
        toggleAvatarMenu() {
            this.avatarMenuOpen = !this.avatarMenuOpen;

            // Focus first menu entry.
            if (this.avatarMenuOpen) {
                this.$nextTick(() => {
                    document.querySelector('.avatar-navigation .navigation-item .navigation-title a').focus();
                });
            }
        },
        onChangeViewMode(tagName, classes) {
            const classList = classes.split(' ');

            switch (tagName) {
                // watch for "consuming_mode" or "fixed" class changes
                case 'BODY':
                    if (classList.includes('consuming_mode')) {
                        this.isFocusMode = true;
                        STUDIP.eventBus.emit('consuming-mode-enabled');
                        this.setCompactNavigation(false);
                    } else {
                        this.isFocusMode = false;
                        STUDIP.eventBus.emit('consuming-mode-disabled');
                    }
                    if (classList.includes('fixed')) {
                        this.headerMagic = true;
                        STUDIP.eventBus.emit('header-magic-enabled');
                    } else {
                        this.headerMagic = false;
                        this.showMenu = false;
                        STUDIP.eventBus.emit('header-magic-disabled');
                    }
                    break;
                // Watch for "responsive-display" and "fullscreen-mode" class changes
                case 'HTML':
                    if (classList.includes('responsive-display')) {
                        this.isResponsive = true;

                        if (classList.includes('fullscreen-mode')) {
                            this.setCompactNavigation(false);
                        }

                        STUDIP.eventBus.emit('responsive-display-enabled');
                        this.$nextTick(() => {
                            this.moveHelpbar();
                        })
                    } else {
                        this.isResponsive = false;
                        STUDIP.eventBus.emit('responsive-display-disabled');
                        this.$nextTick(() => {
                            this.moveHelpbar();
                        })
                    }

                    if (classList.includes('fullscreen-mode')) {
                        this.isFullscreen = true;

                        STUDIP.eventBus.emit('fullscreen-enabled');
                    } else {
                        this.isFullscreen = false;
                        STUDIP.eventBus.emit('fullscreen-disabled');
                    }
                    break;
                case 'HEADER':
                    this.isFocusMode = classList.includes('cw-ribbon-consume');
            }
        },
        sanitizeNavigation(navigation) {
            const cache = STUDIP.Cache.getInstance('responsive.');

            // No navigation object was sent, read from cache
            if (navigation.navigation === undefined) {
                return cache.get('navigation');
            }

            // Navigation object was sent, store in cache
            cache.set('navigation', navigation.navigation);
            STUDIP.Cookie.set('responsive-navigation-hash', navigation.hash);

            return navigation.navigation;
        },
        captureOutsideClick(target) {
            if (this.showMenu && target !== null) {
                if (!this.$refs.container.contains(target)) {
                    this.toggleMenu();
                }
            }
        }
    },
    watch: {
        showMenu(newState) {
            if (newState) {
                // Click outside navigation menu should close it.
                document.addEventListener('click', event => {
                    this.captureOutsideClick(event.target);
                }, true);
            } else {
                document.removeEventListener('click', this.captureOutsideClick(null), true)
            }
        }
    },
    mounted() {
        const cache = STUDIP.Cache.getInstance('responsive.');
        const fullscreen = cache.get('fullscreen-mode') ?? false;
        const fullscreenDocument = document.documentElement.classList.contains('fullscreen-mode');

        if (document.documentElement.classList.contains('responsive-display')) {
            this.isResponsive = true;
        }

        this.isFullscreen = !this.isResponsive && (fullscreenDocument || fullscreen);
        if (this.isFullscreen && !fullscreenDocument) {
            this.setCompactNavigation(true);
        }
        // Re-structure some DOM elements
        this.$nextTick(() => {
            if (this.isResponsive || (this.isFullscreen && !this.isFocusMode)) {
                this.moveHelpbar();

                this.contentbarTitle = document.querySelector('.sidebar-image .sidebar-title')?.textContent;
                const siteTitle = document.getElementById('site-title');
                if (siteTitle) {
                    siteTitle.dataset.originalTitle = siteTitle.textContent.trim();
                    siteTitle.textContent = this.initialTitle;
                }
            }
        });

        // Pressing escape should close an open navigation.
        window.addEventListener('keydown', event => {
            if (event.key === 'Escape' && this.showMenu) {
                this.toggleMenu();
            }
        });

        this.initialNavigation = this.currentNavigation;
        this.initialTitle = this.initialNavigation.title;

        this.globalOn('responsive-navigation-move-to', path => {
            this.moveTo(path);
        });

        // Listen to changes in fullscreen setting
        this.globalOn('toggle-compact-navigation', value => {
            this.setCompactNavigation(value);
        });

        /*
         * Use an observer for html and body in order to check
         * whether we move into consuming mode or leave responsive mode.
         */
        this.observer = new MutationObserver(mutations => {
            for (const m of mutations) {
                const newValue = m.target.getAttribute(m.attributeName);
                this.onChangeViewMode(m.target.tagName, newValue);
            }
        });

        // Observe <html> for class changes.
        this.observer.observe(document.documentElement, {
            attributes: true,
            attributeOldValue : false,
            attributeFilter: ['class']
        });

        // Observe <body> for class changes.
        this.observer.observe(document.body, {
            attributes: true,
            attributeOldValue : false,
            attributeFilter: ['class']
        });

        this.globalOn('has-contentbar', value => {
            this.hasContentbar = value;
            if (value && this.isFullscreen) {
                document.getElementById('responsive-toggle-focusmode').style.display = 'block';
            }
        });

        // Observe courseware contentbar for consuming mode.
        this.globalOn('courseware-contentbar-mounted', element => {
            if (this.isFullscreen) {
                document.getElementById('responsive-toggle-focusmode').style.display = 'block';
            }
            this.observer.observe(element.$el.querySelector('header.cw-ribbon'), {
                attributes: true,
                attributeOldValue : false,
                attributeFilter: ['class']
            })
        });
    },
    beforeDestroy() {
        this.observer.disconnect();
        document.getElementById('header-links').style.display = null;
        STUDIP.eventBus.off('responsive-navigation-move-to');
        STUDIP.eventBus.off('toggle-compact-navigation');
        STUDIP.eventBus.off('has-contentbar');
        STUDIP.eventBus.off('courseware-contentbar-mounted');
    }
}
</script>

<style lang="scss">
@media not prefers-reduced-motion {

    .slide-enter-active,
    .slide-leave-active {
        transition: all .3s ease;
    }

    .slide-enter-to,
    .slide-leave-from,
    .slide-leave {
        margin-left: -3px;
    }

    .slide-enter,
    .slide-enter-from,
    .slide-leave-to {
        margin-left: -50px;
    }

    .appear-enter-active,
    .appear-leave-active {
        transition: opacity .3s ease;
    }

    .appear-leave,
    .appear-leave-from,
    .appear-enter-to {
        opacity: 1;
    }

    .appear-enter,
    .appear-enter-from,
    .appear-leave-to {
        opacity: 0;
    }
}
</style>
