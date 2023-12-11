<template>
    <component :is="tag" class="cw-tile" :class="[color]">
        <studip-ident-image v-model="identimage" :baseColor="tileColor.hex" :pattern="title" />
        <div class="preview-image" :style="previewImageStyle">
            <div
                v-if="handle"
                class="overlay-handle cw-tile-handle"
                tabindex="0"
                role="option"
                aria-describedby="operation"
                :id="handleId"
                @keydown="$emit('handle-keydown', $event)"
            ></div>
            <div class="overlay-text" v-if="hasImageOverlay">
                <slot name="image-overlay"></slot>
            </div>
            <div class="overlay-action-menu" v-if="hasImageOverlayWithActionMenu">
                <slot name="image-overlay-with-action-menu"></slot>
            </div>
        </div>
        <component
            :is="hasDescriptionLink ? 'a' : 'div'"
            :href="hasDescriptionLink ? descriptionLink : ''"
            :title="descriptionTitle"
            class="description"
        >
            <header :class="[icon ? 'description-icon-' + icon : '']">
                {{ title }}
            </header>
            <div v-if="displayProgress" :title="progressTitle" class="progress-wrapper">
                <progress :value="progress" max="100">{{ progress }}</progress>
            </div>
            <div class="description-text-wrapper">
                <p><slot name="description"></slot></p>
            </div>
            <footer>
                <slot name="footer"></slot>
            </footer>
        </component>
    </component>
</template>

<script>
import colorMixin from '@/vue/mixins/courseware/colors.js';
import StudipIdentImage from './../../StudipIdentImage.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tile',
    mixins: [colorMixin],
    components: {
        StudipIdentImage
    },
    props: {
        tag: {
            type: String,
            default: 'div',
            validator: (tag) => {
                return ['div', 'li'].includes(tag);
            },
        },
        color: {
            type: String,
            default: 'studip-blue',
            validator: (value) => {
                return [
                    'black',
                    'charcoal',
                    'royal-purple',
                    'iguana-green',
                    'queen-blue',
                    'verdigris',
                    'mulberry',
                    'pumpkin',
                    'sunglow',
                    'apple-green',
                    'studip-blue',
                    'studip-lightblue',
                    'studip-green',
                    'studip-yellow',
                    'studip-gray',
                ].includes(value);
            },
        },
        title: {
            type: String,
            default: '–',
        },
        icon: {
            type: String,
        },
        imageUrl: {
            type: String,
        },
        displayProgress: {
            type: Boolean,
            default: false,
        },
        progress: {
            type: Number,
            validator: (value) => {
                return value >= 0 && value <= 100;
            },
        },
        descriptionLink: {
            type: String,
            default: '',
        },
        descriptionTitle: {
            type: String,
            default: '',
        },
        handle: {
            type: Boolean,
            default: false,
        },
        handleId: {
            type: String
        }
    },
    data() {
        return {
            identimage: '',
        };
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher'
        }),
        hasImage() {
            return this.imageUrl !== '' && this.imageUrl !== undefined;
        },
        hasImageOverlay() {
            return this.$slots['image-overlay'] !== undefined;
        },
        hasImageOverlayWithActionMenu() {
            return this.$slots['image-overlay-with-action-menu'] !== undefined;
        },
        previewImageStyle() {
            if (this.hasImage) {
                return { 'background-image': 'url(' + this.imageUrl + ')' };
            } 

            return { 'background-image': 'url(' + this.identimage + ')' };
        },
        progressTitle() {
            if (this.userIsTeacher) {
                return this.$gettextInterpolate(this.$gettext("Fortschritt aller Teilnehmenden: %{progress}%"), { progress: this.progress });    
            }
            return this.$gettextInterpolate(this.$gettext("Mein Fortschritt: %{progress}%"), { progress: this.progress });
        },
        hasDescriptionLink() {
            return this.descriptionLink !== '';
        },
        tileColor() {
            return this.mixinColors.find((color) => color.class === this.color);
        },
    },
    methods: {
        showProgress(e) {
            e.preventDefault();
            this.$emit('showProgress');
        },
    },
}
</script>
