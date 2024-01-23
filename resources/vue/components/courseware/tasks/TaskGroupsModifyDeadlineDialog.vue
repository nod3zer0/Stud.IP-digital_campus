<template>
    <studip-dialog
        :title="$gettext('Bearbeitungszeit verlängern')"
        :confirmText="$gettext('Verlängern')"
        confirmClass="accept"
        :closeText="$gettext('Abbrechen')"
        closeClass="cancel"
        @close="onClose"
        @confirm="onConfirm"
    >
        <template #dialogContent>
            <form class="default">
                <p>
                    {{ $gettext('Aktuelle Bearbeitungszeit:') }} <StudipDate :date="startDate" /> - <StudipDate
                        :date="endDate"
                    />
                    ({{ $gettextInterpolate($gettext('%{ count } Tage'), { count: oldDuration }) }})
                </p>
                <div class="formpart">
                    <label class="studiprequired">
                        <span class="textlabel">{{ $gettext('Bearbeitungszeit verlängern bis zum') }}</span>
                        <span class="asterisk" :title="$gettext('Dies ist ein Pflichtfeld')" aria-hidden="true">*</span>
                        <input
                            :id="`task-groups-${uid}`"
                            name="end-date"
                            type="date"
                            v-model="localEndDate"
                            :min="endDateString"
                            class="size-l"
                            required
                        />
                    </label>
                </div>
                <p>
                    {{ $gettext('Verlängerte Bearbeitungszeit:') }} <StudipDate :date="startDate" /> - <StudipDate
                        :date="newEndDate"
                    />
                    ({{ $gettextInterpolate($gettext('%{ count } Tage'), { count: newDuration }) }})
                </p>
            </form>
        </template>
    </studip-dialog>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import StudipDate from '../../StudipDate.vue';

const midnight = (_date) => {
    const date = new Date(_date);
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);
    return date;
};

const dateString = (date) =>
    `${date.getFullYear()}-${('' + (date.getMonth() + 1)).padStart(2, '0')}-${('' + date.getDate()).padStart(2, '0')}`;

let nextUid = 0;

export default {
    props: ['taskGroup'],
    components: {
        StudipDate,
    },
    data: () => ({ localEndDate: null, uid: nextUid++ }),
    computed: {
        endDate() {
            return midnight(this.taskGroup?.attributes?.['end-date'] ?? new Date());
        },
        endDateString() {
            return dateString(this.endDate);
        },
        newDuration() {
            return this.localEndDate
                ? Math.floor((midnight(this.localEndDate) - this.startDate) / (1000 * 60 * 60 * 24))
                : 0;
        },
        newEndDate() {
            return this.localEndDate ? midnight(this.localEndDate) : this.endDate;
        },
        oldDuration() {
            return Math.floor((this.endDate - this.startDate) / (1000 * 60 * 60 * 24));
        },
        startDate() {
            return midnight(this.taskGroup.attributes['start-date']);
        },
    },
    methods: {
        ...mapActions({
            modifyDeadline: 'tasks/modifyDeadlineOfTaskGroup',
            setShowDialog: 'tasks/setShowTaskGroupsModifyDeadlineDialog',
        }),
        onClose() {
            this.setShowDialog(false);
        },
        onConfirm() {
            const endDate = midnight(this.localEndDate);
            this.modifyDeadline({ taskGroup: this.taskGroup, endDate });
            this.onClose();
        },
        resetLocalVars() {
            this.localEndDate = dateString(this.endDate ?? new Date());
        },
    },
    mounted() {
        this.resetLocalVars();
    },
    watch: {
        taskGroup() {
            this.resetLocalVars();
        },
    },
};
</script>
