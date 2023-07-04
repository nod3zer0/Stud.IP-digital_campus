<template>
    <div class="cw-block cw-block-headline">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @showEdit="initCurrentData"
            @storeEdit="storeText"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div
                    class="cw-block-headline-content"
                    :class="[
                        currentStyle,
                        currentHeight,
                        hasGradient ? currentGradient : ''
                    ]"
                    :style="headlineStyle"
                >
                    <div
                        class="cw-block-headline-iconbox"
                        :class="['border-' + currentIconColor, currentHeight]"
                    >
                        <div
                            class="icon-layer"
                            :class="['icon-' + currentIconColor + '-' + currentIcon, currentHeight]"
                        >
                        </div>
                    </div>
                    <div
                        class="cw-block-headline-textbox"
                        :class="['border-' + currentIconColor, currentHeight]"
                        :style="currentStyle === 'ribbon' ? headlineTextboxStyle.rgba : {}"
                    >
                        <div class="cw-block-headline-title" :style="currentStyle === 'vertical' ? headlineTextboxStyle.hex : {}">
                            <h1 :style="textStyle">{{ currentTitle }}</h1>
                        </div>
                        <div v-show="hasSubtitle && subtitleIsSet" class="cw-block-headline-subtitle" :style="currentStyle === 'vertical' ? headlineTextboxStyle.rgba : {}">
                            <h2 :style="textStyle">{{ currentSubtitle }}</h2>
                        </div>
                        <div v-show="hasSecondSubtitle" class="cw-block-headline-second-subtitle">
                            <h2 :style="textStyle">{{ currentSecondSubtitle }}</h2>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab
                        :index="0"
                        :name="$gettext('Layout')"
                        :selected="true"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Typ') }}
                                <select v-model="currentStyle">
                                    <option value="heavy">{{ $gettext('Große Schrift') }}</option>
                                    <option value="ribbon">{{ $gettext('Band') }}</option>
                                    <option value="vertical">{{ $gettext('Vertikal') }}</option>
                                    <option value="bigicon_top">{{ $gettext('Großes Icon oben') }}</option>
                                    <option value="bigicon_before">{{ $gettext('Großes Icon davor') }}</option>
                                    <option value="icon_top_lines">{{ $gettext('Icon oben mit Linien') }}</option>
                                    <option value="skew_text">{{ $gettext('Schief') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ $gettext('Höhe') }}
                                <select v-model="currentHeight">
                                    <option value="full">{{ $gettext('Voll') }}</option>
                                    <option value="half">{{ $gettext('Halb') }}</option>
                                    <option value="quarter">{{ $gettext('Viertel') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab
                        :index="1"
                        :name="$gettext('Inhalt')"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Haupttitel') }}
                                <input type="text" v-model="currentTitle" />
                            </label>
                            <label v-if="hasSubtitle">
                                {{ $gettext('Untertitel') }}
                                <input type="text" v-model="currentSubtitle" />
                            </label>
                            <label v-if="hasSecondSubtitle">
                                {{ $gettext('2. Untertitel') }}
                                <input type="text" v-model="currentSecondSubtitle" />
                            </label>
                            <label>
                                {{ $gettext('Textfarbe') }}
                                <studip-select
                                    :options="colors"
                                    label="hex"
                                    :reduce="color => color.hex"
                                    :clearable="false"
                                    v-model="currentTextColor"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label v-if="hasTextBackgroundColor">
                                {{ $gettext('Texthintergrundfarbe') }}
                                <studip-select
                                    :options="colors"
                                    label="hex"
                                    :reduce="color => color.hex"
                                    :clearable="false"
                                    v-model="currentTextBackgroundColor"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <template v-if="hasIcon">
                                <label>
                                    {{ $gettext('Icon') }}
                                    <studip-select :clearable="false" :options="icons" v-model="currentIcon">
                                        <template #open-indicator="selectAttributes">
                                            <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                        </template>
                                        <template #no-options>
                                            {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                        </template>
                                        <template #selected-option="option">
                                            <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                                        </template>
                                        <template #option="option">
                                            <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                                        </template>
                                    </studip-select>
                                </label>
                                <label>
                                    {{ $gettext('Icon-Farbe') }}
                                    <studip-select
                                        :options="iconColors"
                                        label="name"
                                        :reduce="iconColor => iconColor.class"
                                        :clearable="false"
                                        v-model="currentIconColor"
                                    >
                                        <template #open-indicator="selectAttributes">
                                            <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                        </template>
                                        <template #no-options>
                                            {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                        </template>
                                        <template #selected-option="{name, hex}">
                                            <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                        </template>
                                        <template #option="{name, hex}">
                                            <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                        </template>
                                    </studip-select>
                                </label>
                            </template>
                        </form>
                    </courseware-tab>
                    <courseware-tab
                        :index="2"
                        :name="$gettext('Hintergrund')"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Hintergrundtyp') }}
                                <select v-model="currentBackgroundType">
                                    <option value="color">{{ $gettext('Farbe') }}</option>
                                    <option value="gradient">{{ $gettext('Farbverlauf') }}</option>
                                    <option value="image">{{ $gettext('Bild') }}</option>
                                    <option value="structural-element-image">{{ $gettext('Seiten-Bild') }}</option>
                                </select>
                            </label>
                            <label  v-if="currentBackgroundType === 'color'">
                                {{ $gettext('Hintergrundfarbe') }}
                                <studip-select
                                    :options="colors"
                                    label="hex"
                                    :reduce="color => color.hex"
                                    v-model="currentBackgroundColor"
                                    :clearable="false"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label v-if="currentBackgroundType === 'image'">

                                <div>{{ $gettext('Hintergrundbild') }}</div>

                                <template v-if="currentBackgroundImageId">
                                    <template v-if="currentBackgroundImageType === 'file-refs'">
                                        <StockImageThumbnail :url="currentBackgroundURL" width="8rem" contain />
                                    </template>
                                    <template v-if="currentBackgroundImageType === 'stock-images'">
                                        <StockImageSelectableImageCard
                                            :stock-image="selectedStockImage"
                                            v-if="selectedStockImage"
                                            />
                                    </template>
                                    <label>
                                        <button class="button" type="button" @click="onClickRemoveBackgroundImage">
                                            {{ $gettext('Bild entfernen') }}
                                        </button>
                                    </label>
                                </template>

                                <template v-if="!currentBackgroundImageId">
                                    <courseware-file-chooser
                                        v-model="currentBackgroundImageId"
                                        :isImage="true"
                                        @selectFile="onSelectFile"
                                        />
                                    <div style="margin-block-start: 1em">{{ $gettext('oder') }}</div>
                                    <button class="button" type="button" @click="showStockImageSelector = true">
                                        {{ $gettext('Aus dem Bilderpool auswählen') }}
                                    </button>
                                    <StockImageSelector
                                        v-if="showStockImageSelector"
                                        @close="showStockImageSelector = false"
                                        @select="onSelectStockImage"
                                        />
                                </template>

                            </label>
                            <label v-if="currentBackgroundType === 'gradient'">
                                {{ $gettext('Hintergrundfarbverlauf') }}
                                <select v-model="currentGradient">
                                    <option value="color-blue">{{ $gettext('Blau') }}</option>
                                    <option value="color-purple">{{ $gettext('Purpur') }}</option>
                                    <option value="color-violet">{{ $gettext('Violet') }}</option>
                                    <option value="color-red">{{ $gettext('Rot') }}</option>
                                    <option value="color-orange">{{ $gettext('Orange') }}</option>
                                    <option value="color-yellow">{{ $gettext('Gelb') }}</option>
                                    <option value="color-green">{{ $gettext('Grün') }}</option>
                                    <option value="color-petrol">{{ $gettext('Petrol') }}</option>
                                    <option value="color-grey">{{ $gettext('Grau') }}</option>
                                    <option value="paper">{{ $gettext('Papier') }}</option>
                                    <option value="blueprint">{{ $gettext('Blueprint') }}</option>
                                    <option value="grid">{{ $gettext('Gitter') }}</option>
                                    <option value="blue-bar">{{ $gettext('Blaue Balken') }}</option>
                                    <option value="green-bar">{{ $gettext('Grüne Balken') }}</option>
                                    <option value="faded-sun">{{ $gettext('Faded Sun') }}</option>
                                    <option value="romantic-sun">{{ $gettext('Romantic Sun') }}</option>
                                    <option value="bright-rain">{{ $gettext('Bright Rain') }}</option>
                                    <option value="soft-weather">{{ $gettext('Soft Weather') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>

            </template>
            <template #info>{{ $gettext('Informationen zum Blickfang-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import { blockMixin } from './block-mixin.js';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import { mapGetters, mapActions } from 'vuex';
import contentIcons from './content-icons.js';
import StockImageSelector from '../stock-images/SelectorDialog.vue';
import StockImageSelectableImageCard from '../stock-images/SelectableImageCard.vue';
import StockImageThumbnail from '../stock-images/Thumbnail.vue';

export default {
    name: 'courseware-headline-block',
    mixins: [blockMixin, colorMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareTabs,
        CoursewareTab,
        StockImageSelector,
        StockImageSelectableImageCard,
        StockImageThumbnail,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentSubtitle: '',
            currentSecondSubtitle: '',
            currentStyle: '',
            currentHeight: '',
            currentBackgroundColor: '',
            currentTextColor: '',
            currentTextBackgroundColor: '',
            currentIcon: '',
            currentIconColor: '',
            currentBackgroundType: '',
            currentBackgroundImageId: '',
            currentBackgroundImageType: '',
            currentBackgroundImage: {},
            currentBackgroundURL: '',
            currentGradient: '',
            showStockImageSelector: false,
            selectedStockImage: null,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            stockImageById: 'stock-images/byId',
            urlHelper: 'urlHelper',
            relatedTermOfUse: 'terms-of-use/related',
            currentStructuralElementImageURL: 'currentStructuralElementImageURL'
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        subtitle() {
            return this.block?.attributes?.payload?.subtitle;
        },
        secondSubtitle() {
            return this.block?.attributes?.payload?.second_subtitle;
        },
        style() {
            return this.block?.attributes?.payload?.style;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        backgroundColor() {
            return this.block?.attributes?.payload?.background_color;
        },
        gradient() {
            return this.block?.attributes?.payload?.gradient;
        },
        textColor() {
            return this.block?.attributes?.payload?.text_color;
        },
        textBackgroundColor() {
            return this.block?.attributes?.payload?.text_background_color ?? '#000000';
        },
        icon() {
            return this.block?.attributes?.payload?.icon;
        },
        iconColor() {
            return this.block?.attributes?.payload?.icon_color;
        },
        backgroundImageId() {
            return this.block?.attributes?.payload?.background_image_id;
        },
        backgroundImageType() {
            return this.block?.attributes?.payload?.background_image_type;
        },
        backgroundImage() {
            return this.block?.attributes?.payload?.background_image;
        },
        backgroundType() {
            return this.block?.attributes?.payload?.background_type;
        },
        complementBackgroundColor() {
            return this.calcComplement(this.backgroundColor);
        },
        icons() {
            return contentIcons;
        },
        colors() {
            return this.mixinColors;
        },
        iconColors() {
            return this.mixinColors.filter(color => color.icon && color.class !== 'studip-lightblue');
        },
        textStyle() {
            let style = {};
            style.color = this.currentTextColor;

            return style;
        },
        headlineStyle() {
            let style = {};
            style['background-color'] = this.currentBackgroundColor;
            if (this.currentBackgroundType === 'image') {
                style['background-image'] = 'url(' + this.currentBackgroundURL + ')';
                style['background-color'] = 'transparent';
            }
            if (this.currentBackgroundType === 'structural-element-image') {
                if (this.currentStructuralElementImageURL) {
                    style['background-image'] = 'url(' + this.currentStructuralElementImageURL + ')';
                    style['background-color'] = 'transparent';
                } else {
                    style['background-color'] = '#28497c';
                }

            }
            if (this.hasGradient) {
                style['background-color'] = 'transparent';
            }

            return style;
        },
        headlineTextboxStyle() {
            return {
                rgba: { 'background-color': this.hexToRgbA(this.currentTextBackgroundColor, '0.5') },
                hex: { 'background-color': this.currentTextBackgroundColor }
            };
        },
        hasSubtitle() {
            return !['bigicon_before'].includes(this.currentStyle);
        },
        subtitleIsSet() {
            return this.currentSubtitle !== '';
        },
        hasSecondSubtitle() {
            return ['skew_text'].includes(this.currentStyle);
        },
        hasIcon() {
            return ['bigicon_top', 'bigicon_before', 'icon_top_lines'].includes(this.currentStyle);
        },
        hasTextBackgroundColor() {
            return ['ribbon', 'vertical'].includes(this.currentStyle);
        },
        hasGradient() {
            return this.currentBackgroundType === 'gradient';
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            loadStockImage: 'stock-images/loadById',
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSubtitle = this.subtitle;
            this.currentSecondSubtitle = this.secondSubtitle;
            this.currentStyle = this.style;
            this.currentHeight = this.height;
            this.currentBackgroundColor = this.backgroundColor;
            this.currentGradient = this.gradient !== '' ? this.gradient : 'color-blue';
            this.currentTextColor = this.textColor;
            this.currentTextBackgroundColor = this.textBackgroundColor;
            this.currentIcon = this.icon;
            this.currentIconColor = this.iconColor;
            this.currentBackgroundType = this.backgroundType;
            this.currentBackgroundImageId = this.backgroundImageId;
            this.currentBackgroundImageType = this.backgroundImageType ?? 'file-refs';
            if (this.currentBackgroundImageId !== '') {
                this.loadFile();
            }
        },
        async loadFile() {
            const id = this.currentBackgroundImageId;
            const type = this.currentBackgroundImageType;

            if (type === 'file-refs') {
                const options = { include: 'terms-of-use' };
                await this.loadFileRef({ id: id, options });
                const fileRef = this.fileRefById({ id: id });
                if (
                    fileRef &&
                    this.relatedTermOfUse({ parent: fileRef, relationship: 'terms-of-use' }).attributes[
                        'download-condition'
                    ] === 0
                ) {
                    this.updateCurrentBackgroundImage({
                        id: fileRef.id,
                        type: 'file-refs',
                        name: fileRef.attributes.name,
                        download_url: this.urlHelper.getURL(
                            'sendfile.php',
                            { type: 0, file_id: fileRef.id, file_name: fileRef.attributes.name },
                            true
                        ),
                    });
                }
            } else if (type === 'stock-images') {
                await this.loadStockImage({ id });
                const stockImage = this.stockImageById({ id });
                if (stockImage) {
                    this.selectedStockImage = stockImage;
                    this.updateCurrentBackgroundImage({
                        id,
                        type: 'stock-images',
                        name: stockImage.attributes.title,
                        download_url:
                            stockImage.attributes['download-urls']['medium'] ??
                            stockImage.attributes['download-urls']['small'],
                    });
                }
            }
        },
        updateCurrentBackgroundImage(file) {
            this.currentBackgroundImage = file;
            this.currentBackgroundImageId = file.id;
            this.currentBackgroundImageType = file.type;
            this.currentBackgroundURL = file.download_url;
        },
        onSelectFile(file) {
            this.updateCurrentBackgroundImage({ ...file, type: 'file-refs' });
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.subtitle = this.currentSubtitle;
            attributes.payload.second_subtitle = this.currentSecondSubtitle;
            attributes.payload.style = this.currentStyle;
            attributes.payload.height = this.currentHeight;
            attributes.payload.text_color = this.currentTextColor;
            attributes.payload.text_background_color = this.currentTextBackgroundColor;
            attributes.payload.icon = this.currentIcon;
            attributes.payload.icon_color = this.currentIconColor;
            attributes.payload.background_type = this.currentBackgroundType;
            attributes.payload.background_color = '';
            attributes.payload.gradient = '';
            attributes.payload.background_image_id = '';
            attributes.payload.background_image_type = '';

            if (this.currentBackgroundType === 'color') {
                attributes.payload.background_color = this.currentBackgroundColor;
            }
            if (this.currentBackgroundType === 'image') {
                attributes.payload.background_image_id = this.currentBackgroundImageId;
                attributes.payload.background_image_type = this.currentBackgroundImageType;            }
            if (this.currentBackgroundType === 'gradient') {
                attributes.payload.gradient = this.currentGradient;
            }

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        calcComplement(color) {
            const RGB = this.calcRGB(color);

            return '#' + this.compToHex(255 - RGB.r) + this.compToHex(255 - RGB.g) + this.compToHex(255 - RGB.b);
        },
        calcIconColor(color) {
            const RGB = this.calcRGB(color);

            return (RGB.r + RGB.g + RGB.b) / 3 > 129 ? 'black' : 'white';
        },
        calcRGB(color) {
            color = color.slice(1); // remove #
            let val = parseInt(color, 16);
            let r = val >> 16;
            let g = (val >> 8) & 0x00ff;
            let b = val & 0x0000ff;

            if (g > 255) {
                g = 255;
            } else if (g < 0) {
                g = 0;
            }
            if (b > 255) {
                b = 255;
            } else if (b < 0) {
                b = 0;
            }

            return { r: r, g: g, b: b };
        },
        compToHex(comp) {
            let hex = comp.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        },
        hexToRgbA(hex, a){
            const RGB = this.calcRGB(hex);

            return 'rgba(' + RGB.r + ',' + RGB.g + ',' + RGB.b + ',' + a +')';
        },
        onSelectStockImage(stockImage) {
            this.updateCurrentBackgroundImage({
                id: stockImage.id,
                type: 'stock-images',
                name: stockImage.attributes.title,
                download_url:
                    stockImage.attributes['download-urls']['medium'] ?? stockImage.attributes['download-urls']['small'],
            });
            this.selectedStockImage = stockImage;
            this.showStockImageSelector = false;
        },
        onClickRemoveBackgroundImage() {
            this.currentBackgroundImageId = '';
            this.currentBackgroundImageType = '';
            this.selectedStockImage = null;
        },
    },
};
</script>
