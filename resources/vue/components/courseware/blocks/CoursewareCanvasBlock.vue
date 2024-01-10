<template>
    <div class="cw-block cw-block-canvas" ref="block">
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
                <div v-if="currentTitle" class="cw-block-title">
                    {{ currentTitle }}
                </div>
                <div class="cw-canvasblock-toolbar">
                    <div class="cw-canvasblock-buttonset">
                        <button class="cw-canvasblock-reset" :title="$gettext('Zurücksetzen')" @click="reset"></button>
                        <button class="cw-canvasblock-undo" :title="$gettext('Rückgängig')" @click="undo"></button>
                        <button v-if="hasUploadFolder" class="cw-canvasblock-store" :title="$gettext('Bild im Dateibereich speichern')" @click="store"></button>
                        <button v-if="canSwitchView" :class="viewButtonClass" :title="viewButtonText" @click="switchView"></button>
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            v-for="color in colors"
                            :key="color.name"
                            class="cw-canvasblock-color"
                            :class="[currentColor === color.name ? 'selected-color' : '', color.name]"
                            :title="color.title"
                            @click="setColor(color.name)"
                        />
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-small"
                            :class="{ 'selected-size': currentSize === 2 }"
                            :title="$gettext('klein')"
                            @click="setSize('small')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-normal"
                            :class="{ 'selected-size': currentSize === 5 }"
                            :title="$gettext('normal')"
                            @click="setSize('normal')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-large"
                            :class="{ 'selected-size': currentSize === 8 }"
                            :title="$gettext('groß')"
                            @click="setSize('large')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-huge"
                            :class="{ 'selected-size': currentSize === 12 }"
                            :title="$gettext('riesig')"
                            @click="setSize('huge')"
                        />
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            class="cw-canvasblock-tool cw-canvasblock-tool-pen"
                            :class="{ 'selected-tool': currentTool === 'pen' }"
                            :title="$gettext('Zeichenwerkzeug')"
                            @click="setTool('pen')"
                        />
                        <button
                            class="cw-canvasblock-tool cw-canvasblock-tool-text"
                            :class="{ 'selected-tool': currentTool === 'text' }"
                            :title="$gettext('Textwerkzeug')"
                            @click="setTool('text')"
                        >
                            T
                        </button>
                    </div>
                </div>
                <img :src="currentUrl" class="cw-canvasblock-original-img" ref="image" @load="buildCanvas" />
                <input
                    v-show="textInput"
                    class="cw-canvasblock-text-input"
                    ref="textInputField"
                    @keyup="textInputKeyUp"
                />
                <canvas
                    class="cw-canvasblock-canvas"
                    :class="{
                        'cw-canvasblock-tool-selected-pen': currentTool === 'pen',
                        'cw-canvasblock-tool-selected-text': currentTool === 'text',
                    }"
                    ref="canvas"
                    @mousedown="mouseDown"
                    @mousemove="mouseMove"
                    @mouseup="mouseUp"
                    @mouseout="mouseUp"
                    @mouseleave="mouseUp"

                    @touchstart="touchStart"
                    @touchmove="touchMove"
                    @touchend="touchEnd"
                />
                <div class="cw-canvasblock-hints">
                    <div v-show="write" class="messagebox messagebox_info cw-canvasblock-text-info">
                        {{ $gettext('Texteingabe mit Enter-Taste bestätigen') }}
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <courseware-tabs>
                    <courseware-tab
                        :index="0"
                        :name="$gettext('Grunddaten')"
                        :selected="true"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Überschrift') }}
                                <input type="text" v-model="currentTitle" />
                            </label>
                            <label>
                                {{ $gettext('Hintergrundbild') }}
                                <select v-model="currentImage">
                                    <option value="true">{{ $gettext('Ja') }}</option>
                                    <option value="false">{{ $gettext('Nein') }}</option>
                                </select>
                            </label>
                            <label v-if="currentImage === 'true'">
                                {{ $gettext('Bilddatei') }}
                                <studip-file-chooser
                                    v-model="currentFileId"
                                    selectable="file"
                                    :courseId="studipContext.id"
                                    :userId="userId"
                                    :isImage="true"
                                    :excludedCourseFolderTypes="excludedCourseFolderTypes"
                                />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab
                        :index="1"
                        :name="$gettext('Einstellungen')"
                    >
                        <form class="default" @submit.prevent="">
                            <label>
                                {{ $gettext('Speicherort') }}
                                <courseware-folder-chooser v-model="currentUploadFolderId" :unchoose="true"/>
                            </label>
                            <label>
                                {{ $gettext('Werte anderer Nutzer anzeigen') }}
                                <select v-model="currentShowUserData">
                                    <option value="off">{{ $gettext('deaktiviert') }}</option>
                                    <option value="teacher">{{ $gettext('nur für Lehrende') }}</option>
                                    <option value="all">{{ $gettext('für alle') }}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Leinwand-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-canvas-block',
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
            currentImage: '',
            currentFileId: '',
            currentUploadFolderId: '',
            currentShowUserData: '',
            currentUserView: 'own',
            currentFile: {},

            context: {},
            paint: false,
            write: false,
            clickX: [],
            clickY: [],
            clickDrag: [],
            clickColor: [],
            colors: [
                {rgba: 'rgba(255,255,255,1)', title: this.$gettext('weiß'), name: 'white'},
                {rgba: 'rgba(52,152,219,1)', title: this.$gettext('blau'), name: 'blue'},
                {rgba: 'rgba(46,204,113,1)', title: this.$gettext('grün'), name: 'green'},
                {rgba: 'rgba(155,89,182,1)', title: this.$gettext('lila'), name: 'purple'},
                {rgba: 'rgba(231,76,60,1)', title: this.$gettext('rot'), name: 'red'},
                {rgba: 'rgba(254,211,48,1)', title: this.$gettext('gelb'), name: 'yellow'},
                {rgba: 'rgba(243,156,18,1)', title: this.$gettext('orange'), name: 'orange'},
                {rgba: 'rgba(149,165,166,1)', title: this.$gettext('grau'), name: 'grey'},
                {rgba: 'rgba(52,73,94,1)', title: this.$gettext('dunkel grau'), name: 'darkgrey'},
                {rgba: 'rgba(0,0,0,1)', title: this.$gettext('schwarz'), name: 'black'},
            ],
            currentColor: '',
            currentColorRGBA: '',
            sizes: { small: 2, normal: 5, large: 8, huge: 12 },
            clickSize: [],
            currentSize: '',
            tools: { pen: 'pen', text: 'text' },
            currentTool: '',
            clickTool: [],
            Text: [],
            textInput: false,
            file: null
        };
    },
    computed: {
        ...mapGetters({
            studipContext: 'context',
            fileRefById: 'file-refs/byId',
            getUserDataById: 'courseware-user-data-fields/byId',
            relatedUserData: 'user-data-field/related',
            usersById: 'users/byId',
        }),
        userData() {
            return this.getUserDataById({ id: this.block.relationships['user-data-field'].data.id });
        },
        canvasDraw() {
            if (this.userData !== undefined && this.userData.attributes.payload.canvas_draw) {
                return this.userData.attributes.payload.canvas_draw;
            } else {
                return false;
            }
        },
        allCanvasDraws() {
            const parent = { type: 'courseware-blocks', id: this.block.id };
            const relationship = 'user-data-field';
            const userDataFields = this.relatedUserData({
                parent: parent,
                relationship: relationship,
            });
            let canvasDraws = [];
            if (userDataFields?.length > 0) {
                for (let userDataField of userDataFields) {
                    // extracting the canvas draws of the other users.
                    if (userDataField?.attributes?.payload?.canvas_draw &&
                        userDataField?.relationships?.user?.data?.id !== this.userId ) {
                        let canvas_draw = userDataField.attributes.payload.canvas_draw;
                        let draw_obj = {
                            clickX: JSON.parse(canvas_draw.clickX),
                            clickY: JSON.parse(canvas_draw.clickY),
                            clickDrag: JSON.parse(canvas_draw.clickDrag),
                            clickColor: JSON.parse(canvas_draw.clickColor),
                            clickSize: JSON.parse(canvas_draw.clickSize),
                            clickTool: JSON.parse(canvas_draw.clickTool),
                            Text: JSON.parse(canvas_draw.Text),
                        };
                        canvasDraws.push(draw_obj);
                    }
                }
            }
            return canvasDraws;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        image() {
            return this.block?.attributes?.payload?.image;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        uploadFolderId() {
            return this.block?.attributes?.payload?.upload_folder_id;
        },
        showUsersData() {
            return this.block?.attributes?.payload?.show_usersdata;
        },
        currentUrl() {
            if (this.currentFile?.meta) {
                return this.currentFile.meta['download-url'];
            } else if(this.currentFile?.download_url) {
                    return this.currentFile.download_url;
            } else {
                return '';
            }
        },
        currentFileName() {
            if (this.currentFile?.attributes?.name) {
                return this.currentFile.attributes.name;
            } else {
                return this.currentTitle + '.jpg';
            }
        },
        hasUploadFolder() {
            return this.currentUploadFolderId !== "";
        },
        canSwitchView() {
            // this feature is not something to offer in the Arbeitsplatz!
            let context = this.$store.getters.context;
            if (context.type !== 'courses') {
                return false;
            }
            if (this.currentShowUserData === 'off') {
                return false;
            }
            if (this.currentShowUserData === 'teacher' && !this.isTeacher) {
                return false;
            }
            return true;
        },
        viewButtonText() {
            let text = this.$gettext('Werte anderer Nutzer anzeigen');
            if (this.currentUserView == 'own') {
                text = this.$gettext('Nur eigene Werte anzeigen');
            }
            return text;
        },
        viewButtonClass() {
            return 'cw-canvasblock-show-' + this.currentUserView;
        }
    },
    mounted() {
        if (this.block.id) {
            this.loadFileRefs(this.block.id).then((response) => {
                this.file = response[0];
                this.currentFile = this.file;
                this.initCurrentData();
                this.buildCanvas();
            });
            this.loadImageFile();
        }
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRefs: 'loadFileRefs',
            createFile: 'createFile',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
            updateUserDataFields: 'courseware-user-data-fields/update',
            loadUserDataFields: 'loadUserDataFields',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentImage = this.image;
            this.currentFileId = this.fileId;
            this.currentUploadFolderId = this.uploadFolderId;
            this.currentShowUserData = this.showUsersData;
            if (this.canvasDraw) {
                this.clickX = JSON.parse(this.canvasDraw.clickX);
                this.clickY = JSON.parse(this.canvasDraw.clickY);
                this.clickDrag = JSON.parse(this.canvasDraw.clickDrag);
                this.clickColor = JSON.parse(this.canvasDraw.clickColor);
                this.clickSize = JSON.parse(this.canvasDraw.clickSize);
                this.clickTool = JSON.parse(this.canvasDraw.clickTool);
                this.Text = JSON.parse(this.canvasDraw.Text);
            }
        },
        loadImageFile() {
            this.loadFileRefs(this.block.id).then((response) => {
                this.file = response[0];
                this.currentFile = this.file;
                this.initCurrentData();
                this.buildCanvas();
            });
        },
        setColor(color) {
            if (this.write) {
                return;
            }
            this.currentColor = color;
            this.currentColorRGBA = this.colors.find(c => c.name === color).rgba;
        },
        setSize(size) {
            if (this.textInput) {
                return;
            }
            this.currentSize = this.sizes[size];
        },
        setTool(tool) {
            if (this.write) {
                this.clickX.pop();
                this.clickY.pop();
                this.clickDrag.pop();
                this.clickColor.pop();
                this.clickSize.pop();
                this.clickTool.pop();
                this.write = false;
                this.textInput = false;
            }
            this.currentTool = this.tools[tool];
        },
        reset() {
            this.clickX.length = 0;
            this.clickY.length = 0;
            this.clickDrag.length = 0;
            this.clickColor.length = 0;
            this.clickSize.length = 0;
            this.clickTool.length = 0;
            this.Text.length = 0;
            this.paint = false;
            this.write = false;
            this.textInput = false;
            this.redraw();
        },
        buildCanvas() {
            let blockElem = this.$refs.block;
            let image = this.$refs.image;
            let canvas = this.$refs.canvas;
            canvas.width = blockElem.offsetWidth - 2;
            if (this.currentImage === 'true' && image.height > 0) {
                canvas.height = Math.round((canvas.width / image.width) * image.height);
            } else {
                canvas.height = 500;
            }
            this.context = canvas.getContext('2d');
            this.setColor('blue');
            this.currentSize = this.sizes['normal'];
            this.currentTool = this.tools['pen'];
            this.redraw();
        },
        redraw() {
            let view = this;
            let context = view.context;
            context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, context.canvas.width, context.canvas.height); // set background
            if (view.currentImage === 'true') {
                let outlineImage = new Image();
                outlineImage.src = this.currentUrl;
                context.drawImage(outlineImage, 0, 0, context.canvas.width, context.canvas.height);
            }

            context.lineJoin = 'round';
            let ownCanvasDraw = {
                clickX: view.clickX,
                clickY: view.clickY,
                clickDrag: view.clickDrag,
                clickColor: view.clickColor,
                clickSize: view.clickSize,
                clickTool: view.clickTool,
                Text: view.Text
            }
            let canvasDraws = [ownCanvasDraw];
            if (this.currentUserView === 'all') {
                canvasDraws = [ ...canvasDraws, ...view.allCanvasDraws ];
            }

            for (let draw of canvasDraws) {
                for (var j = 0; j < draw.clickX.length; j++) {
                    if (draw.clickTool[j] === 'pen') {
                        context.beginPath();
                        if (draw.clickDrag[j] && j) {
                            context.moveTo(draw.clickX[j - 1], draw.clickY[j - 1]);
                        } else {
                            context.moveTo(draw.clickX[j] - 1, draw.clickY[j]);
                        }
                        context.lineTo(draw.clickX[j], draw.clickY[j]);
                        context.closePath();
                        context.strokeStyle = draw.clickColor[j];
                        context.lineWidth = draw.clickSize[j];
                        context.stroke();
                    }
                    if (draw.clickTool[j] === 'text') {
                        let fontsize = draw.clickSize[j] * 6;
                        context.font = fontsize + 'px Arial ';
                        context.fillStyle = draw.clickColor[j];
                        context.fillText(draw.Text[j], draw.clickX[j], draw.clickY[j] + fontsize);
                    }
                }
            }
        },
        mouseDown(e) {
            if (this.write) {
                let view = this;
                this.$refs.textInputField.focus();
                window.setTimeout(function () {
                    view.$refs.textInputField.focus();
                }, 0);
                return;
            }
            if (this.currentTool === 'pen') {
                this.paint = true;
                this.addClick(e.offsetX, e.offsetY, false);
                this.redraw();
            }
            if (this.currentTool === 'text') {
                this.write = true;
                this.addClick(e.offsetX, e.offsetY, false);
            }
        },
        mouseMove(e) {
            if (this.paint) {
                this.addClick(e.offsetX, e.offsetY, true);
                this.redraw();
            }
        },
        mouseUp(e) {
            this.storeDraw();
            this.paint = false;
        },
        touchStart(e) {
            e.preventDefault();
            if (this.write) {
                return;
            }
            let canvas = this.$refs.canvas;
            let mousePos = this.getTouchPos(canvas, e);
            if(this.currentTool == 'pen') {
                this.paint = true;
                this.addClick(mousePos.x, mousePos.y, false);
                this.redraw();
            }
            if(this.currentTool == 'text') {
                this.write = true;
                this.addClick(mousePos.x, mousePos.y, false);
            }
        },
        touchMove(e) {
            e.preventDefault();

            let canvas = this.$refs.canvas;
            let mousePos = this.getTouchPos(canvas, e);
            if(this.paint){
                this.addClick(mousePos.x, mousePos.y, true);
                this.redraw();
            }
        },
        touchEnd(e) {
            this.storeDraw();
            this.paint = false;
        },
        getTouchPos(canvasDom, touchEvent) {
            var rect = canvasDom.getBoundingClientRect();
            return {
                x: touchEvent.touches[0].clientX - rect.left,
                y: touchEvent.touches[0].clientY - rect.top
            };
        },
        addClick(x, y, dragging) {
            this.clickX.push(x);
            this.clickY.push(y);
            this.clickDrag.push(dragging);
            this.clickColor.push(this.currentColorRGBA);
            this.clickSize.push(this.currentSize);
            this.clickTool.push(this.currentTool);
            if (this.currentTool === 'text') {
                this.enableTextInput(x, y);
            } else {
                this.Text.push('');
            }
        },
        undo() {
            let dragging = this.clickDrag[this.clickDrag.length - 1];
            this.clickX.pop();
            this.clickY.pop();
            this.clickDrag.pop();
            this.clickColor.pop();
            this.clickSize.pop();
            this.clickTool.pop();
            if (this.write) {
                this.textInput = false;
                this.write = false;
            } else {
                this.Text.pop('');
            }
            if (dragging) {
                this.undo();
            }
            this.redraw();
        },
        enableTextInput(x, y) {
            let view = this;
            let fontsize = this.currentSize * 6;
            this.textInput = true;
            let input = this.$refs.textInputField;
            input.value = '';
            input.style.position = 'absolute';
            input.style.top = this.$refs.canvas.offsetTop + y + 'px';
            input.style.left = 320 + x + 'px';
            input.style.lineHeight = fontsize + 'px';
            input.style.fontSize = fontsize + 'px';
            input.style.width = '300px';
            window.setTimeout(function () {
                view.$refs.textInputField.focus();
            }, 0);
        },
        textInputKeyUp(e) {
            if (e.defaultPrevented) {
                return;
            }
            let key = e.key || e.keyCode;
            if (key === 'Enter' || key === 13) {
                this.Text.push(this.$refs.textInputField.value);
                this.textInput = false;
                this.write = false;
                this.redraw();
            }
            if (key === 'Escape' || key === 'Esc' || key === 27) {
                this.clickX.pop();
                this.clickY.pop();
                this.clickDrag.pop();
                this.clickColor.pop();
                this.clickSize.pop();
                this.clickTool.pop();
                this.textInput = false;
                this.write = false;
            }
        },
        async storeDraw() {
            let data = {};
            data.type = 'courseware-user-data-fields';
            data.id = this.block.relationships['user-data-field'].data.id;
            data.relationships = {};
            data.relationships.block = {};
            data.relationships.block.data = {};
            data.relationships.block.data.id = this.block.id;
            data.relationships.block.data.type = this.block.type;
            data.attributes = {};
            data.attributes.payload = {};
            data.attributes.payload.canvas_draw = {};
            data.attributes.payload.canvas_draw.clickX = JSON.stringify(this.clickX);
            data.attributes.payload.canvas_draw.clickY = JSON.stringify(this.clickY);
            data.attributes.payload.canvas_draw.clickDrag = JSON.stringify(this.clickDrag);
            data.attributes.payload.canvas_draw.clickColor = JSON.stringify(this.clickColor);
            data.attributes.payload.canvas_draw.clickSize = JSON.stringify(this.clickSize);
            data.attributes.payload.canvas_draw.clickTool = JSON.stringify(this.clickTool);
            data.attributes.payload.canvas_draw.Text = JSON.stringify(this.Text);

            if (data.id) {
                await this.updateUserDataFields(data);
            }
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.image = this.currentImage;
            if (this.currentImage === 'true') {
                attributes.payload.file_id = this.currentFileId;
            } else {
                attributes.payload.file_id = '';
            }
            attributes.payload.upload_folder_id = this.currentUploadFolderId;
            attributes.payload.show_usersdata = this.currentShowUserData;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        async store() {
            let user = this.usersById({id: this.userId});
            let imageBase64 = this.context.canvas.toDataURL("image/jpeg", 1.0);
            let image = await fetch(imageBase64);
            let imageBlob = await image.blob();
            let file = {};
            file.attributes = {};
            if(this.currentImage === 'true') {
                file.attributes.name = (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '_' + this.currentFile.attributes.name;
            } else {
                file.attributes.name = (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '_' + this.block.attributes.title + '_' + this.block.id;
            }

            let img = await this.createFile({
                file: file,
                filedata: imageBlob,
                folder: {id: this.currentUploadFolderId}
            });

            if(img && img.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Das Bild wurde erfolgreich im Dateibereich abgelegt.')
                });
            } else {
                this.companionError({
                    info: this.$gettext('Es ist ein Fehler aufgetretten! Das Bild konnte nicht gespeichert werden.')
                });
            }
        },
        async switchView() {
            if (['own', 'all'].includes(this.currentUserView)) {
                let newView = 'own';
                if (this.currentUserView === 'own') {
                    // we will get the latest draws by loading them each time the view is going to be switched to all!
                    await this.loadUserDataFields(this.block.id).then(() => newView = 'all');
                }
                this.currentUserView = newView;
            }
        }
    },
    watch: {
        currentUserView() {
            this.redraw();
        },
        currentFileId(newId) {
            if (newId) {
                this.currentFile = this.fileRefById({ id: newId });
                this.buildCanvas();
            }
        }
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/canvas.scss';
</style>