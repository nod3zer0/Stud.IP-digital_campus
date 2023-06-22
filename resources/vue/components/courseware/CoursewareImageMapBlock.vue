<template>
    <div class="cw-block cw-block-image-map" @mousedown="selectShape">
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
                <img :src="currentUrl" class="cw-image-map-original-img" ref="original_img" @load="buildCanvas" />
                <canvas class="cw-image-map-canvas" ref="canvas"></canvas>
                <img
                    class="cw-image-from-canvas"
                    :src="image_from_canvas"
                    ref="image_from_canvas"
                    :usemap="'#' + map_name"
                />
                <map ref="map" :name="map_name">
                    <area
                        v-for="area in areas"
                        :key="area.id"
                        :id="area.id"
                        :shape="area.shape"
                        :coords="area.coords"
                        :title="area.title"
                        :href="area.external_target"
                        :target="area.link_target"
                        @click=" 
                            if (area.target_type === 'internal') {
                                areaLink(area.internal_target);
                            }
                        "
                    />
                </map>
                <div v-if="showEditMode && viewMode === 'edit' && currentShapes.length > 0"
                    ref="draggableShapeWrapper" class="cw-draggable-shapes-wrapper">
                    <vue-resizeable
                            v-for="(shape, index) in currentShapes"
                            :key="index"
                            :index="index"
                            style="position: absolute"
                            ref="resizableAreaComponents"
                            :fitParent="true"
                            :dragSelector="dragSelector"
                            :active="handlers"
                            :left="getShapeOffsetLeft(shape)"
                            :top="getShapeOffsetTop(shape)"
                            :width="getShapeWidth(shape)"
                            :height="getShapeHeight(shape)"
                            @resize:start="dragStartHandler"
                            @resize:end="endDraggingShape"
                            @drag:start="dragStartHandler"
                            @drag:end="endDraggingShape">
                        <div class="cw-draggable-area"
                            :style="{
                                backgroundColor: getColorRGBA(shape.data.color),
                                color: shape.data.textcolor ? getColorRGBA(shape.data.textcolor) : '',
                                borderRadius: getShapeBorderRadius(shape),
                                border: getShapeBorder(shape),
                                cursor: selectedShapeIndex !== false ? 'grabbing' : '',
                            }"
                            @click="followLink(index)">
                            {{ shape.data.text }}
                        </div>
                    </vue-resizeable>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Bilddatei') }}
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isImage="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label>
                        <a class="button add" @click="addShape('arc')">{{ $gettext('Kreis hinzufügen') }}</a>
                        <a class="button add" @click="addShape('ellipse')">{{ $gettext('Oval hinzufügen') }}</a>
                        <a class="button add" @click="addShape('rect')">{{ $gettext('Rechteck hinzufügen') }}</a>
                    </label>
                    <courseware-tabs v-if="currentShapes.length > 0">
                        <courseware-tab
                            v-for="(shape, index) in currentShapes"
                            :key="index"
                            :index="index"
                            :name="shape.title ? shape.title : ''"
                            :icon="shape.title === '' ? 'link-extern' : ''"
                            :selected="index === 0"
                        >
                            <label class="col-2">
                                {{ $gettext('Farbe') }}
                                <studip-select
                                    :options="colors"
                                    label="name"
                                    :reduce="color => color.class"
                                    :clearable="false"
                                    v-model="shape.data.color"
                                    @input="drawScreen"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label class="col-2">
                                {{ $gettext('Textfarbe') }}
                                <studip-select
                                    :options="colors"
                                    label="name"
                                    :reduce="color => color.class"
                                    :clearable="false"
                                    v-model="shape.data.textcolor"
                                    @input="drawScreen"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <br>
                            <template v-if="shape.type === 'arc'">
                                <label class="col-1">
                                    X
                                    <input type="number" v-model="shape.data.centerX" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    Y
                                    <input type="number" v-model="shape.data.centerY" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    {{ $gettext('Radius') }}
                                    <input type="number" v-model="shape.data.radius" @change="drawScreen" />
                                </label>
                            </template>
                            <template v-if="shape.type === 'rect'">
                                <label class="col-1">
                                    X
                                    <input type="number" v-model="shape.data.X" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    Y
                                    <input type="number" v-model="shape.data.Y" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    {{ $gettext('Höhe') }}
                                    <input type="number" v-model="shape.data.height" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    {{ $gettext('Breite') }}
                                    <input type="number" v-model="shape.data.width" @change="drawScreen" />
                                </label>
                            </template>
                            <template v-if="shape.type === 'ellipse'">
                                <label class="col-1">
                                    X
                                    <input type="number" v-model="shape.data.X" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    Y
                                    <input type="number" v-model="shape.data.Y" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    {{ $gettext('Radius') }} X
                                    <input type="number" v-model="shape.data.radiusX" @change="drawScreen" />
                                </label>
                                <label class="col-1">
                                    {{ $gettext('Radius') }} Y
                                    <input type="number" v-model="shape.data.radiusY" @change="drawScreen" />
                                </label>
                            </template>
                            <label class="col-4">
                                {{ $gettext('Bezeichnung') }}
                                <input type="text" v-model="shape.title" />
                            </label>
                            <label class="col-4">
                                {{ $gettext('Beschriftung') }}
                                <input type="text" v-model="shape.data.text" @change="drawScreen" />
                            </label>
                            <br>
                            <label class="col-2">
                                {{ $gettext('Art des Links') }}
                                <select v-model="shape.link_type">
                                    <option value="internal">{{ $gettext('Interner Link') }}</option>
                                    <option value="external">{{ $gettext('Externer Link') }}</option>
                                </select>
                            </label>
                            <label v-if="shape.link_type === 'internal'" class="col-2">
                                {{ $gettext('Ziel des Links') }}
                                <select v-model="shape.target_internal" @change="drawScreen">
                                    <option v-for="(el, index) in courseware" :key="index" :value="el.id">
                                        {{ el.attributes.title }}
                                    </option>
                                </select>
                            </label>
                            <label v-if="shape.link_type === 'external'" class="col-2">
                                {{ $gettext('Ziel des Links') }}
                                <input
                                    type="text"
                                    placeholder="https://www.studip.de"
                                    v-model="shape.target_external"
                                    @change="
                                        drawScreen();
                                        fixUrl(index);
                                    "
                                />
                            </label>
                            <label>
                                <a class="button cancel" @click="removeShape(index)"
                                    >{{ $gettext('Form entfernen') }}</a
                                >
                            </label>
                        </courseware-tab>
                    </courseware-tabs>
                </form>
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Verweissensitive-Grafik-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import VueResizeable from 'vrp-vue-resizable';
import { blockMixin } from './block-mixin.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-image-map-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareTabs,
        CoursewareTab,
        VueResizeable,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentFileId: '',
            currentFile: {},
            currentShapes: {},
            context: {},
            image_from_canvas: '',
            map_name: '',
            areas: [],
            darkColors: ['black', 'darkgrey', 'purple'],
            colors: [
                { name: this.$gettext('Transparent'), class: 'transparent', rgba: 'rgba(0,0,0,0)' },
                { name: this.$gettext('Weiß'), class: 'white', rgba: 'rgba(255,255,255,1)' },
                { name: this.$gettext('Blau'), class: 'blue', rgba: 'rgba(52,152,219,1)' },
                { name: this.$gettext('Grün'), class: 'green', rgba: 'rgba(46,204,113,1)' },
                { name: this.$gettext('Lila'), class: 'purple', rgba: 'rgba(155,89,182,1)' },
                { name: this.$gettext('Rot'), class: 'red', rgba: 'rgba(231,76,60,1)' },
                { name: this.$gettext('Gelb'), class: 'yellow', rgba: 'rgba(254,211,48,1)' },
                { name: this.$gettext('Orange'), class: 'orange', rgba: 'rgba(243,156,18,1)' },
                { name: this.$gettext('Grau'), class: 'grey', rgba: 'rgba(236, 240, 241,1)' },
                { name: this.$gettext('Dunkelgrau'), class: 'darkgrey', rgba: 'rgba(52,73,94,1)' },
                { name: this.$gettext('Schwarz'), class: 'black', rgba: 'rgba(0,0,0,1)' }
            ],
            file: null,
            dragSelector: ".cw-draggable-area",
            handlers: ["r", "rb", "b", "lb", "l", "lt", "t", "rt"],
            draggedShapeWidth: 50,
            draggedShapeHeight: 50,
            selectedShapeIndex: false,
            draggingActive: false,
            showEditMode: false,
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware-structural-elements/all',
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
            viewMode: 'viewMode',
        }),
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        shapes() {
            return this.block?.attributes?.payload?.shapes;
        },
        currentUrl() {
            if (this.currentFile.download_url !== 'undefined') {
                return this.currentFile.download_url;
            } else {
                return '';
            }
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRef: 'file-refs/loadById',
        }),
        async initCurrentData(event) {
            this.showEditMode = Boolean(event);
            this.currentFileId = this.fileId;
            this.currentShapes = JSON.parse(JSON.stringify(this.shapes));
            await this.loadFile();
            this.buildCanvas();
        },
        async loadFile() {
            const id = this.currentFileId;
            await this.loadFileRef({ id });
            const fileRef = this.fileRefById({ id });

            if (fileRef) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
                    download_url: this.urlHelper.getURL(
                        'sendfile.php',
                        { type: 0, file_id: fileRef.id, file_name: fileRef.attributes.name },
                        true
                    ),
                });
            }
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.file_id = this.currentFileId;
            attributes.payload.shapes = this.currentShapes;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },

        buildCanvas() {
            let canvas = this.$refs.canvas;
            let original_img = this.$refs.original_img;
            canvas.width = 1085;
            if (original_img.height > 0) {
                canvas.height = Math.round((canvas.width / original_img.width) * original_img.height);
            } else {
                canvas.height = 484;
            }
            this.context = canvas.getContext('2d');
            this.drawScreen();
        },
        drawScreen() {
            let context = this.context;
            let view = this;
            let outlineImage = new Image();
            outlineImage.src = this.currentUrl;
            outlineImage.onload = function () {
                context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, context.canvas.width, context.canvas.height); // set background
                if (outlineImage.src !== '') {
                    context.drawImage(outlineImage, 0, 0, context.canvas.width, context.canvas.height);
                }
                view.drawShapes();

                if (!(view.$refs.canvas.length > 0)) {
                    view.image_from_canvas = view.context.canvas.toDataURL('image/jpeg', 1.0);
                    view.mapImage();
                }
            };
        },
        drawShapes() {
            let context = this.context;
            let view = this;
            this.currentShapes.forEach((value, index) => {
                // skip the selected shape when redrawing so it disappears while dragging the shape
                if (this.selectedShapeIndex !== index) {
                    let shape = value;
                    let text = shape.data.text;
                    let shape_width = 0;
                    let shape_height = 0;
                    let text_X = 0;
                    let text_Y = 0;

                    context.beginPath();
                    switch (shape.type) {
                        case 'arc':
                            shape_width = Math.round((2 * shape.data.radius) / Math.sqrt(2)) * 0.85;
                            shape_height = shape_width / 0.85;
                            text_X = shape.data.centerX;
                            text_Y = shape.data.centerY - shape.data.radius * 0.75;
                            context.arc(shape.data.centerX, shape.data.centerY, shape.data.radius, 0, 2 * Math.PI); // x, y, r, startAngle, endAngle ... Angle in radians!
                            context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                            context.fill();
                            break;
                        case 'ellipse':
                            shape_width = shape.data.radiusX;
                            shape_height = shape.data.radiusY * 1.75;
                            text_X = shape.data.X;
                            text_Y = shape.data.Y - shape.data.radiusY * 0.8;
                            context.ellipse(
                                shape.data.X,
                                shape.data.Y,
                                shape.data.radiusX,
                                shape.data.radiusY,
                                0,
                                0,
                                2 * Math.PI
                            );
                            context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                            context.fill();
                            break;
                        case 'rect':
                            shape_width = shape.data.width;
                            shape_height = shape.data.height;
                            text_X = shape.data.X + shape.data.width / 2;
                            text_Y = shape.data.Y;
                            context.rect(shape.data.X, shape.data.Y, shape.data.width, shape.data.height);
                            context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                            context.fill();
                            break;
                        default:
                            return;
                    }

                    if (text && shape.data.color !== 'transparent') {
                        text = view.fitTextToShape(context, text, shape_width);
                        context.textAlign = 'center';
                        context.font = '14px Arial';
                        if (shape.data.textcolor) {
                            context.fillStyle = this.getColorRGBA(shape.data.textcolor);
                        } else {
                            if (view.darkColors.indexOf(shape.data.color) > -1) {
                                context.fillStyle = '#ffffff';
                            } else {
                                context.fillStyle = '#000000';
                            }
                        }
                        let lineHeight = shape_height / (text.length + 1);
                        text.forEach((value, key) => {
                            context.fillText(value, text_X, text_Y + lineHeight * (key + 1));
                        });
                    }

                    context.closePath();
                }
            });
        },
        fitTextToShape( context , text, shapeWidth) {
            shapeWidth = shapeWidth || 0;

            let newText = [];

            if (shapeWidth <= 0) {
                return [text];
            }
            let words = text.split(' ');
            let i = 1;
            while (words.length > 0 && i <= words.length) {
                let word = words.slice(0, i).join(' ');
                let wordWidth = context.measureText(word).width + 2;
                if ( wordWidth > shapeWidth ) {
                    if (i === 1) {
                        i = 2;
                    }
                    newText.push(words.slice(0, i - 1).join(' '));
                    words = words.splice(i - 1);
                    i = 1;
                }
                else {
                    i++;
                }
            }
            if (i > 0) {
                newText.push(words.join(' '));
            }

            return newText;

        },
        mapImage() {
            let view = this;
            // generate map name
            let map_name = 'cw-image-map-' + Math.round(Math.random() * 100);
            this.map_name = map_name;

            // insert areas
            this.areas = [];
            this.currentShapes.forEach((value, key) => {
                let shape = value;
                let area = {};
                let coords = '';
                let x = 0;
                let y = 0;
                area.id = 'shape-' + key;

                switch (shape.type) {
                    case 'arc':
                        area.shape = 'circle';
                        area.coords = shape.data.centerX + ', ' + shape.data.centerY + ', ' + shape.data.radius;
                        break;
                    case 'ellipse':
                        for (let theta = 0; theta < 2 * Math.PI; theta += (2 * Math.PI) / 20) {
                            x = parseInt(shape.data.X) + Math.round(parseInt(shape.data.radiusX) * Math.cos(theta));
                            y = parseInt(shape.data.Y) + Math.round(parseInt(shape.data.radiusY) * Math.sin(theta));
                            coords = coords + x + ',' + y + ',';
                        }
                        area.shape = 'poly';
                        area.coords = coords;
                        break;
                    case 'rect':
                    case 'text':
                        x = parseInt(shape.data.X) + parseInt(shape.data.width);
                        y = parseInt(shape.data.Y) + parseInt(shape.data.height);
                        area.shape = 'rect';
                        area.coords = shape.data.X + ', ' + shape.data.Y + ', ' + x + ', ' + y;
                        break;
                }
                area.title = shape.title;
                shape.link_type === 'external'
                    ? (area.external_target = shape.target_external)
                    : (area.external_target = '#');
                if (shape.link_type === 'internal') {
                    area.internal_target = shape.target_internal;
                } else {
                    area.internal_target = '';
                }
                shape.link_type === 'external' ? (area.link_target = '_blank') : (area.link_target = '_self');
                area.link_type = shape.link_type;
                area.target_type = shape.link_type;
                view.areas.push(area);
            });
        },
        areaLink(target) {
            this.$router.push(target);
        },

        //edit methods
        addShape(addtype) {
            let data = {};
            switch (addtype) {
                case 'arc':
                    data = {
                        centerX: 50,
                        centerY: 50,
                        radius: 50,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
                case 'rect':
                    data = {
                        X: 50,
                        Y: 50,
                        height: 100,
                        width: 50,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
                case 'ellipse':
                    data = {
                        X: 50,
                        Y: 50,
                        radiusX: 50,
                        radiusY: 20,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
            }
            this.currentShapes.push({
                type: addtype,
                data: data,
                title: '',
                link_type: 'external',
                target_internal: '',
                target_external: '',
            });
            this.buildCanvas();
        },
        removeShape(index) {
            this.currentShapes.splice(index, 1);
            this.drawScreen();
        },
        fixUrl(index) {
            let url = this.currentShapes[index].target_external;
            if (url !== '' && url.indexOf('http://') !== 0 && url.indexOf('https://') !== 0) {
                url = 'https://' + url;
            }
            this.currentShapes[index].target_external = url;
        },
        dragStartHandler(data) {
            // redraw screen now that a shape was selected so that it disappears while dragging or resizing
            this.drawScreen();
        },
        selectShape(data) {
            // set current draggable div shape to canvas shape coordinates
            let canvas = this.$refs.image_from_canvas;
            let canvasSpecs = canvas.getBoundingClientRect();
            let mouseX = (data.clientX - canvasSpecs.left) * (canvas.width/canvasSpecs.width);
            let mouseY = (data.clientY - canvasSpecs.top) * (canvas.height/canvasSpecs.height);
            this.currentShapes.forEach((value, key) => {
                let shape = value;
                // if the event target is the draggable area, check for the shape area normally
                // else check if the click was on a resizable area that belongs to a shape since 
                // resizable areas are partly outside the shape
                if (data.target.classList.contains('cw-draggable-area')) {
                    if (this.mouseHit(mouseX, mouseY, shape)) {
                        this.selectedShapeIndex = key;
                    }
                } else {
                    mouseX = data.target.parentElement.offsetLeft;
                    mouseY = data.target.parentElement.offsetTop;
                    if (shape.type == 'arc') {
                        mouseX += shape.data.radius;
                        mouseY += shape.data.radius;
                    }
                    if (shape.type == 'rect') {
                        mouseX += shape.data.width / 2;
                        mouseY += shape.data.height / 2;
                    }
                    if (shape.type == 'ellipse') {
                        mouseX += shape.data.radiusX;
                        mouseY += shape.data.radiusY;
                    }
                    if (this.mouseHit(mouseX, mouseY, shape)) {
                        this.selectedShapeIndex = key;
                    }
                }
            });
        },
        endDraggingShape(data) {
            this.draggingActive = true;
            // transfer div shape data to canvas according to shape
            let shape = this.currentShapes[this.selectedShapeIndex];
            if (shape.type == 'arc') {
                let circle_width = data.width != shape.data.radius * 2? data.width : data.height;
                // if the shape was clicked and not dragged, set the dragging status to false to follow the link
                if (shape.data.centerX == data.left + shape.data.radius || shape.data.centerY == data.top + shape.data.radius) {
                    this.draggingActive = false;
                }
                shape.data.radius = circle_width / 2;
                shape.data.centerX = data.left + shape.data.radius;
                shape.data.centerY = data.top + shape.data.radius;
            }
            if (shape.type == 'rect') {
                if (shape.data.X == data.left || shape.data.Y == data.top) {
                    this.draggingActive = false;
                }
                shape.data.X = data.left;
                shape.data.Y = data.top;
                shape.data.width = data.width;
                shape.data.height = data.height;
            }
            if (shape.type == 'ellipse') {
                if (shape.data.X == data.left + shape.data.radiusX || shape.data.Y == data.top + shape.data.radiusY) {
                    this.draggingActive = false;
                }
                shape.data.radiusX = data.width / 2;
                shape.data.radiusY = data.height / 2;
                shape.data.X = data.left + shape.data.radiusX;
                shape.data.Y = data.top + shape.data.radiusY;
            }
            // unselect shape to stop skipping the selected shape when drawing the canvas
            this.selectedShapeIndex = false;
            this.drawScreen();
        },
        mouseHit(mouseX, mouseY, shape) {
            // check if the mouseclick was on a shape and return true if it was
            if (shape.type == 'arc') {
                let dx = shape.data.centerX - mouseX;
                let dy = shape.data.centerY - mouseY;
                return (dx*dx + dy*dy < shape.data.radius*shape.data.radius);
            }
            if ((shape.type == 'rect') || (shape.type == 'text')) {
                let dx = mouseX - shape.data.X;
                let dy = mouseY - shape.data.Y;
                return ((dx <= shape.data.width) && (dy <= shape.data.height) && (dx >= 0) && (dy >= 0));
            }
            if (shape.type == 'ellipse') {
                let dx = shape.data.X - mouseX;
                let dy = shape.data.Y - mouseY;
                return ((Math.abs(dx) < shape.data.radiusX) && (Math.abs(dy) < shape.data.radiusY));
            }
        },
        getColorRGBA(color) {
            return this.colors.filter((col) => {return col.class === color})[0].rgba;
        },
        getShapeBorder(shape) {
            return shape.data.color === 'transparent' ? 'dashed thin #000' : 'none';
        },
        getShapeBorderRadius(shape) {
            if (shape.type == 'rect') {
                return 0;
            } else {
                return '50%';
            }
        },
        getShapeOffsetLeft(shape) {
            if (shape.type == 'arc') {
                return parseInt(shape.data.centerX - shape.data.radius);
            }
            if (shape.type == 'rect') {
                return parseInt(shape.data.X);
            }
            if (shape.type == 'ellipse') {
                return parseInt(shape.data.X) - shape.data.radiusX;
            }
        },
        getShapeOffsetTop(shape) {
            if (shape.type == 'arc') {
                return parseInt(shape.data.centerY - shape.data.radius);
            }
            if (shape.type == 'rect') {
                return parseInt(shape.data.Y);
            }
            if (shape.type == 'ellipse') {
                return parseInt(shape.data.Y) - shape.data.radiusY;
            }
        },
        getShapeWidth(shape) {
            if (shape.type == 'arc') {
                return parseInt(shape.data.radius * 2);
            }
            if (shape.type == 'rect') {
                return parseInt(shape.data.width);
            }
            if (shape.type == 'ellipse') {
                return parseInt(shape.data.radiusX * 2);
            }
        },
        getShapeHeight(shape) {
            if (shape.type == 'arc') {
                return parseInt(shape.data.radius * 2);
            }
            if (shape.type == 'rect') {
                return parseInt(shape.data.height);
            }
            if (shape.type == 'ellipse') {
                return parseInt(shape.data.radiusY * 2);
            }
        },
        followLink(index) {
            if (!this.draggingActive) {
                this.$refs.map.areas[index].click();
            }
        },
    }
};
</script>
