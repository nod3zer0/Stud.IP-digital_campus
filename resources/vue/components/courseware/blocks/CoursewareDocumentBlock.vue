<template>
    <div class="cw-block cw-block-document">
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
                <div class="cw-pdf-main-container">
                    <template v-if="hasFile">
                        <div v-if="currentTitle !== ''" class="cw-block-title">
                            {{ currentTitle }}
                        </div>
                        <div class="cw-pdf-toolbar">
                            <div class="cw-pdf-toolbar-left">
                                <div class="cw-pdf-toc">
                                    <button
                                        class="undecorated"
                                        :class="{ active: pdfTOCDisplay }"
                                        :title="$gettext('Inhaltsverzeichnis')"
                                        :aria-pressed="pdfTOCDisplay ? 'true' : 'false'"
                                        @click="toggleTOCViewer"
                                    >
                                        <studip-icon
                                            shape="table-of-contents"
                                            :role="
                                                pdfTOC.length === 0
                                                    ? 'inactive'
                                                    : pdfTOCDisplay
                                                    ? 'info_alt'
                                                    : 'clickable'
                                            "
                                            :size="18"
                                            class="text-bottom"
                                        />
                                    </button>
                                </div>
                                <div class="cw-pdf-search-toggle-btn">
                                    <button
                                        class="undecorated"
                                        :class="{ active: showPdfSearchBox }"
                                        :title="$gettext('Suche')"
                                        :aria-pressed="showPdfSearchBox ? 'true' : 'false'"
                                        @click="togglePdfSearchBox"
                                    >
                                        <studip-icon
                                            shape="search"
                                            :role="showPdfSearchBox ? 'info_alt' : 'clickable'"
                                            :size="18"
                                            class="text-bottom"
                                        />
                                    </button>
                                </div>
                                <div class="cw-pdf-search-box" v-show="showPdfSearchBox">
                                    <input
                                        ref="pdfSearchInput"
                                        type="text"
                                        v-model="pdfSearch"
                                        @change="doSearchInPdf"
                                    />
                                    <div class="cw-pdf-search-navs" v-if="pdfSearchFoundNums > 1">
                                        <button class="undecorated" @click="prevPdfSearch" :title="$gettext('Letzte')">
                                            <studip-icon
                                                shape="arr_1left"
                                                :role="pdfSearchFoundSelectedIndex === 0 ? 'inactive' : 'clickable'"
                                                :size="18"
                                                class="text-bottom"
                                            />
                                        </button>
                                        <button class="undecorated" @click="nextPdfSearch" :title="$gettext('Nächste')">
                                            <studip-icon
                                                shape="arr_1right"
                                                :role="
                                                    pdfSearchFoundSelectedIndex === pdfSearchFoundNums - 1
                                                        ? 'inactive'
                                                        : 'clickable'
                                                "
                                                :size="18"
                                                class="text-bottom"
                                            />
                                        </button>
                                    </div>
                                    <span class="cw-pdf-search-num" v-if="pdfSearchFoundNums > 0">
                                        {{ pdfSearchFoundSelectedIndex + 1 }} / {{ pdfSearchFoundNums }}
                                        {{ $gettext('Treffer') }}
                                    </span>
                                </div>
                                <div class="cw-pdf-page-nav">
                                    <button
                                        class="undecorated"
                                        @click="prevPage"
                                        :title="$gettext('Eine Seite zurück')"
                                    >
                                        <studip-icon
                                            shape="arr_1up"
                                            :role="pageNum - 1 === 0 ? 'inactive' : 'clickable'"
                                            :size="18"
                                            class="text-bottom"
                                        />
                                    </button>
                                    <button class="undecorated" @click="nextPage" :title="$gettext('Eine Seite vor')">
                                        <studip-icon
                                            shape="arr_1down"
                                            :role="pageNum === pageCount ? 'inactive' : 'clickable'"
                                            :size="18"
                                            class="text-bottom"
                                        />
                                    </button>
                                    <input
                                        type="text"
                                        ref="pageNumInput"
                                        class="cw-pdf-page-num"
                                        :aria-label="$gettext('Seite')"
                                        :value="pageNum"
                                        @change="updatePageNum"
                                    />
                                    <span> {{ $gettext('von') }} {{ pageCount }} </span>
                                </div>
                            </div>
                            <div class="cw-pdf-toolbar-middle">
                                <div class="cw-pdf-zoom-buttons">
                                    <button class="undecorated" @click="zoomIn" :title="$gettext('Vergrößern')">
                                        <studip-icon shape="add" :size="18" class="text-bottom" />
                                    </button>
                                    <button class="undecorated" @click="zoomOut" :title="$gettext('Verkleinern')">
                                        <studip-icon shape="remove" :size="18" class="text-bottom" />
                                    </button>
                                    <select v-model="currentScale" :aria-label="$gettext('Zoom')" @change="updateZoom">
                                        <option v-show="false" :value="currentScale">{{ formattedZoom }}%</option>
                                        <option v-for="(value, index) in scaleValues" :key="index" :value="value">
                                            {{ value * 100 }}%
                                        </option>
                                    </select>
                                </div>
                                <div class="cw-pdf-rotate">
                                    <button class="undecorated" @click="doRotatePdf" :title="$gettext('Drehen')">
                                        <studip-icon shape="rotate-right" :size="18" class="text-bottom" />
                                    </button>
                                </div>
                            </div>
                            <div class="cw-pdf-toolbar-right">
                                <div class="cw-pdf-handtool">
                                    <button
                                        class="undecorated"
                                        :class="{ active: pdfHandTool }"
                                        :title="$gettext('Hand-Werkzeug')"
                                        :aria-pressed="pdfHandTool ? 'true' : 'false'"
                                        @click="toggleHandTool"
                                    >
                                        <studip-icon
                                            shape="hand"
                                            :role="pdfHandTool ? 'info_alt' : 'clickable'"
                                            :size="18"
                                            class="text-bottom"
                                        />
                                    </button>
                                </div>
                                <div class="cw-pdf-download">
                                    <a
                                        v-if="downloadable === 'true'"
                                        :href="currentUrl"
                                        download
                                        :title="$gettext('Speichern')"
                                    >
                                        <studip-icon shape="download" :size="18" class="text-bottom" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="cw-pdf-outer-container" ref="outerContainer">
                            <div class="cw-pdf-content">
                                <span class="sr-only" aria-live="polite">{{ srMessage }}</span>
                                <div class="cw-pdf-sidebar" v-show="pdfTOCDisplay">
                                    <ul class="cw-pdf-toc-list">
                                        <CoursewarePDFTableOfContent
                                            v-for="(item, index) in pdfTOC"
                                            :item="item"
                                            :key="index"
                                            @tocPageNav="tocPageNav"
                                        />
                                    </ul>
                                </div>
                                <div
                                    ref="container"
                                    class="cw-pdf-viewer-container"
                                    :class="{
                                        'hand-cursor-grab': pdfHandTool,
                                        grabbing: pdfGrabbing,
                                        'has-error': pdfError,
                                    }"
                                    v-dragscroll="pdfHandTool"
                                    @mousedown="handleMouseDown"
                                    @mouseup="handleMouseUp"
                                >
                                    <div class="pdfViewer" />
                                </div>
                            </div>
                            <div v-show="pdfError" class="cw-pdf-error-page">
                                <courseware-companion-box
                                    mood="sad"
                                    :msgCompanion="$gettext('Es gab einen Fehler. Bitte versuchen Sie es erneut!')"
                                >
                                </courseware-companion-box>
                            </div>
                            <div ref="fakeContainer" class="cw-pdf-viewer-fake-container">
                                <div class="pdfViewer" />
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        {{ $gettext('Datei') }}
                        <studip-file-chooser
                            v-model="currentFileId"
                            selectable="file"
                            :courseId="context.id"
                            :userId="userId"
                            :isDocument="true"
                            :excludedCourseFolderTypes="excludedCourseFolderTypes"
                        />
                    </label>
                    <label>
                        {{ $gettext('Download-Icon anzeigen') }}
                        <select v-model="currentDownloadable">
                            <option value="true">{{ $gettext('Ja') }}</option>
                            <option value="false">{{ $gettext('Nein') }}</option>
                        </select>
                    </label>
                    <label>
                        {{ $gettext('Dateityp') }}
                        <select v-model="currentDocType">
                            <option value="pdf">PDF</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Dokument-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import CoursewarePDFTableOfContent from './CoursewarePDFTableOfContent.vue';
