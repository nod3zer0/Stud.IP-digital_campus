<template>
    <section class="contentbox">
        <header><h1>{{ $gettext('Aktivitäten') }}</h1></header>
        <section>
            <studip-progress-indicator
                v-show="loading"
                :description="$gettext('Lade Aktivitäten…')"
            />
            <courseware-companion-box
                v-if="filteredActivitiesList.length === 0 && !loading"
                mood="sad"
                :msgCompanion="$gettext('Es wurden keine Aktivitäten gefunden.')"
            />
            <ul class="cw-activities">
                <courseware-activity-item v-for="(item, index) in filteredActivitiesList" :key="index" :item="item" />
            </ul>
        </section>
    </section>
</template>

<script>
import CoursewareActivityItem from './CoursewareActivityItem.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-activities',
    components: {
        CoursewareActivityItem,
        CoursewareCompanionBox,
        StudipProgressIndicator,
    },
    data() {
        return {
            activitiesList: [],
            loading: false
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getUserById: 'users/byId',
            context: 'context',
            getStructuralElementById: 'courseware-structural-elements/byId',
            getCoursewareUnitById: 'courseware-units/byId',
            coursewareUnits: 'courseware-units/all',

            typeFilter: 'typeFilter',
            unitFilter: 'unitFilter'
        }),
        filteredActivitiesList() {
            let list = this.activitiesList.slice().sort((a,b) => b.timestamp - a.timestamp);
            if (['edited', 'created', 'answered', 'interacted', 'voided',].includes(this.typeFilter)) {
                list = list.filter(activity => activity.type === this.typeFilter);
            }
            if (this.unitFilter !== 'all') {
                list = list.filter(activity => activity.unitId === this.unitFilter);
            }

            return list;
        },
    },
    mounted() {
        this.getActivities();
    },
    methods: {
        ...mapActions({
            loadCoursewareActivities: 'loadCoursewareActivities',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
        }),

        async loadActivitiesElements(activities) {
            const results = [];
            for (const activity of activities) {
                const structuralElementId = activity.relationships.object.meta["object-id"];
                results.push(this.loadStructuralElementById({id: structuralElementId, options: { include: 'ancestors'} }));
            }
            // activity might contain structural element hidden for current user
            return Promise.all(results).catch(e => { if (e.status !== 403) { console.error(e); } });
        },

        async getActivities() {
            this.loading = true;
            let activities = await this.loadCoursewareActivities({ userId: this.userId, courseId: this.context.id});
            this.activitiesList = [];

            await this.loadActivitiesElements(activities);

            for (const activity of activities) {
                let error = false;
                let username = this.getUserById({ id: activity.relationships.actor.data.id }).attributes['formatted-name'];
                const date = new Date(activity.attributes.mkdate);
                const structuralElementId = activity.relationships.object.meta["object-id"];

                const activityStructuralElement = this.getStructuralElementById({ id: structuralElementId });
                if (activityStructuralElement === undefined || !activityStructuralElement.attributes['can-visit']) {
                    error = true;
                }
                if (!error) {
                    const unitId = activityStructuralElement.relationships?.unit?.data?.id ?? null;
                    const unit = this.getCoursewareUnitById({id: unitId });
                    let options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                    let data = {
                        username: username,
                        timestamp: date.getTime(),
                        readableDate: date.toLocaleString('de-DE', options),
                        type: activity.attributes.verb,
                        title: activity.attributes.title,
                        elementId: structuralElementId,
                        unitId: unitId,
                        unit: unit,
                        contextId: activity.relationships.context.data.id,
                        content: activity.attributes.content
                    }
                    this.activitiesList.push(data);
                }
            }
            this.loading = false;
        }
    }
};
</script>
