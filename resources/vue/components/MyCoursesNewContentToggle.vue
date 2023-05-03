<template>
    <ul class="widget-list widget-options">
        <li>
            <a href="#" class="options-checkbox" :class="showNewContents ? 'options-checked' : 'options-unchecked'" @click.prevent="toggleNewContents">
                {{ $gettext('Nur neue Inhalte anzeigen') }}
            </a>
        </li>
    </ul>
</template>

<script>
import Sidebar from "../../assets/javascripts/lib/sidebar.js";
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'my-courses-new-content-toggle',
    mixins: [MyCoursesMixin],
    computed: {
        showNewContents () {
            return this.getViewConfig('only_new');
        },
    },
    methods: {
        toggleNewContents() {
            this.updateViewConfig('only_new', !this.showNewContents).then(() => {
                Sidebar.close();
            });
        },
    },
};
</script>
