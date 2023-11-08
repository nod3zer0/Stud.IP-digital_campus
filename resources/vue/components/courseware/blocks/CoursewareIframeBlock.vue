<template>
    <div class="cw-block cw-block-iframe">
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
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <iframe
                    v-show="currentUrl.includes('http')"
                    :src="activeUrl"
                    :height="currentHeight"
                    width="100%"
                    allowfullscreen
                    sandbox="allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts"
                />
                <div v-if="currentCcInfo" class="cw-block-iframe-cc-data">
                    <span class="cw-block-iframe-cc" :class="['cw-block-iframe-cc-' + currentCcInfo]"></span>
                    <div class="cw-block-iframe-cc-infos">
                        <p v-if="currentCcWork">{{ $gettext('Werk') }}: {{ currentCcWork }}</p>
                        <p v-if="currentCcAuthor">{{ $gettext('Autor') }}: {{ currentCcAuthor }}</p>
                        <p v-if="currentCcBase">{{ $gettext('Lizenz der Plattform') }}: {{ currentCcBase }}</p>
                    </div>
                </div>
                <div v-show="!currentUrl.includes('http')" :style="{ height: currentHeight + 'px' }"></div>
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab :index="0" :name="$gettext('Grunddaten')" :selected="true">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Titel') }}
                                <input type="text" v-model="currentTitle" />
                            </label>
                            <label>
                                {{ $gettext('URL') }}
                                <input type="text" v-model="currentUrl" @change="setProtocol" />
                            </label>
                            <label>
                                {{ $gettext('Höhe') }}
                                <input type="number" v-model="currentHeight" min="0" />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="1" :name="$gettext('Nutzerspezifische ID')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Nutzerspezifische ID übergeben') }}
                                <select v-model="currentSubmitUserId">
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                </select>
                            </label>

                            <label v-if="currentSubmitUserId === 'true'">
                                {{ $gettext('Name des Übergabeparameters') }}
                                <input type="text" v-model="currentSubmitParam" />
                            </label>
                            <label v-if="currentSubmitUserId === 'true'">
                                {{ $gettext('Zufallszeichen für Verschlüsselung (Salt)') }}
                                <input type="text" v-model="currentSalt" />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="2" :name="$gettext('Creative Commons')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Creative Commons Lizenz') }}
                                <select v-model="currentCcInfo">
                                    <option value="false">{{ $gettext('Keine') }}</option>
                                    <option value="by">(by) {{ $gettext('Namensnennung') }}</option>
                                    <option value="by-sa">
                                        (by-sa) {{ $gettext('Namensnennung & Weitergabe unter gleichen Bedingungen') }}
                                    </option>
                                    <option value="by-nc">
                                        (by-nc) {{ $gettext('Namensnennung & Nicht kommerziell') }}
                                    </option>
                                    <option value="by-nd">
                                        (by-nd) {{ $gettext('Namensnennung & Keine Bearbeitung') }}
                                    </option>
                                    <option value="by-nc-nd">
                                        (by-nc-nd)
                                        {{ $gettext('Namensnennung & Nicht kommerziell & Keine Bearbeitung') }}
                                    </option>
                                    <option value="by-nc-sa">
                                        (by-nc-sa)
                                        {{
                                            $gettext(
                                                'Namensnennung & Nicht kommerziell & Weitergabe unter gleichen Bedingungen'
                                            )
                                        }}
                                    </option>
                                </select>
                            </label>
                            <label v-if="currentCcInfo !== 'false'">
                                CC {{ $gettext('Werk') }}
                                <input type="text" v-model="currentCcWork" />
                            </label>
                            <label v-if="currentCcInfo !== 'false'">
                                CC {{ $gettext('Author') }}
                                <input type="text" v-model="currentCcAuthor" />
                            </label>
                            <label v-if="currentCcInfo !== 'false'">
                                CC {{ $gettext('Lizenz der Plattform') }}
                                <input type="text" v-model="currentCcBase" />
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum IFrame-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';
import md5 from 'md5';

export default {
    name: 'courseware-iframe-block',
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
            currentUrl: '',
            currentHeight: '',
            currentSubmitUserId: '',
            currentSubmitParam: '',
            currentSalt: '',
            currentCcInfo: '',
            currentCcWork: '',
            currentCcAuthor: '',
            currentCcBase: '',
        };
    },
    computed: {
        ...mapGetters(['userId']),
        url() {
            return this.block?.attributes?.payload?.url;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        submitUserId() {
            return this.block?.attributes?.payload?.submit_user_id;
        },
        submitParam() {
            return this.block?.attributes?.payload?.submit_param;
        },
        salt() {
            return this.block?.attributes?.payload?.salt;
        },
        ccInfo() {
            return this.block?.attributes?.payload?.cc_info;
        },
        ccWork() {
            return this.block?.attributes?.payload?.cc_work;
        },
        ccAuthor() {
            return this.block?.attributes?.payload?.cc_author;
        },
        ccBase() {
            return this.block?.attributes?.payload?.cc_base;
        },
        activeUrl() {
            if (this.currentUrl) {
                let url = new URL(this.currentUrl);
                if (this.currentSubmitUserId === 'true') {
                    url.searchParams.append(this.currentSubmitParam, md5(this.userId + this.currentSalt));
                }

                return url.href;
            }

            return '';
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentUrl = this.url;
            this.currentHeight = this.height;
            this.currentSubmitUserId = this.submitUserId;
            this.currentSubmitParam = this.submitParam;
            this.currentSalt = this.salt;
            this.currentCcInfo = this.ccInfo;
            this.currentCcWork = this.ccWork;
            this.currentCcAuthor = this.ccAuthor;
            this.currentCcBase = this.ccBase;
            this.setProtocol();
        },
        setProtocol() {
            if (location.protocol === 'https:') {
                if (!this.currentUrl.includes('https:')) {
                    if (this.currentUrl.includes('http:')) {
                        this.currentUrl = this.currentUrl.replace('http', 'https');
                    } else {
                        this.currentUrl = 'https://' + this.currentUrl;
                    }
                }
            } else if (location.protocol === 'http:') {
                if (!this.currentUrl.includes('http:') && !this.currentUrl.includes('https:')) {
                    this.currentUrl = 'http://' + this.currentUrl;
                }
            }
        },

        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.url = this.currentUrl;
            attributes.payload.height = this.currentHeight;
            attributes.payload.submit_user_id = this.currentSubmitUserId;
            attributes.payload.submit_param = this.currentSubmitParam;
            attributes.payload.salt = this.currentSalt;
            attributes.payload.cc_info = this.currentCcInfo;
            attributes.payload.cc_work = this.currentCcWork;
            attributes.payload.cc_author = this.currentCcAuthor;
            attributes.payload.cc_base = this.currentCcBase;

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
@import '../../../../assets/stylesheets/scss/courseware/blocks/iframe.scss';
</style>
