<template>
    <div>
        <CompanionBox :msgCompanion="statusMessage">
            <template #companionActions>
                <span>
                    {{ $gettext('Bearbeitungszeit') }}
                    <StudipDate :date="startDate" /> - <StudipDate :date="endDate" />
                </span>
            </template>
        </CompanionBox>

        <section v-if="tasks.length > 0">
            <table class="default">
                <caption>
                    {{ $gettext('Verteilte Aufgaben') }}
                </caption>
                <thead>
                    <tr>
                        <th>{{ $gettext('Status') }}</th>
                        <th>{{ $gettext('Teilnehmende/Gruppen') }}</th>
                        <th class="responsive-hidden">{{ $gettext('Seite') }}</th>
                        <th>{{ $gettext('bearbeitet') }}</th>
                        <th>{{ $gettext('Abgabefrist') }}</th>
                        <th>{{ $gettext('Abgabe') }}</th>
                        <th class="responsive-hidden renewal">{{ $gettext('Verlängerungsanfrage') }}</th>
                        <th class="responsive-hidden feedback">{{ $gettext('Feedback') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <TaskItem
                        v-for="task in tasks"
                        :task="task"
                        :taskGroup="taskGroup"
                        :key="task.id"
                        @add-feedback="(task) => $emit('add-feedback', task)"
                        @edit-feedback="(feedback) => $emit('edit-feedback', feedback)"
                        @solve-renewal="(task) => $emit('solve-renewal', task)"
                    />
                </tbody>
            </table>
        </section>
        <div v-else>
            <CompanionBox mood="pointing" :msgCompanion="$gettext('Diese Aufgabe wurde an niemanden verteilt.')" />
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import CompanionBox from '../layouts/CoursewareCompanionBox.vue';
import StudipDate from '../../StudipDate.vue';
import TaskItem from './TaskGroupTaskItem.vue';
import { getStatus } from './task-groups-helper.js';

export default {
    components: { CompanionBox, StudipDate, TaskItem },
    props: ['taskGroup', 'tasks'],
    computed: {
        ...mapGetters({
            coursewareContext: 'context',
        }),
        actionMenuContext() {
            return this.$gettextInterpolate(this.$gettext('Courseware-Aufgabe "%{ taskGroup }"'), {
                taskGroup: this.taskGroup.attributes.title,
            });
        },
        endDate() {
            return new Date(this.taskGroup.attributes['end-date']);
        },
        isAfter() {
            return new Date() > this.endDate;
        },
        startDate() {
            return new Date(this.taskGroup.attributes['start-date']);
        },
        status() {
            return getStatus(this.taskGroup);
        },
        statusMessage() {
            return this.status.description;
        },
    },
};
</script>
