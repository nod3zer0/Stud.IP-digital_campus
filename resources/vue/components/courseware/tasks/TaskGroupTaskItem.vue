<template>
    <tr>
        <td>
            <studip-icon
                v-if="status.shape !== undefined"
                :shape="status.shape"
                :role="status.role"
                :title="status.description"
                aria-hidden="true"
            />
            <span class="sr-only">{{ status.description }}</span>
        </td>
        <td>
            <span v-if="user">
                <studip-icon shape="person2" role="info" aria-hidden="true" :title="$gettext('Teilnehmende Person')" />
                <span class="sr-only">{{ $gettext('Teilnehmende Person') }}</span>
                {{ user.attributes['formatted-name'] }}
            </span>
            <span v-if="group">
                <studip-icon shape="group2" role="info" aria-hidden="true" :title="$gettext('Gruppe')" />
                <span class="sr-only">{{ $gettext('Gruppe') }}</span>
                {{ group.attributes['name'] }}
            </span>
        </td>
        <td class="responsive-hidden">
            <a v-if="task.attributes.submitted" :href="getLinkToElement(element)">
                {{ element.attributes.title }}
            </a>
            <span v-else>{{ element.attributes.title }}</span>
        </td>
        <td>{{ task.attributes?.progress?.toFixed(2) || '-.--' }}%</td>
        <td>{{ getReadableDate(task.attributes['submission-date']) }}</td>
        <td>
            <studip-icon v-if="task.attributes.submitted" shape="accept" role="status-green" />
        </td>
        <td class="responsive-hidden">
            <button v-show="task.attributes.renewal === 'pending'" class="button" @click="$emit('solve-renewal', task)">
                {{ $gettext('Anfrage bearbeiten') }}
            </button>
            <span v-show="task.attributes.renewal === 'declined'">
                <studip-icon shape="decline" role="status-red" />
                {{ $gettext('Anfrage abgelehnt') }}
            </span>
            <span v-show="task.attributes.renewal === 'granted'">
                {{ $gettext('verlängert bis') }}:
                {{ getReadableDate(task.attributes['renewal-date']) }}
            </span>
            <studip-icon
                v-if="task.attributes.renewal === 'declined' || task.attributes.renewal === 'granted'"
                :title="$gettext('Anfrage bearbeiten')"
                class="edit"
                shape="edit"
                @click="$emit('solve-renewal', task)"
            />
        </td>
        <td class="responsive-hidden">
            <span
                v-if="feedback"
                :title="
                    $gettextInterpolate($gettext('Feedback geschrieben am: %{ date }'), {
                        date: getReadableDate(feedback.attributes['chdate']),
                    })
                "
            >
                <studip-icon shape="accept" role="status-green" />
                {{ $gettext('Feedback gegeben') }}
                <studip-icon
                    :title="$gettext('Feedback bearbeiten')"
                    class="edit"
                    shape="edit"
                    @click="$emit('edit-feedback', feedback)"
                />
            </span>

            <button v-show="!feedback && task.attributes.submitted" class="button" @click="$emit('add-feedback', task)">
                {{ $gettext('Feedback geben') }}
            </button>
        </td>
    </tr>
</template>
<script>
import taskHelper from '../../../mixins/courseware/task-helper.js';
import { mapGetters } from 'vuex';

export default {
    mixins: [taskHelper],
    props: ['task', 'taskGroup'],
    computed: {
        ...mapGetters({
            elementById: 'courseware-structural-elements/byId',
            feedbackById: 'courseware-task-feedback/byId',
            statusGroupById: 'status-groups/byId',
            userById: 'users/byId',
        }),
        element() {
            return this.elementById({ id: this.task.relationships['structural-element'].data.id });
        },
        feedback() {
            const id = this.task.relationships['task-feedback'].data?.id;
            return id ? this.feedbackById({ id }) : null;
        },
        group() {
            const { id, type } = this.solver;
            return type === 'status-groups' ? this.statusGroupById({ id }) : null;
        },
        solver() {
            return this.task.relationships.solver.data;
        },
        status() {
            return this.getStatus(this.task);
        },
        user() {
            const { id, type } = this.solver;
            return type === 'users' ? this.userById({ id }) : null;
        },
    },
};
</script>
