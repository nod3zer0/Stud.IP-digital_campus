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
                            <translate>Titel</translate>: {{currentData.title}}
                        </h3>
                        <h4>
                            <span v-show="currentData.type !== 'membership'"><translate>Datum</translate>:</span>
                            <span v-show="currentData.type === 'membership'"><translate>Startdatum</translate>:</span>
                            {{ getReadableDate(currentData.date) }}
                        </h4>
                        <h4 v-show="hasEndDate">
                            <translate>Enddatum</translate>: {{ getReadableDate(currentData.end_date)}}
                        </h4>
                        <h4 v-show="hasParticipation">
                            <translate>Beteiligung</translate>: <span v-html="currentData.role"></span>
                        </h4>
                        <div>
                            <h4><translate>Beschreibung</translate>:</h4>
                            <p v-html="currentData.description"></p>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Type</translate>
                        <select v-model="currentData.type">
                            <option value="certificate"><translate>Zertifikat</translate></option>
                            <option value="accreditation"><translate>Akkreditierung</translate></option>
                            <option value="award"><translate>Auszeichnung</translate></option>
                            <option value="book"><translate>Buch</translate></option>
                            <option value="publication"><translate>Veröffentlichung</translate></option>
                            <option value="membership"><translate>Mitgliedschaft</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Titel</translate>
                        <input type="text" v-model="currentData.title">
                    </label>
                    <label>
                        <span v-show="!hasEndDate"><translate>Datum</translate></span>
                        <span v-show="hasEndDate"><translate>Startdatum</translate></span>
                        <input type="date" v-model="currentData.date" />
                    </label>
                    <label v-show="hasEndDate">
                        <translate>Enddatum</translate>
                        <input type="date" v-model="currentData.end_date" />
                    </label>
                    <label v-show="hasParticipation">
                        <translate>Beteiligung</translate>
                        <input type="text" v-model="currentData.role">
                    </label>
                    <label>
                        <translate>Beschreibung</translate>
                        <studip-wysiwyg v-model="currentData.description"></studip-wysiwyg>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Erfolge-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import StudipIcon from '../StudipIcon.vue';
import StudipWysiwyg from '../StudipWysiwyg.vue';

export default {
    name: 'courseware-biography-achievements-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        StudipIcon,
        StudipWysiwyg,
    },
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
