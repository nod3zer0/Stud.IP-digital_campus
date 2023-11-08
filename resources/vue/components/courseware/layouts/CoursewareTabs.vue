<template>
    <div class="cw-tabs">
        <div role="tablist" class="cw-tabs-nav">
            <button
                v-for="(tab, index) in tabs"
                :key="index"
                :class="[
                    tab.isActive ? 'is-active' : '',
                    tab.icon !== '' && tab.name !== '' ? 'cw-tabs-nav-icon-text-' + tab.icon : '',
                    tab.icon !== '' && tab.name === '' ? 'cw-tabs-nav-icon-solo-' + tab.icon : '',
                ]"
                :aria-selected="tab.isActive"
                :aria-controls="tab.id"
                :id="tab.selectorId"
                :tabindex="tab.isActive ? 0 : -1"
                @click="selectTab(tab.selectorId)"
                @keydown="handleKeyEvent($event)"
                :ref="tab.selectorId"
            >
                {{ tab.name }}
            </button>
        </div>
        <div class="cw-tabs-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-tabs',
    props: {
        setSelected: { type: Number, required: false, default: 0 },
    },
    data() {
        return {
            tabs: [],
        };
    },
    created() {
        this.tabs = this.$children.sort((a, b) => {
            return a.index - b.index;
        });
    },
    methods: {
        selectTab(selectorId) {
            let view = this;
            this.tabs.forEach((tab) => {
                tab.isActive = false;
                if (tab.selectorId === selectorId) {
                    tab.isActive = true;
                    view.$refs[selectorId][0].focus();
                    view.$emit('selectTab', tab);
                }
            });
        },
        selectTabByIndex(selectedIndex) {
            let view = this;
            this.tabs.forEach((tab) => {
                tab.isActive = false;
                if (tab.index === selectedIndex) {
                    tab.isActive = true;
                    view.$refs[tab.selectorId][0].focus();
                    view.$emit('selectTab', tab);
                }
            });
        },
        handleKeyEvent(e) {
            let tablist = e.target.parentElement;
            switch (e.keyCode) {
                case 37: // left
                case 38: // up
                    if (tablist.firstChild.id !== e.target.id) {
                        this.selectTab(e.target.previousElementSibling.id);
                    } else {
                        this.selectTab(tablist.lastChild.id);
                    }
                    break;
                case 39: // right
                case 40: // down
                    if (tablist.lastChild.id !== e.target.id) {
                        this.selectTab(e.target.nextElementSibling.id);
                    } else {
                        this.selectTab(tablist.firstChild.id);
                    }
                    break;
                case 36: //pos1
                    this.selectTab(tablist.firstChild.id);
                    break;
                case 35: //end
                    this.selectTab(tablist.lastChild.id);
                    break;
            }
        },
        getButtonById(id) {
            return this.$refs[id][0];
        },
        getFirstTabButton() {
            let selectorId = this.tabs[0].selectorId;
            return this.$refs[selectorId][0];
        },
        getTabButtonByName(name) {
            let selectorId = null;
            this.tabs.forEach((tab) => {
                if (tab.name === name) {
                    selectorId = tab.selectorId;
                }
            });
            if (selectorId) {
                return this.$refs[selectorId][0];
            }
            return null;
        },
        getTabButtonByAlias(alias) {
            let selectorId = null;
            this.tabs.forEach((tab) => {
                if (tab.alias === alias) {
                    selectorId = tab.selectorId;
                }
            });
            if (selectorId) {
                return this.$refs[selectorId][0];
            }
            return null;
        },
    },
    watch: {
        setSelected(tab) {
            this.selectTabByIndex(tab);
        },
    },
};
</script>