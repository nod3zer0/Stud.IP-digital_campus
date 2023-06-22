<template>
    <div class="cw-block cw-block-typewriter">
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
                <div class="cw-typewriter-content">
                    <vue-typer
                        :text="currentText"
                        initial-action="typing"
                        :repeat="0"
                        :type-delay="typeDelay"
                        caret-animation="smooth"
                        :class="[currentFont, currentSize]"
                    ></vue-typer>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label class="col-4">
                        {{ $gettext('Text') }}
                        <textarea v-model="currentText" />
                    </label>
                    <br>
                    <label class="col-1">
                        {{ $gettext('Geschwindigkeit') }}
                        <select v-model="currentSpeed" @change="restartTyping">
                            <option value="0">{{ $gettext('Langsam') }}</option>
                            <option value="1">{{ $gettext('Normal') }}</option>
                            <option value="2">{{ $gettext('Schnell') }}</option>
                            <option value="3">{{ $gettext('Sehr schnell') }}</option>
                        </select>
                    </label>
                    <label class="col-1">
                        {{ $gettext('Schriftart') }}
                        <select v-model="currentFont">
                            <option value="font-default">{{ $gettext('Standard') }}</option>
                            <option value="font-typewriter">Lucida Sans Typewriter</option>
                            <option value="font-trebuchet">Trebuchet MS</option>
                            <option value="font-tahoma">Tahoma</option>
                            <option value="font-georgia">Georgia</option>
                            <option value="font-narrow">Arial Narrow</option>
                        </select>
                    </label>
                    <label class="col-1">
                        {{ $gettext('Schriftgröße') }}
                        <select v-model="currentSize">
                            <option value="size-default">100%</option>
                            <option value="size-tall">125%</option>
                            <option value="size-grande">150%</option>
                            <option value="size-huge">200%</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Schreibmaschinen-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { blockMixin } from './block-mixin.js';
import { VueTyper } from 'vue-typer';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-typewriter-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        VueTyper,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            speeds: [200, 100, 50, 25],
            typing: false,
            speedClasses: [
                'cw-typewriter-letter-fadein-slow',
                'cw-typewriter-letter-fadein-normal',
                'cw-typewriter-letter-fadein-fast',
                'cw-typewriter-letter-fadein-veryfast',
            ],
            currentText: ' ',
            currentSpeed: '',
            currentFont: '',
            currentSize: '',
        };
    },
    computed: {
        text() {
            return this.block?.attributes?.payload?.text;
        },
        speed() {
            return this.block?.attributes?.payload?.speed;
        },
        typeDelay() {
            return this.speeds[this.currentSpeed];
        },
        font() {
            return this.block?.attributes?.payload?.font;
        },
        size() {
            return this.block?.attributes?.payload?.size;
        }
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentText = this.text;
            this.currentSpeed = this.speed;
            this.currentFont = this.font;
            this.currentSize = this.size;
        },
        restartTyping() {
            let text = this.currentText;
            this.currentText = ' ';
            this.$nextTick(()=> {
                this.currentText = text;
            });
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.text = this.currentText;
            attributes.payload.speed = this.currentSpeed;
            attributes.payload.font = this.currentFont;
            attributes.payload.size = this.currentSize;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        }
    },
};
</script>
