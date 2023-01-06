<template>
    <div class="cw-block cw-block-timeline">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @closeEdit="initCurrentData"
            @showEdit="setShowEdit"
            @storeEdit="storeBlock"
        >
            <template #content>
                <ol class="cw-timeline">
                    <li
                        v-for="(item, index) in sortedItems"
                        :key="index"
                        class="cw-timeline-item"
                    >
                        <div class="cw-timeline-item-icon" :class="'cw-timeline-item-icon-color-' + item.color">
                            <studip-icon :shape="item.icon" role="info" size="32" :class="item.color"/>
                        </div>
                        <div
                            class="cw-timeline-item-content"
                            :class="[index % 2 === 0 ? 'left' : 'right', 'cw-timeline-item-content-color-' + item.color]"
                        >
                            <h3 v-if="currentDateFormat !== 'none'">{{ getItemDate(item) }}</h3>
                            <h3 v-else>{{ item.title }}</h3>
                            <article>
                                <header v-if="currentDateFormat !== 'none'">{{ item.title }}</header>
                                <p>{{ item.description }}</p>
                            </article>
                        </div>
                    </li>
                </ol>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Zeitliche Sortierung</translate>
                        <select v-model="currentSort">
                            <option value="none"><translate>Keine</translate></option>
                            <option value="asc"><translate>Aufsteigend</translate></option>
                            <option value="desc"><translate>Absteigend</translate></option>
                        </select>
                    </label>

                    <label>
                        <translate>Zeitangabe</translate>
                        <select v-model="currentDateFormat">
                            <option value="year"><translate>Jahr</translate></option>
                            <option value="date"><translate>Datum</translate></option>
                            <option value="time"><translate>Zeit</translate></option>
                            <option value="datetime"><translate>Datum und Zeit</translate></option>
                            <option value="none"><translate>Keine</translate></option>
                        </select>
                    </label>
                </form>
                <button class="button add" @click="addItem"><translate>Ereignis hinzufügen</translate></button>
                <courseware-tabs
                    v-if="currentItems.length > 0"
                    :setSelected="setItemTab"
                    @selectTab="setItemTab = (parseInt($event.name.replace($gettext('Ereignis') +  ' ', '')) - 1)"
                >
                    <courseware-tab
                        v-for="(item, index) in currentItems"
                        :key="index"
                        :index="index"
                        :name="$gettext('Ereignis') +  ' ' + (index + 1).toString()"
                        :selected="index === 0"
                        canBeEmpty
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Titel</translate>
                                <input type="text" v-model="item.title" />
                            </label>
                            <label>
                                <translate>Beschreibung</translate>
                                <textarea v-model="item.description" />
                            </label>
                            <label>
                                <translate>Farbe</translate>
                                <studip-select
                                    :options="colors"
                                    label="icon"
                                    :clearable="false"
                                    :reduce="option => option.class"
                                    v-model="item.color"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        <translate>Es steht keine Auswahl zur Verfügung.</translate>
                                    </template>
                                    <template #selected-option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label>
                                <translate>Icon</translate>
                                <studip-select :options="icons" :clearable="false" v-model="item.icon">
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        <translate>Es steht keine Auswahl zur Verfügung.</translate>
                                    </template>
                                    <template #selected-option="option">
                                        <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                                    </template>
                                    <template #option="option">
                                        <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label>
                                <translate>Datum</translate>
                                <input type="date" v-model="item.date" required/>
                            </label>
                            <label>
                                <translate>Zeit</translate>
                                <input type="time" v-model="item.time" />
                            </label>
                            <label v-if="currentItems.length > 1">
                                <button class="button trash" @click="removeItem(index)">
                                    <translate>Ereignis entfernen</translate>
                                </button>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info><translate>Informationen zum Zeitstrahl-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import contentIcons from './content-icons.js';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-timeline-block',
    mixins: [blockMixin, colorMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareTabs,
        CoursewareTab,
        StudipIcon,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            showEdit: false,
            setItemTab: 0,
            currentItems: [],
            currentSort: '',
            currentDateFormat: '',
        }
    },
    computed: {
        items() {
            return this.block?.attributes?.payload?.items;
        },
        sort() {
            return this.block?.attributes?.payload?.sort;
        },
        dateformat() {
            return this.block?.attributes?.payload?.dateformat;
        },
        icons() {
            return contentIcons;
        },
        colors() {
            return this.mixinColors.filter(color => color.class !== 'white' && color.class !== 'studip-lightblue');
        },
        sortedItems() {
            if (this.currentSort === 'none') {
                return this.currentItems;
            }
            let view = this;
            let items = _.cloneDeep(this.currentItems);
            return items.sort((a, b) => {
                let dateA = null;
                let dateB = null;

                if (a.time) {
                    dateA = new Date(a.date + 'T' + a.time);
                } else {
                    dateA = new Date(a.date);
                }
                if (b.time) {
                    dateB = new Date(b.date + 'T' + b.time);
                } else {
                    dateB = new Date(b.date);
                }
                if (view.currentSort === 'asc') {
                    return dateA > dateB ? 1 : dateA < dateB ? -1 : 0;
                }
                if (view.currentSort === 'desc') {
                    return dateA < dateB ? 1 : dateA > dateB ? -1 : 0;
                }
            });
        }
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        setShowEdit(state) {
            this.showEdit = state;
        },
        initCurrentData() {
            this.currentItems = this.items;
            this.currentSort = this.sort;
            this.currentDateFormat = this.dateformat;
            this.setItemTab = 0;
        },
        getItemDate(item) {
            switch (this.currentDateFormat) {
                case 'year':
                    if (item.date) {
                        return (new Date(item.date)).getFullYear();
                    }
                    break;
                case 'date':
                    if (item.date) {
                        return this.getReadableDate(item.date);
                    }
                    break;
                case 'time':
                     if (item.time) {
                        return item.time;
                     }
                     break;
                case 'datetime':
                    if (item.date && item.time) {
                        return this.getReadableDate(item.date) + ' ' + item.time;
                    }
                    if (!item.date && item.time) {
                        return '--.--.---- ' + item.time;
                    }
                    if (item.date && !item.time) {
                        return this.getReadableDate(item.date) + ' --:--';
                    }
                    return '--.--.---- --:--'
            }

            return '';
        },
        addItem() {
            this.currentItems.push({
                title: '',
                description: '',
                date: '',
                time: '',
                color: 'studip-blue',
                icon: 'courseware'
            });
            this.$nextTick(() => { this.setItemTab = this.currentItems.length - 1; });
        },
        removeItem(itemIndex){
            this.currentItems = this.currentItems.filter((val, index) => {
                return !(index === itemIndex);
            });
            this.$nextTick(() => { this.setItemTab = 0; });
        },
        storeBlock() {
            let attributes = {
                payload: {
                    dateformat: this.currentDateFormat,
                    sort: this.currentSort,
                    items: this.currentItems
                }
            };

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        items() {
            if (!this.showEdit) {
                this.initCurrentData();
            }
        },
    },
};
</script>
