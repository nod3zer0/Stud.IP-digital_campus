<template>
    <div class="cw-block cw-block-biography cw-block-biography-personal-information">
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
                <div class="cw-block-biography-content" >
                    <div class="cw-block-biography-type cw-block-biography-personal-information-type">
                        <h2>{{currentData.name}}</h2>
                    </div>
                    <div class="cw-block-biography-details cw-block-biography-personal-information-details">
                        <span><translate>Geburtsort</translate>:</span>
                        <span>{{currentData.birthplace}}</span>

                        <span><translate>Geburtsdatum</translate>:</span>
                        <span>{{ getReadableDate(currentData.birthday) }}</span>

                        <span><translate>Geschlecht</translate>:</span>
                        <span>{{displayGenderText(currentData.gender)}}</span>

                        <span><translate>Familienstand</translate>:</span>
                        <span>{{ displayStatusText(currentData.status) }}</span>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Name</translate>
                        <input type="text" v-model="currentData.name">
                    </label>
                    <label>
                        <translate>Geburtsort</translate>
                        <input type="text" v-model="currentData.birthplace">
                    </label>
                    <label>
                        <translate>Geburtsdatum</translate>
                        <input type="date" v-model="currentData.birthday" />
                    </label>
                    <label>
                        <translate>Geschlecht</translate>
                            <select v-model="currentData.gender">
                            <option value="none">{{ displayGenderText('none') }}</option>
                            <option value="male">{{ displayGenderText('male') }}</option>
                            <option value="female">{{ displayGenderText('female') }}</option>
                            <option value="diverse">{{ displayGenderText('diverse') }}</option>
                        </select>
                    </label>
                    <label>
                        <translate>Familienstand</translate>
                            <select v-model="currentData.status">
                                <option value="none">{{ displayStatusText('none') }}</option>
                                <option value="single">{{ displayStatusText('single') }}</option>
                                <option value="married">{{ displayStatusText('married') }}</option>
                                <option value="widowed">{{ displayStatusText('widowed') }}</option>
                                <option value="divorced">{{ displayStatusText('divorced') }}</option>
                                <option value="registered-civil-partnership">{{ displayStatusText('registered-civil-partnership') }}</option>
                                <option value="annulled-civil-partnership">{{ displayStatusText('annulled-civil-partnership') }}</option>
                            </select>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Persönlichen-Informationen-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-biography-personal-information-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
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
            currentData: {},
        }
    },
    computed: {
        payload() {
            return this.block?.attributes?.payload;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        changeDate(date) {
            this.currentData.birthday = date;
        },
        initCurrentData() {
            if (this.payload) {
                this.currentData = this.payload;
                this.currentData.birthday = this.getInputDate(this.currentData.birthday);
            }
        },
        getInputDate(inputDate) {
            let date = new Date(inputDate);
            return date.getFullYear() + '-' +
                ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                ('0' + date.getDate()).slice(-2);
        },
        displayGenderText(gender) {
            switch (gender) {
                case 'none':
                    return this.$gettext('keine Angabe');
                case 'male':
                    return this.$gettext('männlich');
                case 'female':
                    return this.$gettext('Weiblich');
                case 'diverse':
                    return this.$gettext('divers');
                default:
                    return '';
            }
        },
        displayStatusText(status) {
            switch (status) {
                case 'none':
                    return this.$gettext('keine Angabe');
                case 'single':
                    return this.$gettext('ledig');
                case 'married':
                    return this.$gettext('verheiratet');
                case 'widowed':
                    return this.$gettext('verwitwet');
                case 'divorced':
                    return this.$gettext('geschieden');
                case 'registered-civil-partnership':
                    return this.$gettext('eingetragene Lebenspartnerschaft');
                case 'annulled-civil-partnership':
                    return this.$gettext('aufgehobene Lebenspartnerschaft');
                default:
                    return '';
            }
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {
                name: this.currentData.name,
                birthplace: this.currentData.birthplace,
                birthday: new Date(this.currentData.birthday).getTime(),
                gender: this.currentData.gender,
                status: this.currentData.status
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
