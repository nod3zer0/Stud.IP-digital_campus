<template>
    <div class="cw-block cw-block-dialog-cards">
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
                <div class="cw-block-dialog-cards-content">
                    <button
                        class="cw-dialogcards-prev cw-dialogcards-navbutton"
                        :class="{ 'cw-dialogcards-prev-disabled': hasNoPerv }"
                        @click="prevCard"
                        :title="hasNoPerv ? $gettext('keine vorherige Karte') : $gettext('zur vorherigen Karte')"
                    ></button>
                    <div class="cw-dialogcards">
                        <div
                            class="scene scene--card"
                            :class="[card.active ? 'active' : '']"
                            v-for="card in currentCards"
                            :key="card.index"
                        >
                            <div
                                class="card"
                                tabindex="0"
                                :title="$gettext('Karte umdrehen')"
                                @click="flipCard"
                                @keydown.enter="flipCard"
                                @keydown.space="flipCard"
                            >
                                <div class="card__face card__face--front">
                                    <img v-if="card.front_file.length !== 0" :src="card.front_file.download_url" />
                                    <div v-else class="cw-dialogcards-front-no-image"></div>
                                    <p>{{ card.front_text }}</p>
                                </div>
                                <div class="card__face card__face--back">
                                    <img v-if="card.back_file.length !== 0" :src="card.back_file.download_url" />
                                    <div v-else class="cw-dialogcards-back-no-image"></div>
                                    <p>{{ card.back_text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button
                        class="cw-dialogcards-next cw-dialogcards-navbutton"
                        :class="{ 'cw-dialogcards-next-disabled': hasNoNext }"
                        @click="nextCard"
                        :title="hasNoNext ? $gettext('keine nächste Karte') : $gettext('zur nächsten Karte')"
                    ></button>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <button class="button add" @click="addCard">{{ $gettext('Karte hinzufügen') }}</button>
                <courseware-tabs
                    v-if="currentCards.length > 0"
                    :setSelected="setCardTab"
                    @selectTab="activateCard(parseInt($event.name.replace($gettext('Karte') + ' ', '')) - 1)"
                >
                    <courseware-tab
                        v-for="(card, index) in currentCards"
                        :key="index"
                        :index="index"
                        :name="$gettext('Karte') + ' ' + (index + 1).toString()"
                        :selected="index === 0"
                        canBeEmpty
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Bild Vorderseite') }}:
                                <courseware-file-chooser
                                    v-model="card.front_file_id"
                                    :isImage="true"
                                    :canBeEmpty="true"
                                    @selectFile="updateFile(index, 'front', $event)"
                                />
                            </label>
                            <label>
                                {{ $gettext('Text Vorderseite') }}:
                                <input type="text" v-model="card.front_text" />
                            </label>
                            <label>
                                {{ $gettext('Bild Rückseite') }}:
                                <courseware-file-chooser
                                    v-model="card.back_file_id"
                                    :isImage="true"
                                    :canBeEmpty="true"
                                    @selectFile="updateFile(index, 'back', $event)"
                                />
                            </label>
                            <label>
                                {{ $gettext('Text Rückseite') }}:
                                <input type="text" v-model="card.back_text" />
                            </label>
                            <label v-if="!onlyCard">
                                <button class="button trash" @click="removeCard(index)">
                                    {{ $gettext('Karte entfernen') }}
                                </button>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Lernkarten-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-dialog-cards-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentCards: [],
            setCardTab: 0,
        };
    },
    computed: {
        cards() {
            return this.block?.attributes?.payload?.cards;
        },
        onlyCard() {
            return this.currentCards.length === 1;
        },
        hasNoPerv() {
            if (this.currentCards[0] !== undefined) {
                return this.currentCards[0].active;
            } else {
                return true;
            }
        },
        hasNoNext() {
            if (this.currentCards[this.currentCards.length - 1] !== undefined) {
                return this.currentCards[this.currentCards.length - 1].active;
            } else {
                return true;
            }
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
            if (this.cards !== '') {
                let cards = JSON.parse(JSON.stringify(this.cards));
                cards.forEach((card, index) => {
                    card.active = false;
                    if (index === 0) {
                        card.active = true;
                    }
                });
                this.currentCards = cards;
            }
            this.setCardTab = 0;
        },
        storeBlock() {
            let cards = JSON.parse(JSON.stringify(this.currentCards));
            // don't store the file object
            cards.forEach((card) => {
                delete card.front_file;
                delete card.back_file;
                delete card.active;
            });
            let attributes = {};
            attributes.payload = {};
            attributes.payload.cards = cards;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        updateFile(cardIndex, side, file) {
            if (side === 'front') {
                if (file) {
                    this.currentCards[cardIndex].front_file_id = file.id;
                    this.currentCards[cardIndex].front_file = file;
                } else {
                    this.currentCards[cardIndex].front_file_id = '';
                    this.currentCards[cardIndex].front_file = [];
                }
            }
            if (side === 'back') {
                if (file) {
                    this.currentCards[cardIndex].back_file_id = file.id;
                    this.currentCards[cardIndex].back_file = file;
                } else {
                    this.currentCards[cardIndex].back_file_id = '';
                    this.currentCards[cardIndex].back_file = [];
                }
            }
        },
        addCard() {
            this.currentCards.push({
                index: this.currentCards.length,
                front_file_id: '',
                front_file: [],
                front_text: '',
                back_file_id: '',
                back_text: '',
                back_file: [],
                active: false,
            });
            const index = this.currentCards.length - 1;
            this.activateCard(index);
            this.$nextTick(() => {
                this.setCardTab = index;
            });
        },
        removeCard(cardIndex) {
            this.currentCards = this.currentCards.filter((val, index) => {
                return !(index === cardIndex);
            });
            this.$nextTick(() => {
                this.setCardTab = 0;
            });
        },
        flipCard(event) {
            event.currentTarget.classList.toggle('is-flipped');
        },
        nextCard() {
            let view = this;
            this.currentCards.every((card, index) => {
                if (card.active) {
                    if (view.currentCards.length > index + 1) {
                        card.active = false;
                        view.currentCards[index + 1].active = true;
                        view.setCardTab = index + 1;
                    }
                    return false; // end every
                } else {
                    return true; // continue every
                }
            });
        },
        prevCard() {
            let view = this;
            this.currentCards.every((card, index) => {
                if (card.active) {
                    if (index > 0) {
                        card.active = false;
                        view.currentCards[index - 1].active = true;
                        view.setCardTab = index - 1;
                    }
                    return false; // end every
                } else {
                    return true; // continue every
                }
            });
        },
        activateCard(selectedIndex) {
            selectedIndex = parseInt(selectedIndex);
            if (selectedIndex > this.currentCards.length - 1) {
                console.log('can not select this card');
                return false;
            }
            this.currentCards.forEach((card, index) => {
                if (index === selectedIndex) {
                    card.active = true;
                } else {
                    card.active = false;
                }
            });
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/dialog-cards.scss';
</style>
