<template>
    <div class="cw-tools-element-adder">
        <courseware-tabs class="cw-tools-element-adder-tabs">
            <courseware-tab :name="$gettext('Blöcke')" :selected="showBlockadder" :index="0" :style="{ maxHeight: maxHeight + 'px' }">
                <form @submit.prevent="loadSearch">
                    <div class="input-group files-search search cw-block-search">
                        <input
                            ref="searchBox"
                            type="text"
                            v-model="searchInput"
                            @click.stop
                            :label="$gettext('Geben Sie einen Suchbegriff mit mindestens 3 Zeichen ein.')"
                        />
                        <span class="input-group-append" @click.stop>
                            <button v-if="searchInput"
                                type="button"
                                class="button reset-search"
                                id="reset-search"
                                :title="$gettext('Suche zurücksetzen')"
                                @click="resetSearch"
                            >
                                <studip-icon shape="decline" :size="20"></studip-icon>
                            </button>
                            <button 
                                type="submit"
                                class="button"
                                id="search-btn"
                                :title="$gettext('Suche starten')"
                                @click="loadSearch"
                            >
                                <studip-icon shape="search" :size="20"></studip-icon>
                            </button>
                        </span>
                    </div>
                </form>

                <div class="filterpanel">
                    <span class="sr-only">{{ $gettext('Kategorien Filter') }}</span>
                    <button
                        v-for="category in blockCategories"
                        :key="category.type"
                        class="button"
                        :class="{'button-active': category.type ===  currentFilterCategory }"
                        :aria-pressed="category.type ===  currentFilterCategory ? 'true' : 'false'"
                        @click="selectCategory(category.type)"
                    >
                        {{ category.title }}
                    </button>
                </div>

                <div v-if="filteredBlockTypes.length > 0" class="cw-blockadder-item-list">
                    <courseware-blockadder-item
                        v-for="(block, index) in filteredBlockTypes"
                        :key="index"
                        :title="block.title"
                        :type="block.type"
                        :description="block.description"
                        @blockAdded="$emit('blockAdded')"
                    />
                </div>
                <courseware-companion-box
                    v-else
                    :msgCompanion="$gettext('Es wurden keine passenden Blöcke gefunden.')"
                    mood="pointing"
                />
            </courseware-tab>
            <courseware-tab :name="$gettext('Abschnitte')" :selected="showContaineradder" :index="1" :style="{ maxHeight: maxHeight + 'px' }">
                <div class="cw-container-style-selector" role="group" aria-labelledby="cw-containeradder-style">
                    <p class="sr-only" id="cw-containeradder-style">{{ $gettext('Abschnitt-Stil') }}</p>
                    <template
                        v-for="style in containerStyles"
                    >
                        <input
                            :key="style.key  + '-input'"
                            type="radio"
                            name="container-style"
                            :id="'style-' + style.colspan"
                            v-model="selectedContainerStyle"
                            :value="style.colspan"
                        />
                        <label
                            :key="style.key + '-label'"
                            :for="'style-' + style.colspan"
                            :class="[selectedContainerStyle === style.colspan ? 'cw-container-style-selector-active' : '', style.colspan]"
                        >
                            {{ style.title }}
                        </label>
                        
                    </template>
                </div>
                <courseware-container-adder-item
                    v-for="container in containerTypes"
                    :key="container.type"
                    :title="container.title"
                    :type="container.type"
                    :colspan="selectedContainerStyle"
                    :description="container.description"
                    :firstSection="$gettext('erstes Element')"
                    :secondSection="$gettext('zweites Element')"
                ></courseware-container-adder-item>
            </courseware-tab>
        </courseware-tabs>
    </div>
</template>

