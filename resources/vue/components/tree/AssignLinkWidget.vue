<template>
    <sidebar-widget v-if="node" id="assignwidget" class="sidebar-assign" :title="$gettext('Zuordnung')">
        <template #content>
            <a :href="assignUrl" :title="$gettext('Angezeigte Veranstaltungen zuordnen')"
               @click.prevent="assignCurrentCourses">
                <studip-icon shape="arr_2right"></studip-icon>
                {{ $gettext('Angezeigte Veranstaltungen zuordnen') }}
            </a>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';
import StudipIcon from '../StudipIcon.vue';
import { TreeMixin } from '../../mixins/TreeMixin';

export default {
    name: 'AssignLinkWidget',
    components: { SidebarWidget, StudipIcon },
    mixins: [ TreeMixin ],
    props: {
        node: {
            type: String,
            required: true
        },
        courses: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        assignUrl() {
            return STUDIP.URLHelper.getURL('dispatch.php/admin/tree/batch_assign_semtree');
        }
    },
    methods: {
        assignCurrentCourses() {
            STUDIP.Dialog.fromURL(this.assignUrl, { data: {
                assign_semtree: this.courses.map(course => course.id),
                return: window.location.href
            }});
        }
    }
}
</script>
