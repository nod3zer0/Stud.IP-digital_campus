<template>
    <div v-if="details" class="course-details">
        <div class="semester">
            ({{ details.semester }})
        </div>
        <div class="admission-state" v-if="details.admissionstate">
            <studip-icon :shape="details.admissionstate.icon" :role="details.admissionstate.role"
                         :title="details.admissionstate.info"></studip-icon>
        </div>
        <div class="course-lecturers">
            <span v-for="(lecturer, index) in details.lecturers" :key="index">
                <a :href="profileUrl(lecturer.username)"
                   :title="$gettextInterpolate($gettext('Zum Profil von %{ user }'),
                        { user: lecturer.name })">
                    {{ lecturer.name }}
                </a><template v-if="details.lecturers.length > 1 && index < details.lecturers.length - 1">, </template>
            </span>
        </div>
        <MountingPortal :mountTo="'#course-dates-' + course" :append="true">
            <span v-html="details.dates"></span>
        </MountingPortal>
    </div>
</template>

<script>
import axios from 'axios';
import { TreeMixin } from '../../mixins/TreeMixin';

export default {
    name: 'TreeCourseDetails',
    mixins: [ TreeMixin ],
    props: {
        course: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            details: null
        }
    },
    mounted() {
        axios.get(
            STUDIP.URLHelper.getURL('jsonapi.php/v1/tree-node/course/details/' + this.course)
        ).then(response => {
            this.details = response.data;
        });
    }
}
</script>
