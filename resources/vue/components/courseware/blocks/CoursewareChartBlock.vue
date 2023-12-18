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
                    <label class="col-3">
                        {{ $gettext('Beschriftung') }}
                        <input type="text" v-model="currentLabel" @focusout="buildChart" />
                    </label>
                    <label class="col-3">
                        {{ $gettext('Typ') }}
                        <studip-select
                            v-model="currentType"
                            :options="chartTypes"
                            :reduce="chartTypes => chartTypes.value"
                            :clearable="false"
                            @option:selected="buildChart"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                            </template>
                            <template #selected-option="{name}">
                                <span>{{name}}</span>
                            </template>
                            <template #option="{name}">
                                <span>{{name}}</span>
                            </template>
                        </studip-select>
                    </label>
                </form>
                <button class="button add" @click="addItem">{{ $gettext('Datensatz hinzufügen') }}</button>
                <courseware-tabs
                    v-if="currentContent.length > 0"
                    :setSelected="setItemTab"
                    @selectTab="setItemTab = (parseInt($event.name.replace($gettext('Datensatz') +  ' ', ''), 10) - 1)"
                >
                    <courseware-tab
                        v-for="(item, index) in currentContent"
                        :key="index"
                        :index="index"
                        :name="$gettext('Datensatz') +  ' ' + (index + 1).toString()"
                        :selected="index === 0"
                        canBeEmpty
                    >
                        <form class="default" @submit.prevent="">
                            <label class="col-1">
                                {{ $gettext('Wert') }}
                                <input type="number" v-model="item.value" @change="buildChart" />
                            </label>
                            <label class="col-2">
                                {{ $gettext('Farbe') }}
                                <studip-select
                                    :options="colors"
                                    :reduce="colors => colors.value"
                                    label="rgb"
                                    :clearable="false"
                                    v-model="item.color"
                                    @option:selected="buildChart"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10"/></span>
                                    </template>
                                    <template #no-options>
                                        {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                                    </template>
                                    <template #selected-option="{name, rgb}">
                                        <span class="vs__option-color" :style="{'background-color': 'rgb(' + rgb + ')'}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, rgb}">
                                        <span class="vs__option-color" :style="{'background-color': 'rgb(' + rgb + ')'}"></span><span>{{name}}</span>
                                    </template>
                                </studip-select>
                            </label>
                            <label class="col-3">
                                {{ $gettext('Bezeichnung') }}
                                <input type="text" v-model="item.label" @focusout="buildChart" />
                            </label>
                            <button
                                v-if="currentContent.length > 1"
                                class="button trash"
                                @click="removeItem(index)"
                            >
                                {{ $gettext('Datensatz entfernen') }}
                            </button>
                        </form>
                    </courseware-tab>
                </courseware-tabs>                
            </template>
            <template #info>
                <p>{{ $gettext('Informationen zum Diagramm-Block') }}</p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import blockMixin from '@/vue/mixins/courseware/block.js';
import Chart from 'chart.js';
import { mapActions } from 'vuex';


export default {
    name: 'courseware-chart-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, {}),
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
            chartTypes: [
                { name: this.$gettext('Säulendiagramm'), value: 'bar'},
                { name: this.$gettext('Balkendiagramm'), value: 'horizontalBar'},
                { name: this.$gettext('Kreisdiagramm'), value: 'pie'},
                { name: this.$gettext('Ringdiagramm'), value: 'doughnut'},
                { name: this.$gettext('Polardiagramm'), value: 'polarArea'},
                { name: this.$gettext('Liniendiagramm'), value: 'line'},
            ],
            textRecordRemove: this.$gettext('Datensatz entfernen'),
            setItemTab: 0,
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
            this.currentContent = this.content || [];
            this.currentLabel = this.label;
            this.currentType = this.type;
            this.setItemTab = 0;
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
            this.$nextTick(() => { this.setItemTab = this.currentContent.length - 1; });
        },

        removeItem(recordIndex) {
            this.currentContent = this.currentContent.filter((val, index) => {
                return !(index === recordIndex);
            });
            this.buildChart();
            this.$nextTick(() => { this.setItemTab = 0; });
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
