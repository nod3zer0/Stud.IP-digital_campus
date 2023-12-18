<template>
    <div class="cw-toolbar-blocks">
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
                    <button
                        v-if="searchInput"
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
                :class="{ 'button-active': category.type === currentFilterCategory }"
                :aria-pressed="category.type === currentFilterCategory ? 'true' : 'false'"
                @click="selectCategory(category.type)"
            >
                {{ category.title }}
            </button>
        </div>

        <div v-if="filteredBlockTypes.length > 0" class="cw-blockadder-item-list">
            <draggable
                v-if="filteredBlockTypes.length > 0"
                class="cw-blockadder-item-list"
                tag="div"
                role="listbox"
                v-model="filteredBlockTypes"
                handle=".cw-sortable-handle-blockadder"
                :group="{ name: 'blocks', pull: 'clone', put: 'false' }"
                :clone="cloneBlock"
                :sort="false"
                :emptyInsertThreshold="20"
                @start="dragBlockStart($event)"
                @end="dropNewBlock($event)"
                ref="sortables"
                sectionId="0"
            >
                <courseware-blockadder-item
                    v-for="(block, index) in filteredBlockTypes"
                    :key="index"
                    :title="block.title"
                    :type="block.type"
                    :data-blocktype="block.type"
                    :description="block.description"
                    @blockAdded="$emit('blockAdded')"
                />
            </draggable>
        </div>
        <courseware-companion-box
            v-else
            :msgCompanion="$gettext('Es wurden keine passenden Blöcke gefunden.')"
            mood="pointing"
        />
    </div>
</template>

<script>
import CoursewareBlockadderItem from './CoursewareBlockadderItem.vue';
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import containerMixin from '@/vue/mixins/courseware/container.js';
import draggable from 'vuedraggable';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-toolbar-blocks',
    mixins: [containerMixin],
    components: {
        CoursewareBlockadderItem,
        CoursewareCompanionBox,
        draggable,
    },
    data() {
        return {
            searchInput: '',
            currentFilterCategory: '',
            filteredBlockTypes: [],
            categorizedBlocks: [],

            isDragging: false,
        };
    },
    computed: {
        ...mapGetters({
            unorderedBlockTypes: 'blockTypes',
            favoriteBlockTypes: 'favoriteBlockTypes',
        }),
        blockTypes() {
            let blockTypes = JSON.parse(JSON.stringify(this.unorderedBlockTypes));
            blockTypes.sort((a, b) => {
                return a.title > b.title ? 1 : b.title > a.title ? -1 : 0;
            });
            return blockTypes;
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
    },
    methods: {
        ...mapActions({
            companionWarning: 'companionWarning',
            createBlock: 'createBlockInContainer',
            setAdderStorage: 'coursewareBlockAdder',
        }),
        loadSearch() {
            let searchTerms = this.searchInput.trim();
            if (searchTerms.length < 3 && !this.currentFilterCategory) {
                this.companionWarning({
                    info: this.$gettext(
                        'Leider ist Ihr Suchbegriff zu kurz. Der Suchbegriff muss mindestens 3 Zeichen lang sein.'
                    ),
                });
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
            searchTerms.forEach((term) => {
                this.filteredBlockTypes = this.filteredBlockTypes.filter(
                    (block) =>
                        block.title.toLowerCase().includes(term) || block.description.toLowerCase().includes(term)
                );
            });

            // add block types to the search if a search term matches a tag even if they aren't in the given category
            if (this.searchInput.trim().length > 0) {
                this.filteredBlockTypes.push(...this.getBlockTypesByTags(searchTerms));
                // remove possible duplicates
                this.filteredBlockTypes = [
                    ...new Map(this.filteredBlockTypes.map((item) => [item['title'], item])).values(),
                ];
            }
        },
        filterBlockTypesByCategory() {
            if (this.currentFilterCategory !== 'favorite') {
                this.filteredBlockTypes = this.filteredBlockTypes.filter((block) =>
                    block.categories.includes(this.currentFilterCategory)
                );
            } else {
                this.filteredBlockTypes = this.favoriteBlockTypes;
            }
        },
        getBlockTypesByTags(searchTags) {
            return this.categorizedBlocks.filter((block) => {
                const lowercaseTags = block.tags.map((blockTag) => blockTag.toLowerCase());
                for (const tag of searchTags) {
                    if (lowercaseTags.filter((blockTag) => blockTag.includes(tag.toLowerCase())).length > 0) {
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
        },
        cloneBlock(original) {
            original.attributes = {
                'block-type': original.type,
                payload: {
                    file_id: '',
                    folder_id: '',
                    background_image_id: '',
                    files: [],
                    url: 'studip.de',
                    sort: 'none',
                    tool_id: '',
                    cards: [],
                    text: ' ',
                    shapes: {},
                    type: 'Persönliches Ziel',
                    content: [{ color: 'blue', label: '', value: '0' }],
                },
            };
            original.relationships = {
                'user-data-field': {
                    data: { id: null },
                },
            };
            return original;
        },
        dragBlockStart(e) {
            this.isDragging = true;
        },
        async dropNewBlock(e) {
            const target = e.to.__vue__.$attrs;
            const blockType = e.item.__vue__.$attrs['data-blocktype'];

            // only execute if dropped in destined list
            if (!target.containerId) {
                return;
            }
            // set chosen container and section and pass block data
            this.setAdderStorage({
                container: this.containerById({ id: target.containerId }),
                section: target.sectionId,
                type: blockType,
                position: e.newIndex,
            });

            await this.addNewBlock();
            this.resetAdderStorage();
            this.isDragging = false;
        },
    },
    mounted() {
        this.filteredBlockTypes = this.blockTypes;
        setTimeout(() => this.$refs.searchBox.focus(), 800);
    },
    watch: {
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
        },
    },
};
</script>
