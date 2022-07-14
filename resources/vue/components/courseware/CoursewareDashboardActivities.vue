<template>
    <div class="cw-dashboard-activities-wrapper">
        <span v-if="loading">
            <div class="loading-indicator">
                <span class="load-1"></span>
                <span class="load-2"></span>
                <span class="load-3"></span>
            </div>
        </span>
        <courseware-companion-box
            v-if="activitiesList.length === 0 && !loading"
            mood="sad"
            :msgCompanion="$gettext('Es wurden keine Aktivitäten gefunden.')"
        />
        <ul class="cw-dashboard-activities">
            <courseware-activity-item v-for="(item, index) in activitiesList" :key="index" :item="item" />
        </ul>
    </div>
</template>

<script>
import CoursewareActivityItem from './CoursewareActivityItem.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-dashboard-activities',
    components: {
        CoursewareActivityItem,
        CoursewareCompanionBox,
    },
    props: {
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
        }),
    },
    created: function () {
           this.getActivities();
    },
    methods: {
        ...mapActions([
            'loadCoursewareActivities'
        ]),

        async getActivities() {
            this.loading = true;
            let activities = await this.loadCoursewareActivities({ userId: this.userId, courseId: this.context.id});
            this.activitiesList = [];

            activities.forEach(activity => {
                if(activity.type === 'activities') {
                    let username = this.getUserById({ id: activity.relationships.actor.data.id }).attributes['formatted-name'];
                    const date = new Date(activity.attributes.mkdate);
                    const activityStructuralElement = this.getStructuralElementById({ id: activity.relationships.object.meta["object-id"] });

                    let breadcrumb = activityStructuralElement.attributes.title;
                    let completeBreadcrumb = activityStructuralElement.attributes.title;
                    let currentStructuralElement = activityStructuralElement;
                    if (currentStructuralElement === undefined) {
                        return;
                    }
                    let i = 1; //max breadcrumb navigation depth check
                    while (currentStructuralElement.relationships.parent.data !== null) {
                        currentStructuralElement = this.getStructuralElementById({ id: currentStructuralElement.relationships.parent.data.id });
                        if (currentStructuralElement === undefined) {
                            break;
                        }
                        completeBreadcrumb = currentStructuralElement.attributes.title + '/' + completeBreadcrumb;
                        
                        if(++i <= 3) {
                            breadcrumb = currentStructuralElement.attributes.title + '/' + breadcrumb;
                            
                            if(i == 3) {
                                breadcrumb = '.../' + breadcrumb;
                            }
                        }
                    }
                    let options = { year: 'numeric', month: '2-digit', day: '2-digit' };
                    let data = {
                        username: username,
                        date: date.toLocaleString('de-DE', options),
                        type: activity.attributes.verb,
                        text: activity.attributes.title,
                        complete_breadcrumb: completeBreadcrumb,
                        element_breadcrumb: breadcrumb,
                        element_id: activity.relationships.object.meta["object-id"],
                        context_id: activity.relationships.context.data.id,
                        content: activity.attributes.content
                    }

                    this.activitiesList.push(data);
                }
            });

            this.loading = false;
        }
    }
};
</script>
