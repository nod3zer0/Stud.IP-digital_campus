<template>
    <div class="cw-root-content-hint" v-if="hideRoot">
        <courseware-companion-box
            :msgCompanion="
                $gettext(
                    'In diesem Lernmaterial wird die Startseite ausgeblendet. Dies können Sie in den Einstellungen des Lernmaterials ändern. Wenn Sie die Einstellung beibehalten wollen, legen Sie bitte eine Seite an.'
                )
            "
        >
            <template v-slot:companionActions>
                <button v-if="canEdit" class="button" @click="addPage">
                    {{ $gettext('Eine Seite hinzufügen') }}
                </button>
            </template>
        </courseware-companion-box>
    </div>
    <div v-else class="cw-root-content-wrapper">
        <div class="cw-root-content" :class="['cw-root-content-' + rootLayout]">
            <div class="cw-root-content-img" :style="image">
                <section class="cw-root-content-description" :style="bgColor">
                    <img v-if="imageIsSet" class="cw-root-content-description-img" :src="imageURL" />
                    <template v-else>
                        <studip-ident-image
                            class="cw-root-content-description-img"
                            v-model="identImageCanvas"
                            :showCanvas="true"
                            :baseColor="bgColorHex"
                            :pattern="structuralElement.attributes.title"
                        />
                        <studip-ident-image
                            v-model="identImage"
                            :width="1095"
                            :height="withTOC ? 300 : 480"
                            :baseColor="bgColorHex"
                            :pattern="structuralElement.attributes.title"
                        />
                    </template>
                    <div class="cw-root-content-description-text">
                        <h1>{{ structuralElement.attributes.title }}</h1>
                        <p>
                            {{ structuralElement.attributes.payload.description }}
                        </p>
                    </div>
                </section>
            </div>
        </div>
        <div v-if="withTOC" class="cw-root-content-toc">
            <ul class="cw-tiles">
                <li
                    v-for="child in childElements"
                    :key="child.id"
                    class="tile"
                    :class="[child.attributes.payload.color]"
                >
                    <router-link :to="'/structural_element/' + child.id" :title="child.attributes.title">
                        <div
                            v-if="hasImage(child)"
                            class="preview-image"
                            :style="getChildStyle(child)"
                        ></div>
                        <studip-ident-image
                            v-else
                            :baseColor="getColor(child).hex"
                            :pattern="child.attributes.title"
                            :showCanvas="true"
                        />
                        <div class="description">
                            <header
                                :class="[
                                    child.attributes.purpose !== ''
                                        ? 'description-icon-' + child.attributes.purpose
                                        : '',
                                ]"
                            >
                                {{ child.attributes.title || '–' }}
                            </header>
                            <div class="description-text-wrapper">
                                <p>{{ child.attributes.payload.description }}</p>
                            </div>
                            <footer>
                                {{ countChildChildren(child) }}
                                <translate :translate-n="countChildChildren(child)" translate-plural="Seiten">
                                    Seite
                                </translate>
                            </footer>
                        </div>
                    </router-link>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import CoursewareCompanionBox from './../layouts/CoursewareCompanionBox.vue';
import StudipIdentImage from './../../StudipIdentImage.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-root-content',
    mixins: [colorMixin],
    props: {
        structuralElement: Object,
        canEdit: Boolean,
    },
    components: {
        CoursewareCompanionBox,
        StudipIdentImage,
    },
    data() {
        return {
            identImage: '',
            identImageCanvas: '',
        };
    },
    computed: {
        ...mapGetters({
            rootLayout: 'rootLayout',
            childrenById: 'courseware-structure/children',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
        }),
        imageURL() {
            return this.structuralElement.relationships?.image?.meta?.['download-url'];
        },
        imageIsSet() {
            return this.imageURL !== undefined;
        },
        image() {
            let style = {};
            const backgroundURL = this.imageIsSet ? this.imageURL : this.identImage;

            style.backgroundImage = 'url(' + backgroundURL + ')';
            style.height = this.withTOC ? '300px' : '480px';

            return style;
        },
        bgColorHex() {
            const elementColor = this.structuralElement?.attributes?.payload?.color ?? 'studip-blue';
            const color = this.mixinColors.find((c) => {
                return c.class === elementColor;
            });
            return color.hex;
        },
        bgColor() {
            return { 'background-color': this.bgColorHex };
        },
        withTOC() {
            return this.rootLayout === 'toc';
        },
        hideRoot() {
            return this.rootLayout === 'none';
        },
        childElements() {
            return this.childrenById(this.structuralElement.id).map((id) => this.structuralElementById({ id }));
        },
    },
    methods: {
        ...mapActions({
            showElementAddDialog: 'showElementAddDialog',
        }),
        getChildStyle(child) {
            let url = child.relationships?.image?.meta?.['download-url'];

            if (url) {
                return { 'background-image': 'url(' + url + ')' };
            } else {
                return {};
            }
        },
        countChildChildren(child) {
            return this.childrenById(child.id).length + 1;
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },
        getColor(child) {
            return this.mixinColors.find((color) => color.class === child.attributes.payload.color);
        },
        addPage() {
            this.showElementAddDialog(true);
        }
    },
};
</script>
<style scoped lang="scss">
.cw-root-content {
    max-width: 1095px;
    margin-bottom: 1em;
    overflow: hidden;
    .cw-root-content-img {
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }
    .cw-root-content-description {
        display: flex;
        flex-direction: row;
        margin: 0 8em;
        padding: 2em 4px 2em 2em;
        position: relative;
        top: 8em;

        .cw-root-content-description-img {
            width: 240px;
            height: fit-content;
            margin-right: 2em;
        }
        .cw-root-content-description-text {
            max-height: calc(480px - 18em);
            overflow-y: auto;
            &::-webkit-scrollbar {
                width: 2px;
            }

            &::-webkit-scrollbar-track {
                box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.3);
            }

            &::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.4);
            }
            h1,
            p {
                color: #fff;
                margin-right: 2em;
            }
        }
    }
}
.cw-root-content-toc {
    max-width: 1095px;
    margin-bottom: 1em;
    .cw-root-content-description {
        margin: 0 8em;
        top: 1.5em;
        .cw-root-content-description-text {
            max-height: calc(300px - 6em);
        }
    }
}
.cw-root-content-hint {
    max-width: 1095px;
}
</style>
