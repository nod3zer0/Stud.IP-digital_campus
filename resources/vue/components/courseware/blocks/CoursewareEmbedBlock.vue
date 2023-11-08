<template>
    <div class="cw-block cw-block-embed" ref="block">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <template v-if="oembedData">
                    <div
                        v-if="oembedData.type === 'rich' || oembedData.type === 'video'"
                        v-html="oembedData.html"
                        class="cw-block-embed-iframe-wrapper"
                        :style="{ height: contentHeight + 'px' }"
                    ></div>

                    <div v-if="oembedData.type === 'photo'" :style="{ height: contentHeight + 'px' }">
                        <img :src="oembedData.url" />
                    </div>

                    <div v-if="oembedData.type === 'link' && oembedData.provider_name === 'DeviantArt'">
                        <img :src="oembedData.fullsize_url" />
                    </div>
                    <div class="cw-block-embed-info">
                        <span class="cw-block-embed-title">{{ oembedData.title }}</span>
                        <span class="cw-block-embed-author-name">
                            {{ $gettext('erstellt von') }}
                            <a :href="oembedData.author_url" target="_blank">{{ oembedData.author_name }}</a></span
                        >
                        <span class="cw-block-embed-source">
                            {{ $gettext('veröffentlicht auf') }}
                            <a :href="oembedData.provider_url" target="_blank">{{ oembedData.provider_name }}</a></span
                        >
                    </div>
                </template>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        {{ $gettext('Quelle') }}
                        <select v-model="currentSource">
                            <option v-for="(value, key) in endPoints" :key="key" :value="key">{{ key }}</option>
                        </select>
                    </label>
                    <label>
                        {{ $gettext('URL') }}
                        <input type="text" v-model="currentUrl" />
                    </label>
                    <label v-if="currentSource === 'youtube'" class="col-1">
                        {{ $gettext('Startpunkt') }}
                        <input
                            type="time"
                            v-model="currentStartTime"
                            step="1"
                            min="00:00:00"
                            max="24:00:00"
                            @change="updateTime"
                        />
                    </label>
                    <label v-if="currentSource === 'youtube'" class="col-1">
                        {{ $gettext('Endpunkt') }}
                        <input
                            type="time"
                            v-model="currentEndTime"
                            step="1"
                            :min="currentStartTime"
                            max="24:00:00"
                            @change="updateTime"
                        />
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Embed-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-embed-block',
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
            currentSource: '',
            currentUrl: '',
            currentStartTime: '',
            currentEndTime: '',

            endPoints: {
                audiomack: 'https://www.audiomack.com/oembed',
                codepen: 'https://codepen.io/api/oembed',
                codesandbox: 'https://codesandbox.io/oembed',
                deviantart: 'https://backend.deviantart.com/oembed',
                ethfiddle: 'https://ethfiddle.com/services/oembed/',
                flickr: 'https://www.flickr.com/services/oembed/',
                giphy: 'https://giphy.com/services/oembed',
                kidoju: 'https://www.kidoju.com/api/oembed',
                learningapps: 'https://learningapps.org/oembed.php',
                sketchfab: 'https://sketchfab.com/oembed',
                slideshare: 'https://www.slideshare.net/api/oembed/2',
                soundcloud: 'https://soundcloud.com/oembed',
                speakerdeck: 'https://speakerdeck.com/oembed.json',
                sway: 'https://sway.com/api/v1.0/oembed',
                'sway.office': 'https://sway.office.com/api/v1.0/oembed',
                spotify: 'https://embed.spotify.com/oembed/',
                vimeo: 'https://vimeo.com/api/oembed.json',
                youtube: 'https://www.youtube.com/oembed',
            },
            oembedData: {},
            contentHeight: 300,
        };
    },
    computed: {
        url() {
            return this.block?.attributes?.payload?.url;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        startTime() {
            return this.block?.attributes?.payload?.starttime;
        },
        endTime() {
            return this.block?.attributes?.payload?.endtime;
        },
        oembed() {
            return this.block?.attributes?.payload?.oembed;
        },
    },
    mounted() {
        this.initCurrentData();
        window.addEventListener('resize', this.calcContentHeight);
    },

    created() {
        STUDIP.eventBus.on('courseware:update-tab', (data) => {
            this.recalculateContentHeight(data);
        });
        STUDIP.eventBus.on('courseware:update-collapsible', (data) => {
            this.recalculateContentHeight(data);
        });
    },
    destroyed() {
        window.removeEventListener('resize', this.calcContentHeight);
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSource = this.source;
            this.currentUrl = this.url;
            this.currentStartTime = this.startTime;
            this.currentEndTime = this.endTime;
            this.oembedData = this.oembed;
            if (this.oembedData) {
                this.updateTime();
                this.calcContentHeight();
            }
        },
        recalculateContentHeight(data) {
            if (this.$parent._uid === data.uid) {
                if (this.oembedData) {
                    this.calcContentHeight();
                }
            }
        },
        addTimeData(data) {
            if (this.currentSource === 'youtube') {
                if (this.currentStartTime !== '') {
                    let start = this.currentStartTime.split(':');
                    let s = parseInt(start[0], 10) * 3600 + parseInt(start[1], 10) * 60 + parseInt(start[2], 10);
                    let query = '?feature=oembed&start=' + s;
                    if (this.currentEndTime !== '') {
                        let end = this.currentEndTime.split(':');
                        let e = parseInt(end[0], 10) * 3600 + parseInt(end[1], 10) * 60 + parseInt(end[2], 10);
                        query = query + '&end=' + e;
                    }
                    data.html = data.html.replace('?feature=oembed', query);
                }
            }
            return data;
        },
        updateTime() {
            this.oembedData = this.addTimeData(this.oembedData);
        },
        validateCurrentSource() {
            var validSource = false;
            let view = this;
            for (const key of Object.keys(this.endPoints)) {
                if (view.currentUrl.includes(key)) {
                    view.currentSource = key;
                    validSource = true;
                    break;
                }
            }

            return validSource;
        },
        calcContentHeight() {
            if (this.oembedData.height && this.oembedData.width) {
                this.contentHeight =
                    ((this.$refs.block.offsetWidth - 4) / this.oembedData.width) * this.oembedData.height;
            }
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.url = this.currentUrl;
            attributes.payload.source = this.currentSource;
            attributes.payload.starttime = this.currentStartTime;
            attributes.payload.endtime = this.currentEndTime;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/embed.scss';
</style>
