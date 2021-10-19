<template>
    <div class="cw-manager-copy-selector">
        <div v-if="sourceEmpty" class="cw-manager-copy-selector-source">
            <button class="button" @click="selectSource('own'); loadOwnCourseware()"><translate>Aus meine Inhalte kopieren</translate></button>
            <button class="button" @click="selectSource('remote')"><translate>Aus Veranstaltung kopieren</translate></button>
        </div>
        <div v-else>
            <button class="button" @click="reset"><translate>Quelle auswählen</translate></button>
            <button v-show="!sourceOwn && hasRemoteCid" class="button" @click="selectNewCourse"><translate>Veranstaltung auswählen</translate></button>
            <div v-if="sourceRemote">
                <h2 v-if="!hasRemoteCid"><translate>Veranstaltungen</translate></h2>
                <ul v-if="!hasRemoteCid">
                    <li v-for="semester in semesterMap" :key="semester.id">
                    <h3>{{semester.attributes.title}}</h3>
                        <ul>
                            <li
                                v-for="course in coursesBySemester(semester)"
                                :key="course.id"
                                class="cw-manager-copy-selector-course"
                                @click="loadRemoteCourseware(course.id)"
                            >
                            <studip-icon :shape="getCourseIcon(course)" />
                                {{course.attributes.title}}
                            </li>
                        </ul>
                    </li>

                </ul>
                <courseware-manager-element
                    v-if="remoteId !== '' && hasRemoteCid"
                    type="remote"
                    :currentElement="remoteElement"
                    @selectElement="setRemoteId"
                    @loadSelf="loadSelf"
                />
                <courseware-companion-box
                    v-if="remoteId === '' && hasRemoteCid"
                    :msgCompanion="$gettext('In dieser Veranstaltung wurden noch keine Inhalte angelegt')"
                    mood="sad"
                />
            </div>
            <div v-if="sourceOwn">
                <courseware-manager-element
                    v-if="ownId !== ''"
                    type="own"
                    :currentElement="ownElement"
                    @selectElement="setOwnId"
                    @loadSelf="loadSelf"
                />
                <courseware-companion-box
                    v-else
                    :msgCompanion="$gettext('Sie haben noch keine eigenen Inhalte angelegt')"
                    mood="sad"
                />
            </div>
        </div>
    </div>
</template>

<script>
import CoursewareManagerElement from './CoursewareManagerElement.vue';
import { mapActions, mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-manager-copy-selector',
    components:{
        CoursewareManagerElement,
        CoursewareCompanionBox,
    },
    props: {},
    data() {return{
        source: '',
        courses: [],
        remoteCid: '',
        remoteCoursewareInstance: {},
        remoteId: '',
        remoteElement: {},
        ownCoursewareInstance: {},
        ownId: '',
        ownElement: {},
        semesterMap: [],

    }},
    computed: {
        ...mapGetters({
            userId: 'userId',
            structuralElementById: 'courseware-structural-elements/byId',
            semesterById: 'semesters/byId',
        }),
        sourceEmpty() {
            return this.source === '';
        },
        sourceOwn() {
            return this.source === 'own';
        },
        sourceRemote() {
            return this.source === 'remote';
        },
        hasRemoteCid() {
            return this.remoteCid !== '';
        },
        loadedCourses() {
            return this.courses.sort((a, b) => a.attributes.title > b.attributes.title);
        }
    },
    methods: {
        ...mapActions({
            loadUsersCourses: 'loadUsersCourses',
            loadStructuralElement: 'loadStructuralElement',
            loadRemoteCoursewareStructure: 'loadRemoteCoursewareStructure',
            loadSemester: 'semesters/loadById',
        }),
        selectSource(source) {
            this.source = source;
        },
        async loadRemoteCourseware(cid) {
            this.remoteCid = cid;
            this.remoteCoursewareInstance = await this.loadRemoteCoursewareStructure({rangeId: this.remoteCid, rangeType: 'courses'});
            if (this.remoteCoursewareInstance !== null) {
                this.setRemoteId(this.remoteCoursewareInstance.relationships.root.data.id);
            } else {
                this.remoteId = '';
            }
            
        },
        async loadOwnCourseware() {
            this.ownCoursewareInstance = await this.loadRemoteCoursewareStructure({rangeId: this.userId, rangeType: 'users'});
            if (this.ownCoursewareInstance !== null) {
                this.setOwnId(this.ownCoursewareInstance.relationships.root.data.id);
            } else {
                this.ownId = '';
            }
        },
        reset() {
            this.selectSource('');
            this.remoteCid = '';
        },
        selectNewCourse() {
            this.remoteCid = '';
            this.remoteId = '';
        },
        async setRemoteId(target) {
            this.remoteId = target;
            await this.loadStructuralElement(this.remoteId);
            this.initRemote();
        },
        initRemote() {
            this.remoteElement = this.structuralElementById({ id: this.remoteId });
        },
        async setOwnId(target) {
            this.ownId = target;
            await this.loadStructuralElement(this.ownId);
            this.initOwn();
        },
        initOwn() {
            this.ownElement = this.structuralElementById({ id: this.ownId });
        },
        loadSelf(data) {
            this.$emit('loadSelf', data);
        },
        loadSemesterMap() {
            let view = this;
            let semesters = [];
            this.courses.every(course => {
                let semId = course.relationships['start-semester'].data.id;
                if(!semesters.includes(semId)) {
                    semesters.push(semId);
                }
                return true;
            });
            semesters.every(semester => {
                view.loadSemester({id: semester}).then( () => {
                    view.semesterMap.push(view.semesterById({id: semester}));
                    view.semesterMap.sort((a, b) => a.attributes.start < b.attributes.start);
                });
                return true;
            });
        },
        coursesBySemester(semester) {
            return this.loadedCourses.filter(course => {
                return course.relationships['start-semester'].data.id === semester.id}
            );
        },
        getCourseIcon(course) {
            if (course.attributes['course-type'] === 99) {
                return 'studygroup';
            }

            return 'seminar';
        }
    },
    async mounted() {
        this.courses = await this.loadUsersCourses(this.userId);
        this.loadSemesterMap();
    }

}
</script>
