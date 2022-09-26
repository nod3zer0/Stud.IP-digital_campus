<template>
    <div class="cw-block cw-block-biography-career">
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
                        <div class="cw-timeline-item-icon cw-timeline-item-icon-color-studip-blue">
                            <studip-icon v-if="item.type === 'school'" shape="doctoral-cap" role="clickable" size="32"/>
                            <studip-icon v-if="item.type === 'experience'" shape="tools" role="clickable" size="32"/>
                        </div>
                        <div
                            class="cw-timeline-item-content cw-timeline-item-content-color-studip-blue"
                            :class="[index % 2 === 0 ? 'left' : 'right',]"
                        >
                            <h3>{{ item.date ? getReadableDate(item.date) : ''}}{{ item.enddate ? ' - ' + getReadableDate(item.enddate) : '' }}</h3>
                            <article>
                                <header>{{ getItemTypeName(item.type) }}</header>
                                <div v-if="item.type === 'school'">
                                    <p><translate>Bezeichnung der Qualifikation</translate>: {{ item.qualification }}</p>
                                    <p><translate>Hauptfächer / Schwerpunkt</translate>: {{ item.focus }}</p>
                                    <p><translate>berufliche Fähigkeiten</translate>: {{ item.skills }}</p>
                                </div>
                                <div v-if="item.type === 'experience'">
                                    <p><translate>Name des Arbeitgebers</translate>: {{ item.employer }}</p>
                                    <p><translate>Beruf / Funktion</translate>: {{ item.job }}</p>
                                </div>
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
                                <translate>Startdatum</translate>
                                <input type="date" v-model="item.date" required />
                            </label>
                            <label>
                                <translate>Enddatum</translate>
                                <input type="date" v-model="item.enddate" />
                            </label>
                            <label>
                                <translate>Art</translate>
                                <select v-model="item.type">
                                    <option value="school"><translate>Schul- und Berufsbildung</translate></option>
                                    <option value="experience"><translate>Berufserfahrung</translate></option>
                                </select>
                            </label>
                            <div v-show="item.type === 'school'">
                                <label>
                                    <translate>Bezeichnung der Qualifikation</translate>
                                    <input type="text" v-model="item.qualification" />
                                </label>
                                <label>
                                    <translate>Hauptfächer / Schwerpunkt</translate>
                                    <input type="text" v-model="item.focus" />
                                </label>
                                <label>
                                    <translate>berufliche Fähigkeiten</translate>
                                    <input type="text" v-model="item.skills" />
                                </label>
                            </div>
                            <div v-show="item.type === 'experience'">
                                <label>
                                    <translate>Name des Arbeitgebers</translate>
                                    <input type="text" v-model="item.employer" />
                                </label>
                                <label>
                                    <translate>Beruf / Funktion</translate>
                                    <input type="text" v-model="item.job" />
                                </label>
                            </div>
                            <label>
                                <translate>Beschreibung</translate>
                                <textarea v-model="item.description" />
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
            <template #info><translate>Informationen zum Karriere-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-biography-career-block',
    mixins: [blockMixin],
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
        }
    },
    computed: {
        items() {
            return this.block?.attributes?.payload?.items;
        },
        sort() {
            return this.block?.attributes?.payload?.sort;
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
            this.setItemTab = 0;
        },
        addItem() {
            this.currentItems.push({
                title: '',
                description: '',
                date: '',
                enddate: '',
                type: 'school',
                qualification: '',
                focus: '',
                skills: '',
                employer: '',
                job: '',
            });
            this.$nextTick(() => { this.setItemTab = this.currentItems.length - 1; });
        },
        removeItem(itemIndex){
            this.currentItems = this.currentItems.filter((val, index) => {
                return !(index === itemIndex);
            });
            this.$nextTick(() => { this.setItemTab = 0; });
        },
        getItemTypeName(type) {
            switch (type) {
                case 'school':
                    return this.$gettext('Schul- und Berufsbildung');
                case 'experience':
                    return this.$gettext('Berufserfahrung');
            }

            return '';
        },
        storeBlock() {
            let attributes = {
                payload: {
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
    }
};
</script>