import { getDocument } from 'pdfjs-dist';
import {
    DefaultAnnotationLayerFactory,
    DefaultTextLayerFactory,
    DefaultXfaLayerFactory,
    DefaultStructTreeLayerFactory,
    PDFFindController,
    PDFLinkService,
    PDFPageView,
    PDFViewer,
    EventBus,
} from 'pdfjs-dist/web/pdf_viewer.js';
// pdfjsWorker must be imported!
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.entry';
import { dragscroll } from 'vue-dragscroll';

import { mapActions, mapGetters } from 'vuex';
import 'pdfjs-dist/web/pdf_viewer.css';

export default {
    name: 'courseware-document-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, { CoursewarePDFTableOfContent }),
    directives: {
        dragscroll,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentFileId: '',
            currentFile: {},
            currentDownloadable: '',
            currentDocType: '',

            pdfError: false,
            pdfBasePage: null,
            pdfPage: null,
            pdfTextContent: null,
            pdfHandTool: false,
            pdfGrabbing: false,
            pdfTextLayer: null,
            pdfAnnotationLayer: null,
            pdfAnnotation: false,
            pdfRotate: 0,
            PdfViewer: null,
            pdfEventBus: null,
            pdfLinkService: null,
            pdfFindController: null,
            pdfDoc: null,
            pdfLoadingTask: null,
            pdfSearch: '',
            pdfSearchMatchesMapping: [],
            pdfSearchFoundNums: 0,
            pdfSearchFoundSelectedIndex: 0,
            pdfSearchHighlightedList: [],
            showPdfSearchBox: false,
            pdfTOC: [],
            pdfTOCDisplay: false,
            pageNum: 1,
            pageCount: 0,
            scale: 1,
            currentScale: 1,
            scaleValues: [0.5, 1, 1.5, 2, 3, 4],
            file: null,

            srMessage: '',
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        fileDownloadable() {
            return this.currentDownloadable === 'true';
        },
        downloadable() {
            return this.block?.attributes?.payload?.downloadable ?? 'true';
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        docType() {
            return this.block?.attributes?.payload?.doc_type;
        },
        currentUrl() {
            if (this.currentFile?.meta) {
                return this.currentFile.meta['download-url'];
            } else {
                return '';
            }
        },
        hasFile() {
            return this.currentFileId !== '';
        },
        formattedZoom() {
            return Number.parseInt(this.scale * 100, 10);
        },
    },
    watch: {
        scale(newValue) {
            let overflow = newValue > 1 ? 'auto' : 'hidden';
            let container = this.$refs.container;
            container.style.overflow = overflow;
            this.currentScale = newValue;
        },
        pageNum(newValue) {
            this.resetPdfViewer();
        },
        showPdfSearchBox() {
            this.resetPdfSearch();
        },
        currentFileId(newId) {
            if (newId) {
                this.currentFile = this.fileRefById({ id: newId });
            }
        }
    },
    mounted() {
        if (this.block.id) {
            this.loadFileRefs(this.block.id).then((response) => {
                this.file = response[0];
                this.currentFile = this.file;
                this.initPdfTask();
            });
        }
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRefs: 'loadFileRefs',
            companionWarning: 'companionWarning',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentDownloadable = this.downloadable;
            this.currentFileId = this.fileId;
            this.currentDocType = this.docType;
        },
        initPdfTask() {
            if (this.currentUrl) {
                let view = this;
                view.pdfEventBus = new EventBus();
                view.pdfLoadingTask = getDocument(this.currentUrl).promise;
                view.pdfLoadingTask.__PDFDocumentLoadingTask = true;
                // Link Service
                view.pdfLinkService = new PDFLinkService({
                    eventBus: view.pdfEventBus,
                });
                // Find Controller
                view.pdfFindController = new PDFFindController({
                    eventBus: view.pdfEventBus,
                    linkService: view.pdfLinkService,
                });
                // Annotation Layer
                view.pdfAnnotationLayer = new DefaultAnnotationLayerFactory();
                // Text Layer
                view.pdfTextLayer = new DefaultTextLayerFactory();
                // Load Pdf Document
                view.loadPdfDocument();

                // Handle search results.
                view.pdfEventBus.on('updatetextlayermatches', ({ source, pageIndex }) => {
                    if (view.pdfViewer.pdfPage._pageIndex == pageIndex) {
                        setTimeout(() => {
                            view.handleSearchMatches();
                        }, 260);
                    }
                });
            }
        },
        loadPdfDocument() {
            if (this.pdfLoadingTask) {
                let view = this;
                view.pdfLoadingTask.then((pdfDocument) => {
                    view.pdfDoc = pdfDocument;
                    view.pageCount = pdfDocument.numPages;
                    // get table of contents if any.
                    view.loadPdfTOC();
                    // Rendering PDF viewer
                    view.loadPdfViewer();
                    view.pdfLinkService.setDocument(view.pdfDoc, null);
                    view.pdfFindController.setDocument(view.pdfDoc);
                });
            }
        },
        loadPdfTOC() {
            if (this.pdfDoc) {
                let view = this;
                view.pdfTOC = [];
                // Get the tree outline
                view.pdfDoc.getOutline().then((outline) => {
                    if (outline) {
                        view.pdfTOC = outline;
                    }
                });
            }
        },
        loadPdfViewer() {
            if (this.pdfDoc) {
                let view = this;
                this.pdfError = false;
                let container = this.$refs.container;
                let outerContainer = this.$refs.outerContainer;
                let fakeContainer = this.$refs.fakeContainer;
                this.pdfDoc
                    .getPage(parseInt(view.pageNum))
                    .then((pdfPage) => {
                        view.pdfPage = pdfPage;
                        // Creating the page view with default parameters.
                        let defaultViewport = pdfPage.getViewport({
                            scale: 1.35,
                        });

                        view.pdfBasePage = new PDFViewer({
                            container: fakeContainer,
                            eventBus: view.pdfEventBus,
                            findController: view.pdfFindController,
                        });

                        let pdfPageViewOptions = {
                            container: container,
                            id: view.pageNum,
                            scale: view.scale,
                            defaultViewport: defaultViewport,
                            eventBus: view.pdfEventBus,
                            findController: view.pdfFindController,
                            textHighlighterFactory: view.pdfBasePage,
                            xfaLayerFactory: view.pdfDoc.isPureXfa ? new DefaultXfaLayerFactory() : null,
                            structTreeLayerFactory: new DefaultStructTreeLayerFactory(),
                        };
                        if (view.pdfHandTool === false) {
                            pdfPageViewOptions.textLayerFactory = view.pdfTextLayer;
                            pdfPageViewOptions.annotationLayerFactory = view.pdfAnnotationLayer;
                        } else {
                            pdfPageViewOptions.textLayerMode = 0;
                            pdfPageViewOptions.annotationMode = 0;
                        }
                        // Force annotation to be disabled.
                        if (!this.pdfAnnotation && pdfPageViewOptions?.annotationLayerFactory) {
                            pdfPageViewOptions.annotationLayerFactory = null;
                            pdfPageViewOptions.annotationMode = 0;
                        }
                        view.pdfViewer = new PDFPageView(pdfPageViewOptions);
                        // Associates the actual page with the view, and drawing it
                        view.pdfViewer.setPdfPage(view.pdfPage);
                        // Set LinkService viewer
                        view.pdfLinkService.setViewer(view.pdfViewer);
                        // Set outer container height
                        outerContainer.style.height = container.offsetHeight + 'px';
                        view.renderPage();
                    })
                    .catch((err) => {
                        console.log(err);
                        outerContainer.style.minHeight = '350px';
                        view.pdfError = true;
                    });
            }
        },
        renderPage() {
            if (this.pdfViewer) {
                this.updatePdfViewer();
                this.pdfViewer.draw();
                if (!this.pdfHandTool) {
                    this.pdfViewer.textLayer.findController = this.pdfFindController;
                }
                if (this.pdfPage) {
                    this.pdfPage.getTextContent().then((textContent) => {
                        this.pdfTextContent = textContent;
                    });
                }
                if (this.pdfSearchMatchesMapping.length) {
                    this.pdfSearchDisplayHandler();
                }
            }
        },
        resetPdfViewer() {
            this.pdfViewer.destroy();
            let container = this.$refs.container;
            while (!container.lastChild.classList.contains('pdfViewer')) {
                container.removeChild(container.lastChild);
            }
            this.loadPdfViewer();
        },
        updatePdfViewer(resetScale = false) {
            let updateArgs = {
                scale: resetScale ? 1 : this.scale,
                rotation: this.pdfRotate,
            };
            this.pdfViewer.update(updateArgs);
        },
        prevPage() {
            if (this.pageNum <= 1) {
                return;
            }
            this.pageNum--;
        },
        nextPage() {
            if (this.pageNum >= this.pdfDoc.numPages) {
                return;
            }
            this.pageNum++;
        },
        goToPage(page) {
            const pageNum = Number.parseInt(page, 10);
            if (pageNum < 1 || pageNum > this.pdfDoc.numPages) {
                return;
            }
            this.pageNum = pageNum;
        },
        tocPageNav(dest) {
            let view = this;
            let destObj = dest.find(
                (ref) =>
                    typeof ref === 'object' &&
                    ref !== null &&
                    Number.isInteger(ref.num) &&
                    ref.num >= 0 &&
                    Number.isInteger(ref.gen) &&
                    ref.gen >= 0
            );
            if (destObj) {
                view.pdfDoc.getPageIndex(destObj).then((pageIndex) => {
                    view.goToPage(pageIndex + 1);
                });
            }
        },
        updatePageNum() {
            let pageNumInput = this.$refs.pageNumInput;
            let value = Number.parseInt(pageNumInput.value, 10);
            if (Number.isInteger(value) && value > 0 && value <= Number.parseInt(this.pageCount, 10)) {
                this.pageNum = value;
            } else {
                pageNumInput.value = this.pageNum;
            }
        },
        doRotatePdf() {
            let rotationDegs = [0, 90, 180, 270, 360];
            let index = rotationDegs.indexOf(this.pdfRotate);
            let nextIndex = index + 1 >= rotationDegs.length ? 0 : index + 1;
            let nextDeg = rotationDegs[nextIndex];
            this.pdfRotate = nextDeg;
            this.renderPage();
            this.updateSrMessage(this.$gettext('gedreht'));
        },
        zoomIn() {
            this.scale = this.scale < 4 ? (this.scale * 10 + 1) / 10 : this.scale;
            this.renderPage();
            this.updateSrMessage(this.$gettext('vergrößert'));
        },
        zoomOut() {
            this.scale = this.scale > 0.1 ? (this.scale * 10 - 1) / 10 : this.scale;
            this.renderPage();
            this.updateSrMessage(this.$gettext('verkleinert'));
        },
        updateZoom(e) {
            const value = e.target.value;
            if (this.scale === value) {
                return;
            }
            this.scale = value;
            this.renderPage();
            this.updateSrMessage(this.$gettext('Zoom Stufe ausgweählt'));
        },
        toggleHandTool() {
            this.pdfHandTool = !this.pdfHandTool;
            this.resetPdfViewer();
            this.showPdfSearchBox = false;
        },
        handleHandToolDisplay(event) {
            this.pdfGrabbing = event.type === 'mousedown';
        },
        handleMouseDown(e) {
            this.handleHandToolDisplay(e);
        },
        handleMouseUp(e) {
            this.handleHandToolDisplay(e);
        },
        togglePdfSearchBox() {
            this.showPdfSearchBox = this.pdfHandTool ? false : !this.showPdfSearchBox;
            if (this.showPdfSearchBox) {
                this.$nextTick(() => {
                    this.$refs.pdfSearchInput.focus();
                });
            }
        },
        handleSearchMatches() {
            let view = this;
            let allMatches = view.pdfFindController.pageMatches;
            let totalMatches = 0;
            let searchSelectIndex = 0;
            let matchesPageCount = 0;
            view.pdfSearchMatchesMapping = [];
            for (let pageIndex = 0; pageIndex < view.pageCount; pageIndex++) {
                let pageNum = pageIndex + 1;
                let pageMatches = allMatches[pageIndex];
                totalMatches += pageMatches.length;
                if (pageMatches.length) {
                    matchesPageCount++;
                }
                for (let i in pageMatches) {
                    let matchIndex = parseInt(i, 10);
                    let mappingObj = {
                        selectIndex: searchSelectIndex,
                        matchIndex: matchIndex,
                        pageNum: pageNum,
                    };
                    view.pdfSearchMatchesMapping.push(mappingObj);
                    searchSelectIndex++;
                }
            }
            // Find next match if there the current page has nothing.
            if (
                view.pdfSearchFoundSelectedIndex === 0 &&
                view.pdfViewer.pdfPage._pageIndex > 0 &&
                matchesPageCount > 0
            ) {
                let nextMapped = view.pdfSearchMatchesMapping.filter(
                    (map) => map.pageNum >= view.pdfViewer.pdfPage._pageIndex + 1
                );
                if (nextMapped.length) {
                    view.pdfSearchFoundSelectedIndex = nextMapped[0].selectIndex;
                }
            }
            view.pdfSearchFoundNums = totalMatches;
            view.pdfSearchDisplayHandler();
        },
        doSearchInPdf() {
            let findObj = {
                type: '',
                query: this.pdfSearch,
                phraseSearch: true,
                caseSensitive: false,
                entireWord: true,
                highlightAll: true,
                findPrevious: false,
                matchDiacritics: false,
            };
            this.pdfEventBus.dispatch('find', findObj);
        },
        prevPdfSearch() {
            if (this.pdfSearchFoundSelectedIndex === 0) {
                return;
            }
            this.pdfSearchFoundSelectedIndex--;
            this.pdfSearchDisplayHandler();
        },
        nextPdfSearch() {
            if (this.pdfSearchFoundSelectedIndex === this.pdfSearchFoundNums - 1) {
                return;
            }
            this.pdfSearchFoundSelectedIndex++;
            this.pdfSearchDisplayHandler();
        },
        pdfSearchDisplayHandler() {
            // Go to page based on selected index.
            let pageMatches = this.pdfSearchMatchesMapping.filter(
                (map) => map.selectIndex === this.pdfSearchFoundSelectedIndex
            );
            if (pageMatches.length) {
                let matchObj = pageMatches[0];
                // A timeout of > 250ms is needed when page is changed!
                let highlightRenderTimeout = 0;
                if (matchObj.pageNum !== this.pageNum) {
                    this.goToPage(matchObj.pageNum);
                    highlightRenderTimeout = 260;
                }
                setTimeout(() => {
                    this.setPdfSearchHighlighted();
                    this.scrollToSearchFounds(matchObj.matchIndex);
                }, highlightRenderTimeout);
            }
        },
        scrollToSearchFounds(matchIndex) {
            if (this.pdfSearchHighlightedList?.length) {
                let selectedSpan = this.pdfSearchHighlightedList[matchIndex];
                if (selectedSpan) {
                    selectedSpan.classList.add('selected');
                    selectedSpan.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        },
        setPdfSearchHighlighted() {
            if (this.pdfViewer?.textLayer?.textDivs) {
                let textDivs = this.pdfViewer.textLayer.textDivs;
                let highlightedSpans = [];
                for (let textSpan of textDivs) {
                    if (textSpan?.children) {
                        let children = [...textSpan.children];
                        for (let child of children) {
                            if (child.nodeName == 'SPAN' && child.classList.contains('highlight')) {
                                child.classList.remove('selected');
                                highlightedSpans.push(child);
                            }
                        }
                    }
                }
                // Sort the array based on the top of the span.
                highlightedSpans.sort((current, next) => {
                    let currentTop = parseInt(current.parentNode.style.top, 10);
                    let nextTop = parseInt(next.parentNode.style.top, 10);
                    return currentTop > nextTop;
                });
                this.pdfSearchHighlightedList = highlightedSpans;
            }
        },
        resetPdfSearch() {
            this.pdfSearch = '';
            this.pdfSearchFoundNums = 0;
            this.pdfSearchFoundSelectedIndex = 0;
            this.pdfSearchHighlightedList = [];
            this.pdfSearchMatchesMapping = [];
            this.doSearchInPdf();
        },
        toggleTOCViewer() {
            if (this.pdfTOC.length) {
                this.pdfTOCDisplay = !this.pdfTOCDisplay;
            } else {
                this.pdfTOCDisplay = false;
            }
        },
        storeBlock() {
            if (this.currentFile === undefined) {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Datei aus.'),
                });
                return false;
            } else {
                let attributes = {};
                attributes.payload = {};
                attributes.payload.title = this.currentTitle;
                attributes.payload.file_id = this.currentFile.id;
                attributes.payload.downloadable = this.currentDownloadable.toString();
                attributes.payload.doc_type = this.currentDocType;

                this.updateBlock({
                    attributes: attributes,
                    blockId: this.block.id,
                    containerId: this.block.relationships.container.data.id,
                });
            }
        },
        updateSrMessage(message) {
            this.srMessage = '';
            this.srMessage = message;
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/document.scss';
</style>