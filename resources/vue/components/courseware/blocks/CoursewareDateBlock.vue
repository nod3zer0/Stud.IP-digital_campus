<template>
    <div class="cw-block cw-block-date">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentStyle === 'countdown'" class="cw-date-countdown">
                    <div class="cw-date-countdown-digit" data-countdown="days">
                        <div class="cw-date-countdown-number">{{ countdownDays }}</div>
                        <div v-show="countdownDays === '01'" class="cw-date-countdown-label-sg">
                            {{ $gettext('Tag') }}
                        </div>
                        <div v-show="countdownDays !== '01'" class="cw-date-countdown-label-pl">
                            {{ $gettext('Tage') }}
                        </div>
                    </div>
                    <div class="cw-date-countdown-digit" data-countdown="hours">
                        <div class="cw-date-countdown-number">{{ countdownHours }}</div>
                        <div v-show="countdownHours === '01'" class="cw-date-countdown-label-sg">
                            {{ $gettext('Stunde') }}
                        </div>
                        <div v-show="countdownHours !== '01'" class="cw-date-countdown-label-pl">
                            {{ $gettext('Stunden') }}
                        </div>
                    </div>
                    <div class="cw-date-countdown-digit" data-countdown="minutes">
                        <div class="cw-date-countdown-number">{{ countdownMinutes }}</div>
                        <div v-show="countdownMinutes === '01'" class="cw-date-countdown-label-sg">
                            {{ $gettext('Minute') }}
                        </div>
                        <div v-show="countdownMinutes !== '01'" class="cw-date-countdown-label-pl">
                            {{ $gettext('Minuten') }}
                        </div>
                    </div>
                    <div class="cw-date-countdown-digit" data-countdown="seconds">
                        <div class="cw-date-countdown-number">{{ countdownSeconds }}</div>
                        <div v-show="countdownSeconds === '01'" class="cw-date-countdown-label-sg">
                            {{ $gettext('Sekunde') }}
                        </div>
                        <div v-show="countdownSeconds !== '01'" class="cw-date-countdown-label-pl">
                            {{ $gettext('Sekunden') }}
                        </div>
                    </div>
                </div>
                <div v-if="currentStyle === 'date'" class="cw-date-date">
                    <div class="cw-date-date-digits" data-date="date">
                        <div class="cw-date-date-number">{{ currentDeDate }}</div>
                    </div>
                    <div class="cw-date-date-space"></div>
                    <div class="cw-date-date-digits" data-date="time">
                        <div class="cw-date-date-number">{{ currentTime }}</div>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Datum') }}
                        <input type="date" v-model="currentDate" @change="computeTimestamp" />
                    </label>
                    <label>
                        {{ $gettext('Uhrzeit') }}
                        <input type="time" v-model="currentTime" @change="computeTimestamp" />
                    </label>
                    <label>
                        {{ $gettext('Layout') }}
                        <select v-model="currentStyle">
                            <option value="countdown">{{ $gettext('Countdown') }}</option>
                            <option value="date">{{ $gettext('Datum') }}</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>{{ $gettext('Informationen zum Termin-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions } from 'vuex';
export default {
    name: 'courseware-date-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentTimestamp: 0,
            currentStyle: '',
            currentTime: '',
            currentDate: '',
            currentDeDate: '',

            countdownDays: '00',
            countdownHours: '00',
            countdownMinutes: '00',
            countdownSeconds: '00',
        };
    },
    computed: {
        title() {
            return this.block?.attributes?.payload?.title;
        },
        timestamp() {
            return this.block?.attributes?.payload?.timestamp;
        },
        style() {
            return this.block?.attributes?.payload?.style;
        },
        date() {
            return new Date(this.currentTimestamp);
        },
    },
    mounted() {
        this.initCurrentData();
        if (this.currentStyle === 'countdown') {
            this.countdown();
        }
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            companionWarning: 'companionWarning',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentTimestamp = this.timestamp;
            this.currentDate =
                this.date.getFullYear() +
                '-' +
                ('0' + (this.date.getMonth() + 1)).slice(-2) +
                '-' +
                ('0' + this.date.getDate()).slice(-2);
            this.currentDeDate =
                ('0' + this.date.getDate()).slice(-2) +
                '.' +
                ('0' + (this.date.getMonth() + 1)).slice(-2) +
                '.' +
                this.date.getFullYear();
            this.currentTime = ('0' + this.date.getHours()).slice(-2) + ':' + ('0' + this.date.getMinutes()).slice(-2);
            this.currentStyle = this.style;
        },
        countdown() {
            let view = this;
            setInterval(function () {
                let now = new Date().getTime();
                let distance = view.currentTimestamp - now;
                if (distance < 0) {
                    return;
                }
                view.countdownDays = Math.floor(distance / (1000 * 60 * 60 * 24));
                view.countdownHours = ('0' + Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).slice(
                    -2
                );
                view.countdownMinutes = ('0' + Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).slice(-2);
                view.countdownSeconds = ('0' + Math.floor((distance % (1000 * 60)) / 1000)).slice(-2);
            }, 1000);
        },
        computeTimestamp() {
            this.currentTimestamp = new Date(this.currentDate + ' ' + this.currentTime).getTime();
        },
        storeBlock() {
            let cmpInfo = false;
            if (this.currentDate === '') {
                cmpInfo = this.$gettext('Bitte geben Sie ein Datum an.');
            } else if (this.currentTime === '') {
                cmpInfo = this.$gettext('Bitte geben Sie eine Uhrzeit an.');
            }
            if (cmpInfo) {
                this.companionWarning({
                    info: cmpInfo,
                });
                return false;
            } else {
                let attributes = {};
                attributes.payload = {};
                attributes.payload.timestamp = this.currentTimestamp;
                attributes.payload.style = this.currentStyle;

                this.updateBlock({
                    attributes: attributes,
                    blockId: this.block.id,
                    containerId: this.block.relationships.container.data.id,
                });
            }
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/date.scss';
</style>
