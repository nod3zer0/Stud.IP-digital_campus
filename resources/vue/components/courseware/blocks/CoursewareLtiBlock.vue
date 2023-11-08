<template>
    <div class="cw-block cw-block-lti">
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
                <iframe
                    v-if="toolId !== ''"
                    class="cw-block-lti-content"
                    :src="iframeUrl"
                    :height="currentHeight"
                    width="100%"
                    allowfullscreen
                    sandbox="allow-downloads allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts"
                />
                <div v-else class="cw-block-lti-content">
                    <span class="cw-block-lti-icon-tool">
                        {{ $gettext('Kein LTI-Tool konfiguriert') }}
                    </span>
                </div>
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
                                {{ $gettext('Auswahl des externen Tools') }}
                                <select v-model="currentToolId">
                                    <option v-for="tool in tools" :key="tool.id" :value="tool.id">
                                        {{ tool.name }}
                                    </option>
                                    <option value="0">{{ $gettext('Zugangsdaten selbst eingeben...') }}</option>
                                </select>
                            </label>
                            <label v-show="allowCustomUrl">
                                {{ $gettext('URL der Anwendung (optional)') }}
                                <studip-tooltip-icon
                                    :text="$gettext('Sie können direkt auf eine URL in der Anwendung verlinken.')"
                                />
                                <input type="text" v-model="currentLaunchUrl" :placeholder="currentTool?.launch_url" />
                            </label>

                            <div v-show="customToolSelected">
                                <label class="studiprequired">
                                    {{ $gettext('URL der Anwendung') }}
                                    <span
                                        class="asterisk"
                                        :title="$gettext('Dies ist ein Pflichtfeld')"
                                        aria-hidden="true"
                                        >*</span
                                    >
                                    <studip-tooltip-icon
                                        :text="
                                            $gettext(
                                                'Die Betreiber dieses Tools müssen Ihnen eine URL und Zugangsdaten (Consumer-Key und Consumer-Secret) mitteilen.'
                                            )
                                        "
                                    />
                                    <input type="text" v-model="currentLaunchUrl" required />
                                </label>
                                <label class="studiprequired">
                                    {{ $gettext('Consumer-Key des LTI-Tools') }}
                                    <span
                                        class="asterisk"
                                        :title="$gettext('Dies ist ein Pflichtfeld')"
                                        aria-hidden="true"
                                        >*</span
                                    >
                                    <input type="text" v-model="currentConsumerKey" required />
                                </label>
                                <label class="studiprequired">
                                    {{ $gettext('Consumer-Secret des LTI-Tools') }}
                                    <span
                                        class="asterisk"
                                        :title="$gettext('Dies ist ein Pflichtfeld')"
                                        aria-hidden="true"
                                        >*</span
                                    >
                                    <input type="text" v-model="currentConsumerSecret" required />
                                </label>
                                <label>
                                    {{ $gettext('OAuth Signatur Methode des LTI-Tools') }}
                                    <select v-model="currentOauthSignatureMethod">
                                        <option value="sha1">HMAC-SHA1</option>
                                        <option value="sha256">HMAC-SHA256</option>
                                    </select>
                                </label>
                                <label>
                                    <input type="checkbox" v-model="currentSendLisPerson" />
                                    {{ $gettext('Nutzerdaten an LTI-Tool senden') }}
                                    <studip-tooltip-icon
                                        :text="
                                            $gettext(
                                                'Nutzerdaten dürfen nur an das externe Tool gesendet werden, wenn es keine Datenschutzbedenken gibt. Mit Setzen des Hakens bestätigen Sie, dass die Übermittlung der Daten zulässig ist.'
                                            )
                                        "
                                    />
                                </label>
                            </div>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="1" :name="$gettext('Zusätzliche Einstellungen')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Höhe') }}
                                <input type="number" v-model="currentHeight" min="0" />
                            </label>
                            <label>
                                {{ $gettext('Zusätzliche LTI-Parameter') }}
                                <studip-tooltip-icon
                                    :text="$gettext('Ein Wert pro Zeile, Beispiel: Review:Chapter=1.2.56')"
                                />
                                <textarea v-model="currentCustomParameters" />
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum LTI-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-lti-block',
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
            currentHeight: '',
            currentToolId: '',
            currentLaunchUrl: '',
            currentConsumerKey: '',
            currentConsumerSecret: '',
            currentOauthSignatureMethod: '',
            currentSendLisPerson: false,
            currentCustomParameters: '',
        };
    },
    computed: {
        ...mapGetters({
            urlHelper: 'urlHelper',
            ltiTools: 'lti-tools/all',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        tools() {
            return this.ltiTools.map((tool) => ({
                id: tool.id,
                name: tool.attributes.name,
                launch_url: tool.attributes['launch-url'],
                allow_custom_url: tool.attributes['allow-custom-url'],
            }));
        },
        toolId() {
            return this.block?.attributes?.payload?.tool_id;
        },
        currentTool() {
            return this.tools.find((tool) => tool.id === this.currentToolId);
        },
        allowCustomUrl() {
            return this.currentTool?.allow_custom_url;
        },
        customToolSelected() {
            return this.currentToolId === '0';
        },
        launchUrl() {
            return this.block?.attributes?.payload?.launch_url;
        },
        consumerKey() {
            return this.block?.attributes?.payload?.consumer_key;
        },
        consumerSecret() {
            return this.block?.attributes?.payload?.consumer_secret;
        },
        oauthSignatureMethod() {
            return this.block?.attributes?.payload?.oauth_signature_method ?? 'sha1';
        },
        sendLisPerson() {
            return this.block?.attributes?.payload?.send_lis_person;
        },
        customParameters() {
            return this.block?.attributes?.payload?.custom_parameters;
        },
        iframeUrl() {
            return this.urlHelper.getURL('dispatch.php/courseware/lti/iframe/' + this.block.id);
        },
    },
    async mounted() {
        await this.loadLtiTools();
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadLtiTools: 'lti-tools/loadAll',
            companionWarning: 'companionWarning',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentHeight = this.height;
            this.currentToolId = this.toolId !== '' ? this.toolId : this.currentToolId; // keep preselected tool
            this.currentLaunchUrl = this.launchUrl;
            this.currentConsumerKey = this.consumerKey;
            this.currentConsumerSecret = this.consumerSecret;
            this.currentOauthSignatureMethod = this.oauthSignatureMethod;
            this.currentSendLisPerson = Boolean(this.sendLisPerson); // prevent undefined value
            this.currentCustomParameters = this.customParameters;
        },
        storeBlock() {
            // require url, key and secret if custom tool is selected
            if (this.currentToolId === '0') {
                if (!this.currentLaunchUrl) {
                    this.companionWarning({
                        info: this.$gettext('Bitte geben Sie eine URL der Anwendung an.'),
                    });
                    return false;
                }
                if (!this.currentConsumerKey) {
                    this.companionWarning({
                        info: this.$gettext('Bitte geben Sie den Consumer-Key des LTI-Tools an.'),
                    });
                    return false;
                }
                if (!this.currentConsumerSecret) {
                    this.companionWarning({
                        info: this.$gettext('Bitte geben Sie den Consumer-Secret des LTI-Tools an.'),
                    });
                    return false;
                }
            }

            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.height = this.currentHeight;
            attributes.payload.tool_id = this.currentToolId;
            attributes.payload.launch_url = this.currentLaunchUrl;
            if (this.currentToolId === '0') {
                attributes.payload.consumer_key = this.currentConsumerKey;
                attributes.payload.consumer_secret = this.currentConsumerSecret;
                attributes.payload.oauth_signature_method = this.currentOauthSignatureMethod;
                attributes.payload.send_lis_person = this.currentSendLisPerson;
            }
            attributes.payload.custom_parameters = this.currentCustomParameters;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        tools(value) {
            // Preselect tool
            if (this.currentToolId === '') {
                if (value.length > 0) {
                    // Preselect first tool
                    this.currentToolId = value[0].id;
                } else {
                    // Preselect custom tool
                    this.currentToolId = '0';
                }
            }
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/lti.scss';
</style>
