import draggable from 'vuedraggable';
import { mapActions, mapState } from 'vuex';

export default {
    components: {
        draggable,
    },
    data: () => ({
        order: [],
    }),
    computed: {
        ...mapState('contentmodules', [
            'categories',
            'filterCategory',
            'highlighted',
            'modules',
            'view',
        ]),
        activeModules() {
            return this.sortedModules.filter(module => module.active);
        },
        inactiveModules() {
            return this.sortedModules.filter(module => !module.active);
        },
        sortedModules: {
            get() {
                return Object.values(this.modules)
                    .filter(module => {
                        return this.filterCategory === null
                            || this.filterCategory === module.category;
                    })
                    .sort(function (a, b) {
                        if (a.active && !b.active) {
                            return -1;
                        } else if (!a.active && b.active) {
                            return 1;
                        } else if (a.active) {
                            return a.position - b.position;
                        } else {
                            return a.displayname.localeCompare(b.displayname);
                        }
                    });
            },
            set(modules) {
                let position = 0;
                for (const key in modules) {
                    modules[key].position = position++;
                }
                this.exchangeModules(modules).then((output) => {
                    if (output.tabs) {
                        $('.tabs_wrapper').replaceWith(output.tabs);
                    }
                });
            },
        },
    },
    methods: {
        ...mapActions('contentmodules', [
            'changeView',
            'exchangeModules',
            'setModuleActive',
            'setModuleVisible',
            'swapModules',
        ]),
        keyboardHandler(event, module) {
            const activeIndex = this.activeModules.findIndex(m => m.id === module.id);

            let otherModule = null;
            if (event.key === 'ArrowUp' && activeIndex > 0) {
                otherModule = this.activeModules[activeIndex - 1];
            } else if (event.key === 'ArrowDown' && activeIndex !== this.activeModules.length - 1) {
                otherModule = this.activeModules[activeIndex + 1];
            }

            if (otherModule === null) {
                return;
            }

            event.preventDefault();

            this.swapModules({
                moduleA: module,
                moduleB: otherModule,
            }).then((output) => {
                if (output.tabs) {
                    $('.tabs_wrapper').replaceWith(output.tabs);
                }
            }).then(() => {
                this.$nextTick(() => {
                    this.$refs[`draghandle-${module.id}`][0].focus();
                });
            });
        },
        toggleModuleActivation(module) {
            module.pulse = true;
            this.setModuleActive({
                moduleId: module.id,
                active: !module.active,
            }).then((output) => {
                if (output.tabs) {
                    $('.tabs_wrapper').replaceWith(output.tabs);
                }
                module.pulse = false;
            });
        },
        toggleModuleVisibility(module) {
            this.setModuleVisible({
                moduleId: module.id,
                visible: module.visibility === 'tutor',
            }).then((output) => {
                if (output.tabs) {
                    $('.tabs_wrapper').replaceWith(output.tabs);
                }
            });
        },
        getRenameURL(module) {
            return STUDIP.URLHelper.getURL(`dispatch.php/course/contentmodules/rename/${module.id}`);
        },
        getDescriptionURL(module) {
            return STUDIP.URLHelper.getURL(`dispatch.php/course/contentmodules/info/${module.id}`);
        },
        getModuleCSSClasses(module, active= null) {
            let classes = [];
            classes.push(module.pulse ? 'pulse' : '');

            if (!(active ?? module.active)) {
                classes.push('inactive');
            } else {
                classes.push(module.visibility === 'tutor' ? 'visibility-invisible' : 'visibility-visible');
            }


            return classes.join(' ');
        },
    },
};
