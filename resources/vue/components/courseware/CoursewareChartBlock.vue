<template>
    <div class="cw-block cw-block-chart">
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
                <canvas class="cw-chart-block-canvas" ref="chartCanvas" />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Beschriftung</translate>
                        <input type="text" v-model="currentLabel" @focusout="buildChart" />
                    </label>
                    <label>
                        <translate>Typ</translate>
                        <select v-model="currentType">
                            <option value="bar"><translate>Säulendiagramm</translate></option>
                            <option value="horizontalBar"><translate>Balkendiagramm</translate></option>
                            <option value="pie"><translate>Kreisdiagramm</translate></option>
                            <option value="doughnut"><translate>Ringdiagramm</translate></option>
                            <option value="polarArea"><translate>Polardiagramm</translate></option>
                            <option value="line"><translate>Liniendiagramm</translate></option>
                        </select>
                    </label>
                    <fieldset v-for="(item, index) in currentContent" :key="index">
                        <legend>
                            <translate>Datensatz</translate> {{ index + 1 }}
                            <span
                                v-if="!onlyRecord"
                                class="cw-block-chart-item-remove"
                                :title="textRecordRemove"
                                @click="removeItem(index)">
                                <studip-icon shape="trash" />
                            </span>
                        </legend>
                        <label>
                            <translate>Wert</translate>
                            <input type="number" v-model="item.value" @change="buildChart" />
                        </label>
                        <label>
                            <translate>Bezeichnung</translate>
                            <input type="text" v-model="item.label" @focusout="buildChart" />
                        </label>
                        <label>
                            <translate>Farbe</translate>
                            <studip-select
                                :options="colors"
                                :reduce="colors => colors.value"
                                label="rgb"
                                :clearable="false"
                                v-model="item.color"
                                @option:selected="buildChart"
                            >
                                <template #open-indicator="selectAttributes">
                                    <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                </template>
                                <template #no-options="{ search, searching, loading }">
                                    <translate>Es steht keine Auswahl zur Verfügung.</translate>
                                </template>
                                <template #selected-option="{name, rgb}">
                                    <span class="vs__option-color" :style="{'background-color': 'rgb(' + rgb + ')'}"></span><span>{{name}}</span>
                                </template>
                                <template #option="{name, rgb}">
                                    <span class="vs__option-color" :style="{'background-color': 'rgb(' + rgb + ')'}"></span><span>{{name}}</span>
                                </template>
                            </studip-select>
                        </label>
                    </fieldset>
                </form>
                <button class="button add" @click="addItem"><translate>Datensatz hinzufügen</translate></button>
            </template>
            <template #info>
                <p><translate>Informationen zum Diagramm-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { blockMixin } from './block-mixin.js';
import Chart from 'chart.js';
import { mapActions } from 'vuex';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-chart-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        StudipIcon,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            chart: null,
            currentContent: [],
            currentLabel: '',
            currentType: '',
            colors: [
                { name:this.$gettext('Rot'), value: 'red', rgb: '192, 57, 43' },
                { name:this.$gettext('Blau'), value: 'blue', rgb: '52, 152, 219' },
                { name:this.$gettext('Gelb'), value: 'yellow', rgb: '241, 196, 15' },
                { name:this.$gettext('Grün'), value: 'green', rgb: '46, 204, 113' },
                { name:this.$gettext('Lila'), value: 'purple', rgb: '155, 89, 182' },
                { name:this.$gettext('Orange'), value: 'orange', rgb: '230, 126, 34' },
                { name:this.$gettext('Türkis'), value: 'turquoise', rgb: '26, 188, 156' },
                { name:this.$gettext('Grau'), value: 'grey', rgb: '52, 73, 94' },
                { name:this.$gettext('Hellgrau'), value: 'lightgrey', rgb: '149, 165, 166' },
                { name:this.$gettext('Schwarz'), value: 'black', rgb: '0, 0, 0' },
            ],
            textRecordRemove: this.$gettext('Datensatz entfernen'),
        };
    },
    computed: {
        content() {
            return this.block?.attributes?.payload?.content;
        },
        label() {
            return this.block?.attributes?.payload?.label;
        },
        type() {
            return this.block?.attributes?.payload?.type;
        },
        onlyRecord() {
            return this.currentContent.length === 1;
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
            this.currentContent = this.content;
            this.currentLabel = this.label;
            this.currentType = this.type;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.content = this.currentContent;
            attributes.payload.label = this.currentLabel;
            attributes.payload.type = this.currentType;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },

        addItem() {
            this.currentContent.push({ value: '0', label: '', color: 'blue' });
        },

        removeItem(recordIndex) {
            this.currentContent = this.currentContent.filter((val, index) => {
                return !(index === recordIndex);
            });
            this.buildChart();
        },

        buildChart() {
            if (this.chart !== null) {
                this.chart.destroy();
            }
            let ctx = this.$refs.chartCanvas.getContext('2d');
            let type = this.currentType;
            let label = this.currentLabel;
            let labels = [];
            let data = [];
            let backgroundColor = [];
            let borderColor = [];

            this.currentContent.forEach((item) => {
                labels.push(item.label);
                data.push(item.value);
                backgroundColor.push('rgba(' + this.colors.filter((color) => { return color.value === item.color })[0].rgb + ', 0.6)');
                borderColor.push('rgba(' + this.colors.filter((color) => { return color.value === item.color })[0].rgb + ', 1.0)');
            });

            switch (type) {
                case 'bar':
                case 'horizontalBar':
                    this.chart = new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: label,
                                    data: data,
                                    backgroundColor: backgroundColor,
                                    borderColor: borderColor,
                                    borderWidth: 1,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                yAxes: [
                                    {
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                    },
                                ],
                                xAxes: [
                                    {
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                    },
                                ],
                            },
                            legend: {
                                display: false,
                            },
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
                case 'pie':
                case 'doughnut':
                case 'polarArea':
                    this.chart = new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    data: data,
                                    backgroundColor: backgroundColor,
                                    borderWidth: 1,
                                },
                            ],
                        },
                        options: {
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
                case 'line':
                    this.chart = new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: label,
                                    data: data,
                                    fill: false,
                                    borderWidth: 2,
                                    pointBackgroundColor: borderColor,
                                },
                            ],
                        },
                        options: {
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
            }
        },
    },
    watch: {
        currentType() {
            this.buildChart();
        },
    },
};
</script>
