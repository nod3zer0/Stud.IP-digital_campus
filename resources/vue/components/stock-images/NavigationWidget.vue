<template>
    <SidebarWidget>
        <template #content>
            <ul
                class="widget-list widget-links sidebar-navigation navigation-level-3"
                :aria-label="$gettext('Dritte Navigationsebene')"
            >
                <li class="active">
                    <a aria-current="page" id="nav_overview_index" class="active" :href="overviewUrl">
                        {{ $gettext('Übersicht') }}
                    </a>
                </li>
            </ul>
        </template>
    </SidebarWidget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';

export default {
    components: {
        SidebarWidget,
    },
    computed: {
        overviewUrl() {
            return window.STUDIP.URLHelper.getURL('dispatch.php/contents/overview');
        },
    },
};
</script>
