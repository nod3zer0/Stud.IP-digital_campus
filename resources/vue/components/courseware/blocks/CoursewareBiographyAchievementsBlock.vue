<template>
    <div class="cw-block cw-block-biography cw-block-biography-achievements">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @showEdit="setShowEdit"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div class="cw-block-biography-content" >
                    <div class="cw-block-biography-type" :class="'cw-block-biography-achievements-type-' + currentData.type">
                        <h2>{{ typeName }}</h2>
                    </div>
                    <div class="cw-block-biography-details">
                        <h3>
                            {{ $gettext('Titel') }}: {{currentData.title}}
                        </h3>
                        <h4>
                            <span v-show="currentData.type !== 'membership'">{{ $gettext('Datum') }}:</span>
                            <span v-show="currentData.type === 'membership'">{{ $gettext('Startdatum') }}:</span>
                            {{ getReadableDate(currentData.date) }}
                        </h4>
                        <h4 v-show="hasEndDate">
                            {{ $gettext('Enddatum') }}: {{ getReadableDate(currentData.end_date)}}
                        </h4>
                        <h4 v-show="hasParticipation">
                            {{ $gettext('Beteiligung') }}: {{ currentData.role }}
                        </h4>
                        <div>
                            <h4>{{ $gettext('Beschreibung') }}:</h4>
                            <div class="formatted-content ck-content" v-html="currentData.description"></div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Type') }}
                        <select v-model="currentData.type">
                            <option value="certificate">{{ $gettext('Zertifikat') }}</option>
                            <option value="accreditation">{{ $gettext('Akkreditierung') }}</option>
                            <option value="award">{{ $gettext('Auszeichnung') }}</option>
                            <option value="book">{{ $gettext('Buch') }}</option>
                            <option value="publication">{{ $gettext('Veröffentlichung') }}</option>
                            <option value="membership">{{ $gettext('Mitgliedschaft') }}</option>
                        </select>
                    </label>
                    <label>
                        {{ $gettext('Titel') }}
                        <input type="text" v-model="currentData.title">
                    </label>
                    <label>
                        <template v-if="!hasEndDate">{{ $gettext('Datum') }}</template>
                        <template v-else>{{ $gettext('Startdatum') }}</template>
                        <input type="date" v-model="currentData.date" />
                    </label>
                    <label v-show="hasEndDate">
                        {{ $gettext('Enddatum') }}
                        <input type="date" v-model="currentData.end_date" />
                    </label>
                    <label v-show="hasParticipation">
                        {{ $gettext('Beteiligung') }}
                        <input type="text" v-model="currentData.role">
                    </label>
                    <div class="label-text">
                        {{ $gettext('Beschreibung') }}
                    </div>
                    <studip-wysiwyg v-model="currentData.description"></studip-wysiwyg>
                </form>
            </template>
            <template #info>{{ $gettext('Informationen zum Erfolge-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import StudipWysiwyg from '../../StudipWysiwyg.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-biography-achievements-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, { StudipWysiwyg }),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentData: {},
            showEdit: false,
        }
    },
    computed: {
        payload() {
            return this.block?.attributes?.payload;
        },
        typeName() {
            switch (this.currentData.type) {
                case 'certificate':
                    return this.$gettext('Zertifikat');
                case 'accreditation':
                    return this.$gettext('Akkreditierung');
                case 'award':
                    return this.$gettext('Auszeichnung');
                case 'book':
                    return this.$gettext('Buch');
                case 'publication':
                    return this.$gettext('Veröffentlichung');
                case 'membership':
                    return this.$gettext('Mitgliedschaft');
                default:
                    return '';
            }
        },
        achievementClass() {
            switch (this.currentData.type) {
                case 'certificate':
                case 'accreditation':
                case 'award':
                    return 'certificate';
                case 'book':
                case 'publication':
                    return 'publication';
                case 'membership':
                    return 'membership';
                default:
                    return '';
            }
        },
        hasParticipation() {
            return ['book', 'publication'].includes(this.currentData.type);
        },
        hasEndDate() {
            return this.currentData.type === 'membership';
        },
    },
    mounted() {
        this.initCurrentData();
    },
    updated() {
        this.updateCanvas();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            if (this.payload) {
                this.currentData = this.payload;
                this.currentData.date = this.getInputDate(this.currentData.date);
                this.currentData.end_date = this.getInputDate(this.currentData.end_date);
            }
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
        getInputDate(inputDate) {
            let date = new Date(inputDate);
            return date.getFullYear() + '-' +
                ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                ('0' + date.getDate()).slice(-2);
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {
                type: this.currentData.type,
                title: this.currentData.title,
                date: new Date(this.currentData.date).getTime(),
                end_date: new Date(this.currentData.end_date).getTime(),
                role: this.currentData.role,
                description: this.currentData.description
            };

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        payload() {
            if (!this.showEdit) {
                this.initCurrentData();
            }
        },
    }
};
</script>
<style scoped lang="scss">
    @import "../../../../assets/stylesheets/scss/courseware/blocks/biography.scss";
</style>