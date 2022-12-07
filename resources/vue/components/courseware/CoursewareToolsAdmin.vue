<template>
    <div class="cw-tools cw-tools-admin">
        <form class="default" @submit.prevent="">
            <fieldset>
                <legend>{{ $gettext('Allgemeine Einstellungen') }}</legend>
                <label>
                    <span>{{ $gettext('Art der Inhaltsabfolge') }}</span>
                    <select class="size-s" v-model="currentProgression">
                        <option value="0">{{ $gettext('Frei') }}</option>
                        <option value="1">{{ $gettext('Sequentiell') }}</option>
                    </select>
                </label>

                <label>
                    <span>{{ $gettext('Editierberechtigung für Tutor/-innen') }}</span>
                    <select class="size-s" v-model="currentPermissionLevel">
                        <option value="dozent">{{ $gettext('Nein') }}</option>
                        <option value="tutor">{{ $gettext('Ja') }}</option>
                    </select>
                </label>
            </fieldset>
            <fieldset>
                <legend>
                    {{ $gettext('Zertifikate') }}
                </legend>
                <label>
                    <input type="checkbox" name="makecert" v-model="makeCert">
                    <span>
                        {{ $gettext('Zertifikat bei Erreichen einer Fortschrittsgrenze versenden') }}
                    </span>
                    <studip-tooltip-icon :text="$gettext('Erreicht eine Person in diesem Lernmaterial den ' +
                        'hier eingestellten Fortschritt, so erhält Sie ein PDF-Zertifikat per E-Mail.')"/>
                </label>
                <label v-if="makeCert">
                    <span>
                        {{ $gettext('Erforderlicher Fortschritt (in Prozent), um ein Zertifikat zu erhalten') }}
                    </span>
                    <input type="number" min="1" max="100" name="threshold" v-model="certThreshold">
                </label>
                <label v-if="makeCert">
                    <span>
                        {{ $gettext('Hintergrundbild des Zertifikats wählen') }}
                    </span>
                    <courseware-file-chooser :isImage="true" v-model="certImage"
                                             @selectFile="updateCertImage"></courseware-file-chooser>
                </label>
            </fieldset>
            <fieldset>
                <legend>
                    {{ $gettext('Erinnerungen') }}
                </legend>
                <label>
                    <input type="checkbox" name="sendreminders" v-model="sendReminders">
                    <span>
                        {{ $gettext('Erinnerungsnachrichten an alle Teilnehmenden schicken') }}
                    </span>
                    <studip-tooltip-icon :text="$gettext('Hier können periodisch Nachrichten an alle ' +
                    'Teilnehmenden verschickt werden, um z.B. an die Bearbeitung dieses Lernmaterials zu erinnern.')"/>
                </label>

                <label v-if="sendReminders">
                    <span>
                        {{ $gettext('Zeitraum zwischen Erinnerungen') }}
                    </span>
                    <select name="reminder_interval" v-model="reminderInterval">
                        <option value="7">
                            {{ $gettext('wöchentlich') }}
                        </option>
                        <option value="14">
                            {{ $gettext('14-tägig') }}
                        </option>
                        <option value="30">
                            {{ $gettext('monatlich') }}
                        </option>
                        <option value="90">
                            {{ $gettext('vierteljährlich') }}
                        </option>
                        <option value="180">
                            {{ $gettext('halbjährlich') }}
                        </option>
                        <option value="365">
                            {{ $gettext('jährlich') }}
                        </option>
                    </select>
                </label>
                <label v-if="sendReminders" class="col-3">
                    <span>
                        {{ $gettext('Erstmalige Erinnerung am') }}
                        <input type="date" name="reminder_start_date"
                               v-model="reminderStartDate">
                    </span>
                </label>
                <label v-if="sendReminders" class="col-3">
                    <span>
                        {{ $gettext('Letztmalige Erinnerung am') }}
                        <input type="date" name="reminder_end_date"
                               v-model="reminderEndDate">
                    </span>
                </label>
                <label v-if="sendReminders">
                    <span>
                        {{ $gettext('Text der Erinnerungsmail') }}
                        <textarea cols="70" rows="4" name="reminder_mail_text" data-editor="minimal"
                                  v-model="reminderMailText"></textarea>
                    </span>
                </label>
            </fieldset>
            <fieldset>
                <legend>
                    {{ $gettext('Fortschritt') }}
                </legend>
                <label>
                    <input type="checkbox" name="resetprogress" v-model="resetProgress">
                    <span>
                        {{ $gettext('Fortschritt periodisch auf 0 zurücksetzen') }}
                    </span>
                    <studip-tooltip-icon :text="$gettext('Hier kann eingestellt werden, den Fortschritt ' +
                        'aller Teilnehmenden periodisch auf 0 zurückzusetzen.')"/>
                </label>
                <label v-if="resetProgress">
                    <span>
                        {{ $gettext('Zeitraum zum Rücksetzen des Fortschritts') }}
                    </span>
                    <select name="reset_progress_interval" v-model="resetProgressInterval">
                        <option value="14">
                            {{ $gettext('14-tägig') }}
                        </option>
                        <option value="30">
                            {{ $gettext('monatlich') }}
                        </option>
                        <option value="90">
                            {{ $gettext('vierteljährlich') }}
                        </option>
                        <option value="180">
                            {{ $gettext('halbjährlich') }}
                        </option>
                        <option value="365">
                            {{ $gettext('jährlich') }}
                        </option>
                    </select>
                </label>
                <label v-if="resetProgress" class="col-3">
                    <span>
                        {{ $gettext('Erstmaliges Zurücksetzen am') }}
                        <input type="date" dataformatas="" name="reset_progress_start_date"
                               v-model="resetProgressStartDate">
                    </span>
                </label>
                <label v-if="resetProgress" class="col-3">
                    <span>
                        {{ $gettext('Letztmaliges Zurücksetzen am') }}
                        <input type="date" name="reset_progress_end_date"
                               v-model="resetProgressEndDate">
                    </span>
                </label>
                <label v-if="resetProgress">
                    <span>
                        {{ $gettext('Text der Rücksetzungsmail') }}
                        <textarea cols="70" rows="4" name="reset_progress_mail_text" data-editor="minimal"
                                  v-model="resetProgressMailText"></textarea>
                    </span>
                </label>
            </fieldset>
        </form>
        <button class="button" @click="store">{{ $gettext('Übernehmen') }}</button>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CoursewareFileChooser from "./CoursewareFileChooser.vue";
