<template>
    <div class="cw-block cw-block-gallery">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @showEdit="showEdit"
            @storeEdit="storeBlock"
            @closeEdit="closeEdit"
        >
            <template #content>
                <template v-if="files.length !== 0">
                    <div
                        v-if="currentLayout === 'carousel'"
                        class="cw-block-gallery-content"
                        :style="{ height: `${currentHeight}px` }"
                    >
                        <div
                            v-for="(image, index) in files"
                            :key="image.id"
                            ref="images"
                            class="cw-block-gallery-slides cw-block-gallery-fade"
                        >
                            <div class="cw-block-gallery-number-text">{{ index + 1 }} / {{ files.length }}</div>
                            <img
                                :src="image.meta['download-url']"
                                :style="{ height: `${currentHeight}px` }"
                                @load="
                                    if (files.length - 1 === index) {
                                        startGallery();
                                    }
                                "
                            />
                            <div
                                class="cw-block-gallery-file-description"
                                :class="{ 'show-on-hover': currentMouseoverFileNames === 'true' }"
                            >
                                <p v-if="currentShowFileNames === 'true'">{{ image?.attributes?.name }}</p>
                                <p v-if="currentShowFileDescription === 'true'">{{ image?.attributes?.description }}</p>
                            </div>
                        </div>
                        <div v-if="currentNav === 'true'">
                            <button
                                class="cw-block-gallery-prev"
                                :title="$gettext('Vorheriges Bild')"
                                @click="plusSlides(-1)"
                            ></button>
                            <button
                                class="cw-block-gallery-next"
                                :title="$gettext('Nächstes Bild')"
                                @click="plusSlides(1)"
                            ></button>
                        </div>
                    </div>
                    <div v-if="currentLayout === 'grid'" class="cw-block-gallery-content">
                        <div class="cw-block-gallery-grid">
                            <figure v-for="image in files" :key="image.id" :style="{ 'max-width': gridWidth }">
                                <img :src="image.meta['download-url']" :title="image?.attributes?.name" />
                                <figcaption v-if="showDescription">
                                    <p v-if="currentShowFileNames === 'true'" class="cw-block-gallery-grid-file-name">
                                        {{ image?.attributes?.name }}
                                    </p>
                                    <p
                                        v-if="currentShowFileDescription === 'true'"
                                        class="cw-block-gallery-grid-file-description"
                                    >
                                        {{ image?.attributes?.description }}
                                    </p>
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                </template>
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab :index="0" :name="$gettext('Einstellungen')" :selected="true">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Layout') }}
                                <select v-model="currentLayout">
                                    <option value="carousel">{{ $gettext('Karussell') }}</option>
                                    <option value="grid">{{ $gettext('Gitter') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ $gettext('Ordner') }}
                                <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                            </label>
                            <label v-if="currentLayout === 'carousel'">
                                {{ $gettext('Höhe') }}
                                <input type="number" min="0" max="800" v-model="currentHeight" />
                            </label>
                            <label v-if="currentLayout === 'grid'">
                                {{ $gettext('Gitter-Spalten') }}
                                <select v-model="currentCols">
                                    <option :value="100">1</option>
                                    <option :value="50">2</option>
                                    <option :value="33">3</option>
                                    <option :value="25">4</option>
                                    <option :value="20">5</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :index="1" :name="$gettext('Beschreibung')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Dateinamen anzeigen') }}
                                <select v-model="currentShowFileNames">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ $gettext('Dateibeschreibung anzeigen') }}
                                <select v-model="currentShowFileDescription">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                            <label v-if="showDescription && currentLayout === 'carousel'">
                                {{ $gettext('Beschreibung erscheint bei Mouseover') }}
                                <studip-tooltip-icon
                                    :text="
                                        $gettext(
                                            'Der Beschreibungstext wird angezeigt, wenn Sie den Mauszeiger über das Bild bewegen.'
                                        )
                                    "
                                />
                                <select v-model="currentMouseoverFileNames">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab v-if="currentLayout === 'carousel'" :index="2" :name="$gettext('Autoplay')">
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Autoplay') }}
                                <select v-model="currentAutoplay">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                            <label v-if="currentAutoplay === 'true'">
                                {{ $gettext('Autoplay Timer in Sekunden') }}
                                <input type="number" min="1" max="60" v-model="currentAutoplayTimer" />
                            </label>
                            <label v-if="currentAutoplay === 'true'">
                                {{ $gettext('Navigation') }}
                                <select v-model="currentNav">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Galerie-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-gallery-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentFolderId: '',
            currentLayout: '',
            currentAutoplay: '',
            currentNav: '',
            currentHeight: '',
            currentCols: 33,
            currentShowFileNames: '',
            currentShowFileDescription: '',
            currentMouseoverFileNames: '',
            currentAutoplayTimer: '',
            editModeFiles: [],
            slideIndex: 0,
            editMode: false,
        };
    },
    computed: {
        ...mapGetters({
            urlHelper: 'urlHelper',
            relatedFileRefs: 'file-refs/related',
            relatedTermOfUse: 'terms-of-use/related',
        }),
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        layout() {
            return this.block?.attributes?.payload?.layout ?? 'carousel';
        },
        autoplay() {
            return this.block?.attributes?.payload?.autoplay;
        },
        autoplayTimer() {
            return this.block?.attributes?.payload?.autoplay_timer;
        },
        nav() {
            return this.block?.attributes?.payload?.nav;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        cols() {
            return this.block?.attributes?.payload?.cols;
        },
        showFileNames() {
            return this.block?.attributes?.payload?.show_filenames;
        },
        showFileDescription() {
            return this.block?.attributes?.payload?.show_description ?? 'false';
        },
        mouseoverFileNames() {
            return this.block?.attributes?.payload?.mouseover_filenames ?? 'false';
        },
        files() {
            if (!this.editMode) {
                return this.block?.attributes?.payload?.files;
            }
            return this.editModeFiles;
        },
        gridWidth() {
            return 'calc(' + this.currentCols + '% - 8px)';
        },
        showDescription() {
            return this.currentShowFileNames === 'true' || this.currentShowFileDescription === 'true';
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentFolderId = this.folderId;
            this.currentLayout = this.layout;
            this.currentAutoplay = this.autoplay;
            this.currentAutoplayTimer = this.autoplayTimer;
            this.currentNav = this.nav;
            this.currentHeight = this.height;
            this.currentCols = this.cols;
            this.currentShowFileNames = this.showFileNames;
            this.currentShowFileDescription = this.showFileDescription;
            this.currentMouseoverFileNames = this.mouseoverFileNames;
        },
        startGallery() {
            this.slideIndex = 0;
            this.showSlides(0);
            if (this.currentAutoplay === 'true') {
                this.playSlides();
            }
        },
        async getFolderFiles() {
            const parent = { type: 'folders', id: `${this.currentFolderId}` };
            const relationship = 'file-refs';
            const options = { include: 'terms-of-use' };
            await this.loadRelatedFileRefs({ parent, relationship, options });

            const files = this.relatedFileRefs({ parent, relationship });
            this.processFiles(files);
        },
        processFiles(files) {
            this.editModeFiles = files
                .filter((file) => {
                    if (
                        this.relatedTermOfUse({ parent: file, relationship: 'terms-of-use' }).attributes[
                            'download-condition'
                        ] !== 0
                    ) {
                        return false;
                    }
                    if (!file.attributes['mime-type'].includes('image')) {
                        return false;
                    }

                    return true;
                })
                .map((file) => ({
                    id: file.id,
                    attributes: {
                        name: file.attributes.name,
                        description: file.attributes.description,
                    },
                    meta: {
                        'download-url': this.urlHelper.getURL(
                            'sendfile.php',
                            { type: 0, file_id: file.id, file_name: file.attributes.name },
                            true
                        ),
                    },
                }));
        },
        showEdit() {
            this.editMode = true;
            this.initCurrentData();
        },
        closeEdit() {
            this.editMode = false;
            this.initCurrentData();
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.folder_id = this.currentFolderId;
            attributes.payload.layout = this.currentLayout;
            attributes.payload.autoplay = this.currentAutoplay;
            attributes.payload.autoplay_timer = this.currentAutoplayTimer;
            attributes.payload.nav = this.currentNav;
            attributes.payload.height = this.currentHeight;
            attributes.payload.cols = this.currentCols;
            attributes.payload.show_filenames = this.currentShowFileNames;
            attributes.payload.show_description = this.currentShowFileDescription;
            attributes.payload.mouseover_filenames = this.currentMouseoverFileNames;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        plusSlides(n) {
            this.showSlides((this.slideIndex += n));
        },
        showSlides(n) {
            let slides = this.$refs.images;
            if (slides === undefined) {
                return false;
            }
            if (n > slides.length - 1) {
                this.slideIndex = 0;
            }
            if (n < 0) {
                this.slideIndex = slides.length - 1;
            }
            slides.forEach((slide) => {
                slide.style.display = 'none';
            });
            slides[this.slideIndex].style.display = 'block';
        },
        playSlides() {
            let slides = this.$refs.images;
            slides.forEach((slide) => {
                slide.style.display = 'none';
            });
            if (this.slideIndex > slides.length - 1) {
                this.slideIndex = 0;
            }
            if (slides[this.slideIndex]) {
                slides[this.slideIndex].style.display = 'block';
            }
            this.slideIndex++;
            if (this.currentAutoplay === 'true') {
                setTimeout(this.playSlides, this.currentAutoplayTimer * 1000);
            }
        },
    },
    watch: {
        currentFolderId() {
            this.getFolderFiles();
        },
        currentAutoplay(value) {
            if (value === 'false') {
                this.currentNav = 'true';
            }
        },
        currentAutoplayTimer(value) {
            if (value > 60 || value < 1) {
                this.currentAutoplayTimer = '2';
            }
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/gallery.scss';
</style>
