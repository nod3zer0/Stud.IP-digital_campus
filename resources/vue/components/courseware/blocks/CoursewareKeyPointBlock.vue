<template>
    <div class="cw-block cw-block-keypoint">
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
                <div class="cw-keypoint-content" :class="['cw-keypoint-' + currentColor]">
                    <studip-icon v-if="currentIcon" size="48" :shape="currentIcon" :role="currentRole" />
                    <p class="cw-keypoint-sentence">{{ currentText }}</p>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label class="col-4">
                        {{ $gettext('Merksatz') }}
                        <input
                            type="text"
                            name="cw-keypoint-content"
                            class="cw-keypoint-set-content"
                            v-model="currentText"
                            spellcheck="true"
                        />
                    </label>
                    <br />
                    <label class="col-2">
                        {{ $gettext('Farbe') }}
                        <studip-select
                            :options="colors"
                            label="icon"
                            :clearable="false"
                            :reduce="(option) => option.icon"
                            v-model="currentColor"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10" /></span>
                            </template>
                            <template #no-options>
                                {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                            </template>
                            <template #selected-option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                ><span>{{ name }}</span>
                            </template>
                            <template #option="{ name, hex }">
                                <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                                ><span>{{ name }}</span>
                            </template>
                        </studip-select>
                    </label>
                    <label class="col-2">
                        {{ $gettext('Icon') }}
                        <studip-select :options="icons" :clearable="false" v-model="currentIcon">
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10" /></span>
                            </template>
                            <template #no-options>
                                {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                            </template>
                            <template #selected-option="option">
                                <studip-icon :shape="option.label" />
                                <span class="vs__option-with-icon">{{ option.label }}</span>
                            </template>
                            <template #option="option">
                                <studip-icon :shape="option.label" />
                                <span class="vs__option-with-icon">{{ option.label }}</span>
                            </template>
                        </studip-select>
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Merksatz-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import contentIconsMixin from '@/vue/mixins/courseware/content-icons.js';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-key-point-block',
    mixins: [blockMixin, colorMixin, contentIconsMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
            currentColor: '',
            currentIcon: '',
        };
    },
    computed: {
        file() {
            return `icons/${this.color}/${this.icon}.svg`;
        },
        icons() {
            return this.contentIcons;
        },
        colors() {
            return this.mixinColors.filter(
                (color) => color.icon && color.class !== 'white' && color.class !== 'studip-lightblue'
            );
        },
        text() {
            return this.block?.attributes?.payload?.text;
        },
        color() {
            return this.block?.attributes?.payload?.color;
        },
        icon() {
            return this.block?.attributes?.payload?.icon;
        },
        currentRole() {
            switch (this.currentColor) {
                case 'black':
                    return 'info';

                case 'grey':
                    return 'inactive';

                case 'green':
                    return 'status-green';

                case 'red':
                    return 'status-red';

                case 'white':
                    return 'info_alt';

                case 'yellow':
                    return 'status-yellow';

                case 'blue':
                default:
                    return 'clickable';
            }
        },
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentText = this.text;
            this.currentColor = this.color;
            this.currentIcon = this.icon;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.text = this.currentText;
            attributes.payload.color = this.currentColor;
            attributes.payload.icon = this.currentIcon;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    mounted() {
        this.initCurrentData();
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/keypoint.scss';
</style>