<script>
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareBlockadderItem from './CoursewareBlockadderItem.vue';
import CoursewareContainerAdderItem from './CoursewareContainerAdderItem.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'cw-tools-blockadder',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareBlockadderItem,
        CoursewareContainerAdderItem,
        CoursewareCompanionBox,
    },
    props: {
        stickyRibbon: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            showBlockadder: true,
            showContaineradder: false,
            searchInput: '',
            currentFilterCategory: '',
            filteredBlockTypes: [],
            categorizedBlocks: [],
            selectedContainerStyle: 'full'
        };
    },
    computed: {
        ...mapGetters({
            adderStorage: 'blockAdder',
            containerAdder: 'containerAdder',
            unorderedBlockTypes: 'blockTypes',
            containerTypes: 'containerTypes',
            favoriteBlockTypes: 'favoriteBlockTypes',
            showToolbar: 'showToolbar',
        }),
        blockTypes() {
            let blockTypes = JSON.parse(JSON.stringify(this.unorderedBlockTypes));
            blockTypes.sort((a, b) => {
                return a.title > b.title ? 1 : b.title > a.title ? -1 : 0;
            });
            return blockTypes;
        },
        containerStyles() {
            return [
                { key: 0, title: this.$gettext('Volle Breite'), colspan: 'full'},
                { key: 1, title: this.$gettext('Halbe Breite'), colspan: 'half' },
                { key: 2, title: this.$gettext('Halbe Breite (zentriert)'), colspan: 'half-center' },
            ];
        },
        blockCategories() {
            return [
                { title: this.$gettext('Favoriten'), type: 'favorite' },
                { title: this.$gettext('Texte'), type: 'text' },
                { title: this.$gettext('Multimedia'), type: 'multimedia' },
                { title: this.$gettext('Interaktion'), type: 'interaction' },
                { title: this.$gettext('Gestaltung'), type: 'layout' },
                { title: this.$gettext('Externe Inhalte'), type: 'external' },
                { title: this.$gettext('Biografie'), type: 'biography' },
            ];
        },
        maxHeight() {
            if (this.stickyRibbon) {
                return parseInt(window.innerHeight * 0.75) - 120;
            } else {
                return parseInt(Math.min(window.innerHeight * 0.75, window.innerHeight - 197)) - 120;
            }
        }
    },
    methods: {
        ...mapActions({
            removeFavoriteBlockType: 'removeFavoriteBlockType',
            addFavoriteBlockType: 'addFavoriteBlockType',
            coursewareContainerAdder: 'coursewareContainerAdder',
            companionWarning: 'companionWarning'
        }),
        displayContainerAdder() {
            this.showContaineradder = true;
            this.showBlockadder = false;
        },
        displayBlockAdder() {
            this.showContaineradder = false;
            this.showBlockadder = true;
            this.disableContainerAdder();
        },
        isBlockFav(block) {
            let isFav = false;
            this.favoriteBlockTypes.forEach((type) => {
                if (type.type === block.type) {
                    isFav = true;
                }
            });

            return isFav;
        },
        disableContainerAdder() {
            this.coursewareContainerAdder(false);
        },
        loadSearch() {
            let searchTerms = this.searchInput.trim();
            if (searchTerms.length < 3 && !this.currentFilterCategory) {
                this.companionWarning({info: this.$gettext('Leider ist Ihr Suchbegriff zu kurz. Der Suchbegriff muss mindestens 3 Zeichen lang sein.')});
                return;
            }
            this.filteredBlockTypes = this.blockTypes;

            // filter results by given filter first so only these results are searched if an additional search term is given
            if (this.currentFilterCategory) {
                this.filterBlockTypesByCategory();
                this.categorizedBlocks = this.filteredBlockTypes;
            } else {
                this.categorizedBlocks = this.blockTypes;
            }

            searchTerms = searchTerms.toLowerCase().split(' ');

            // sort out block types that don't contain all search words
            searchTerms.forEach(term => {
                this.filteredBlockTypes = this.filteredBlockTypes.filter(block => (
                    block.title.toLowerCase().includes(term)
                    || block.description.toLowerCase().includes(term)
                ));
            });

            // add block types to the search if a search term matches a tag even if they aren't in the given category
            if (this.searchInput.trim().length > 0) {
                this.filteredBlockTypes.push(...this.getBlockTypesByTags(searchTerms));
                // remove possible duplicates
                this.filteredBlockTypes = [...new Map(this.filteredBlockTypes.map(item => [item['title'], item])).values()];
            }
        },
        filterBlockTypesByCategory() {
            if (this.currentFilterCategory !== 'favorite') {
                this.filteredBlockTypes = this.filteredBlockTypes.filter(block => block.categories.includes(this.currentFilterCategory));
            } else {
                this.filteredBlockTypes = this.favoriteBlockTypes;
            }
            
        },
        getBlockTypesByTags(searchTags) {
            return this.categorizedBlocks.filter(block => {
                const lowercaseTags = block.tags.map(blockTag => blockTag.toLowerCase());
                for (const tag of searchTags) {
                    if (lowercaseTags.filter(blockTag => blockTag.includes(tag.toLowerCase())).length > 0) {
                        return true;
                    }
                }
                return false;
            });
        },
        selectCategory(type) {
            if (this.currentFilterCategory !== type) {
                this.currentFilterCategory = type;
            } else {
                this.resetCategory();
            }
        },
        resetCategory() {
            this.currentFilterCategory = '';
            if (!this.searchInput) {
                this.filteredBlockTypes = this.blockTypes;
            } else {
                this.loadSearch();
            }
        },
        resetSearch() {
            this.filteredBlockTypes = this.blockTypes;
            this.searchInput = '';
            this.currentFilterCategory = '';
        }
    },
    mounted() {
        if (this.containerAdder === true) {
            this.displayContainerAdder();
        }
        this.filteredBlockTypes = this.blockTypes;
        setTimeout(() => this.$refs.searchBox.focus(), 800);
    },
    watch: {
        adderStorage(newValue) {
            if (Object.keys(newValue).length !== 0) {
                this.displayBlockAdder();
            }
        },
        containerAdder(newValue) {
            if (newValue === true) {
                this.displayContainerAdder();
            }
        },
        showToolbar(newValue, oldValue) {
            if (oldValue === true && newValue === false) {
                this.disableContainerAdder();
            }
        },
        searchInput(newValue, oldValue) {
            if (newValue.length >= 3 && newValue !== oldValue) {
                this.loadSearch();
            }
            if (newValue.length < oldValue.length && newValue.length < 3) {
                if (!this.currentFilterCategory) {
                    this.filteredBlockTypes = this.blockTypes;
                } else {
                    this.loadSearch();
                }
            }
        },
        currentFilterCategory(newValue) {
            if (newValue) {
                this.loadSearch();
            }
        }
    }
};
</script>
