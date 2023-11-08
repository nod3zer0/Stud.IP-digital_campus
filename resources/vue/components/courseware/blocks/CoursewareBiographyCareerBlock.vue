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
                                    <p>{{ $gettext('Bezeichnung der Qualifikation') }}: {{ item.qualification }}</p>
                                    <p>{{ $gettext('Hauptfächer / Schwerpunkt') }}: {{ item.focus }}</p>
                                    <p>{{ $gettext('berufliche Fähigkeiten') }}: {{ item.skills }}</p>
                                </div>
                                <div v-if="item.type === 'experience'">
                                    <p>{{ $gettext('Name des Arbeitgebers') }}: {{ item.employer }}</p>
                                    <p>{{ $gettext('Beruf / Funktion') }}: {{ item.job }}</p>
                                </div>
                            </article>
                        </div>
                    </li>
                </ol>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Zeitliche Sortierung') }}
                        <select v-model="currentSort">
                            <option value="none">{{ $gettext('Keine') }}</option>
                            <option value="asc">{{ $gettext('Aufsteigend') }}</option>
                            <option value="desc">{{ $gettext('Absteigend') }}</option>
                        </select>
                    </label>
                </form>
                <button class="button add" @click="addItem">{{ $gettext('Ereignis hinzufügen') }}</button>
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
                            <label class="col-1">
                                {{ $gettext('Startdatum') }}
                                <input type="date" v-model="item.date" required />
                            </label>
                            <label class="col-1">
                                {{ $gettext('Enddatum') }}
                                <input type="date" v-model="item.enddate" />
                            </label>
                            <label>
                                {{ $gettext('Art') }}
                                <select v-model="item.type">
                                    <option value="school">{{ $gettext('Schul- und Berufsbildung') }}</option>
                                    <option value="experience">{{ $gettext('Berufserfahrung') }}</option>
                                </select>
                            </label>
                            <div v-show="item.type === 'school'">
                                <label>
                                    {{ $gettext('Bezeichnung der Qualifikation') }}
                                    <input type="text" v-model="item.qualification" />
                                </label>
                                <label>
                                    {{ $gettext('Hauptfächer / Schwerpunkt') }}
                                    <input type="text" v-model="item.focus" />
                                </label>
                                <label>
                                    {{ $gettext('berufliche Fähigkeiten') }}
                                    <input type="text" v-model="item.skills" />
                                </label>
                            </div>
                            <div v-show="item.type === 'experience'">
                                <label>
                                    {{ $gettext('Name des Arbeitgebers') }}
                                    <input type="text" v-model="item.employer" />
                                </label>
                                <label>
                                    {{ $gettext('Beruf / Funktion') }}
                                    <input type="text" v-model="item.job" />
                                </label>
                            </div>
                            <label>
                                {{ $gettext('Beschreibung') }}
                                <textarea v-model="item.description" />
                            </label>
                            <label v-if="currentItems.length > 1">
                                <button class="button trash" @click="removeItem(index)">
                                    {{ $gettext('Ereignis entfernen') }}
                                </button>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>{{ $gettext('Informationen zum Karriere-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-biography-career-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
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
<style scoped lang="scss">
    @import "../../../../assets/stylesheets/scss/courseware/blocks/timeline.scss";
    @import "../../../../assets/stylesheets/scss/courseware/blocks/biography.scss";
</style>