import StudipTooltipIcon from '../StudipTooltipIcon.vue';

export default {
    name: 'cw-tools-admin',
    components: { StudipTooltipIcon, CoursewareFileChooser },
    data() {
        return {
            currentPermissionLevel: '',
            currentProgression: '',
            makeCert: false,
            certThreshold: 0,
            certImage: '',
            sendReminders: false,
            reminderInterval: 7,
            reminderStartDate: '',
            reminderEndDate: '',
            reminderMailText: '',
            resetProgress: false,
            resetProgressInterval: 180,
            resetProgressStartDate: '',
            resetProgressEndDate: '',
            resetProgressMailText: ''
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
        }),
    },
    methods: {
        ...mapActions({
            storeCoursewareSettings: 'storeCoursewareSettings',
            companionSuccess: 'companionSuccess',
        }),
        initData() {
            console.log(this.courseware.attributes);
            this.currentPermissionLevel = this.courseware.attributes['editing-permission-level'];
            this.currentProgression = this.courseware.attributes['sequential-progression'] ? '1' : '0';
            this.certSettings = this.courseware.attributes['certificate-settings'];
            this.makeCert = typeof(this.certSettings) === 'object' &&
                Object.keys(this.certSettings).length > 0;
            this.certThreshold = this.certSettings.threshold;
            this.certImage = this.certSettings.image;
            this.reminderSettings = this.courseware.attributes['reminder-settings'];
            this.sendReminders = typeof(this.reminderSettings) === 'object' &&
                Object.keys(this.reminderSettings).length > 0;
            this.reminderInterval = this.reminderSettings.interval;
            this.reminderStartDate = this.reminderSettings.startDate;
            this.reminderEndDate = this.reminderSettings.endDate;
            this.reminderMailText = this.reminderSettings.mailText;
            this.resetProgressSettings = this.courseware.attributes['reset-progress-settings'];
            this.resetProgress = typeof(this.resetProgressSettings) === 'object' &&
                Object.keys(this.resetProgressSettings).length > 0;
            this.resetProgressInterval = this.resetProgressSettings.interval;
            this.resetProgressStartDate = this.resetProgressSettings.startDate;
            this.resetProgressEndDate = this.resetProgressSettings.endDate;
            this.resetProgressMailText = this.resetProgressSettings.mailText;
        },
        store() {
            this.companionSuccess({
                info: this.$gettext('Die Einstellungen wurden übernommen.'),
            })
            this.storeCoursewareSettings({
                permission: this.currentPermissionLevel,
                progression: this.currentProgression,
                certificateSettings: this.generateCertificateSettings(),
                reminderSettings: this.generateReminderSettings(),
                resetProgressSettings: this.generateResetProgressSettings()
            });
        },
        generateCertificateSettings() {
            return this.makeCert ? {
                threshold: this.certThreshold,
                image: this.certImage
            } : {};
        },
        generateReminderSettings() {
            return this.sendReminders ? {
                interval: this.reminderInterval,
                startDate: this.reminderStartDate,
                endDate: this.reminderEndDate,
                mailText: this.reminderMailText
            } : {};
        },
        generateResetProgressSettings() {
            return this.resetProgress ? {
                interval: this.resetProgressInterval,
                startDate: this.resetProgressStartDate,
                endDate: this.resetProgressEndDate,
                mailText: this.resetProgressMailText
            } : {};
        },
        updateCertImage(file) {
            this.certImage = file.id;
        }
    },
    mounted() {
        this.initData();
    },
};
</script>
