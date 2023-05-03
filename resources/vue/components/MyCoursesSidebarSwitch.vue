<template>
    <ul class="widget-list widget-links sidebar-views">
        <li :class="{ active: tableView }">
            <a href="#" @click.prevent="setTiledView(false)">
                {{ $gettext('Tabellarische Ansicht') }}
            </a>
        </li>
        <li :class="{ active: tilesView }">
            <a href="#" @click.prevent="setTiledView(true)">
                {{ $gettext('Kachelansicht') }}
            </a>
        </li>
    </ul>
</template>

<script>
import Sidebar from "../../assets/javascripts/lib/sidebar.js";
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'my-courses-sidebar-switch',
    mixins: [MyCoursesMixin],
    computed: {
        tableView () {
            return !this.getViewConfig('tiled');
        },
        tilesView () {
            return this.getViewConfig('tiled');
        },
    },
    methods: {
        setTiledView (state) {
            this.updateViewConfig('tiled', state).then(() => {
                Sidebar.close();
            });
        }
    },
};
</script>
