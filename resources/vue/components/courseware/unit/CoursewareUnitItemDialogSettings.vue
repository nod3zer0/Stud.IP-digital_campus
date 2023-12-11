<template>
    <studip-dialog
        :title="$gettext('Einstellungen')"
        :confirmText="$gettext('Speichern')"
        confirmClass="accept"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="600"
        width="500"
        @close="$emit('close')"
        @confirm="storeSettings"
    >
        <template v-slot:dialogContent>
            <form v-if="!loadSettings" class="default" @submit.prevent="">
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
                <fieldset v-if="certificatesRemindersEnabled">
                    <legend>{{ $gettext('Zertifikate') }}</legend>
                    <label>
                        <input type="checkbox" v-model="makeCert">
                        <span>
                            {{ $gettext('Zertifikat bei Erreichen einer Fortschrittsgrenze versenden') }}
                        </span>
                        <studip-tooltip-icon :text="$gettext('Erreicht eine Person in diesem Lernmaterial den ' +
                            'hier eingestellten Fortschritt, so erhält Sie ein PDF-Zertifikat per E-Mail.')"/>
                    </label>
                    <label v-if="makeCert">
                        <span>
                            {{ $gettext('Erforderlicher Fortschritt (in Prozent)') }}
                        </span>
                        <input type="number" min="1" max="100" v-model="certThreshold">
                    </label>
                    <label v-if="makeCert">
                        <span>
                            {{ $gettext('Bild wählen') }}
                        </span>
                        <studip-tooltip-icon :text="$gettext('Wählen Sie hier ein Bild, das auf ' +
                            'dem Zertifikat in der linken oberen Ecke in Originalgröße angezeigt wird. Wenn Sie das ' +
                            'Bild als Hintergrund des Zertifikats verwenden möchten, muss es nur die passende Größe ' +
                            'haben, um die ganze Seite auszufüllen.')"></studip-tooltip-icon>
                        <courseware-file-chooser :isImage="true" v-model="certImage" @selectFile="updateCertImage" />
                    </label>
                    <label v-if="makeCert" class="col-3">
                        <span>
                            {{ $gettext('Titel') }}
                            <input type="text" v-model="certTitle">
                        </span>
                    </label>
                    <label v-if="makeCert">
                        <span>
                            {{ $gettext('Text') }}
                            <studip-wysiwyg v-model="certText"></studip-wysiwyg>
                        </span>
                    </label>
                    <button v-if="makeCert" type="button" class="button" @click.prevent="previewCertificate">
                        {{ $gettext('Vorschau') }}
                    </button>
                </fieldset>
                <fieldset v-if="certificatesRemindersEnabled">
                    <legend>
                        {{ $gettext('Erinnerungen') }}
                    </legend>
                    <label>
                        <input type="checkbox" v-model="sendReminders">
                        <span>
                            {{ $gettext('Erinnerungsnachrichten an alle Teilnehmenden schicken') }}
                        </span>
                        <studip-tooltip-icon :text="$gettext('Hier können periodisch Nachrichten an alle ' +
                            'Teilnehmenden verschickt werden, um z.B. an die Bearbeitung dieses Lernmaterials ' +
                            'zu erinnern.')"/>
                    </label>

                    <label v-if="sendReminders">
                        <span>
                            {{ $gettext('Zeitraum zwischen Erinnerungen') }}
                        </span>
                        <select v-model="reminderInterval">
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
                            <input type="date" v-model="reminderStartDate">
                        </span>
                    </label>
                    <label v-if="sendReminders" class="col-3">
                        <span>
                            {{ $gettext('Letztmalige Erinnerung am') }}
                            <input type="date" v-model="reminderEndDate">
                        </span>
                    </label>
                    <label v-if="sendReminders">
                        <span>
                            {{ $gettext('Text der Erinnerungsmail') }}
                            <studip-wysiwyg v-model="reminderMailText"></studip-wysiwyg>
                        </span>
                    </label>
                </fieldset>
                <fieldset v-if="certificatesRemindersEnabled">
                    <legend>
                        {{ $gettext('Fortschritt') }}
                    </legend>
                    <label>
                        <input type="checkbox" v-model="resetProgress">
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
                        <select v-model="resetProgressInterval">
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
                            <input type="date" v-model="resetProgressStartDate">
                        </span>
                    </label>
                    <label v-if="resetProgress" class="col-3">
                        <span>
                            {{ $gettext('Letztmaliges Zurücksetzen am') }}
                            <input type="date" v-model="resetProgressEndDate">
                        </span>
                    </label>
                    <label v-if="resetProgress">
                        <span>
                            {{ $gettext('Text der Rücksetzungsmail') }}
                            <studip-wysiwyg v-model="resetProgressMailText"></studip-wysiwyg>
                        </span>
                    </label>
                </fieldset>
            </form>
            <studip-progress-indicator v-else :description="$gettext('Lade Einstellungen…')"/>

        </template>
    </studip-dialog>
</template>

<script>
import CoursewareFileChooser from '../layouts/CoursewareFileChooser.vue';
import StudipProgressIndicator from '../../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-item-dialog-settings',
    components: {
        CoursewareFileChooser,
        StudipProgressIndicator,
    },
    props: {
        unit: Object
    },
    data() {
        return {
            currentInstance: null,
            loadSettings: false,
            currentRootLayout: 'default',
            currentPermissionLevel: '',
            currentProgression: 0,
            makeCert: false,
            certThreshold: 0,
            certImage: '',
            certTitle: '',
            certText: '',
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
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            instanceById: 'courseware-instances/byId',
            userIsTeacher: 'userIsTeacher'
        }),
        instance() {
            if (this.inCourseContext) {
                return this.instanceById({id: 'course_' + this.context.id + '_' + this.unit.id});
            } else {
                return this.instanceById({id: 'user_' + this.context.id + '_' + this.unit.id});
            }

        },
        inCourseContext() {
            return this.context.type === 'courses';
        },
        certificatesRemindersEnabled() {
            return this.getStudipConfig('COURSEWARE_CERTIFICATES_ENABLE');
        }
    },
    methods: {
        ...mapActions({
            loadInstance: 'loadInstance',
            storeCoursewareSettings: 'storeCoursewareSettings',
            companionSuccess: 'companionSuccess'
        }),
        async loadUnitInstance() {
            const context = {type: this.context.type, id: this.context.id, unit: this.unit.id};
            await this.loadInstance(context);
        },
        initData() {
            this.currentRootLayout = this.currentInstance.attributes['root-layout'];
            this.currentPermissionLevel = this.currentInstance.attributes['editing-permission-level'];
            this.currentProgression = this.currentInstance.attributes['sequential-progression'] ? '1' : '0';
            this.certSettings = this.currentInstance.attributes['certificate-settings'];
            this.makeCert = typeof(this.certSettings) === 'object' &&
                Object.keys(this.certSettings).length > 0;
            this.certThreshold = this.certSettings.threshold;
            this.certImage = this.certSettings.image;
            this.certTitle = this.certSettings.title;
            this.certText = this.certSettings.text;
            this.reminderSettings = this.currentInstance.attributes['reminder-settings'];
            this.sendReminders = typeof(this.reminderSettings) === 'object' &&
                Object.keys(this.reminderSettings).length > 0;
            this.reminderInterval = this.reminderSettings.interval;
            this.reminderStartDate = this.reminderSettings.startDate;
            this.reminderEndDate = this.reminderSettings.endDate;
            this.reminderMailText = this.reminderSettings.mailText;
            this.resetProgressSettings = this.currentInstance.attributes['reset-progress-settings'];
            this.resetProgress = typeof(this.resetProgressSettings) === 'object' &&
                Object.keys(this.resetProgressSettings).length > 0;
            this.resetProgressInterval = this.resetProgressSettings.interval;
            this.resetProgressStartDate = this.resetProgressSettings.startDate;
            this.resetProgressEndDate = this.resetProgressSettings.endDate;
            this.resetProgressMailText = this.resetProgressSettings.mailText;
        },
        storeSettings() {
            this.$emit('close');
            this.currentInstance.attributes['root-layout'] = this.currentRootLayout;
            this.currentInstance.attributes['editing-permission-level'] = this.currentPermissionLevel;
            this.currentInstance.attributes['sequential-progression'] = this.currentProgression;
            this.currentInstance.attributes['certificate-settings'] = this.generateCertificateSettings();
            this.currentInstance.attributes['reminder-settings'] = this.generateReminderSettings();
            this.currentInstance.attributes['reset-progress-settings'] = this.generateResetProgressSettings();
            this.storeCoursewareSettings({
                instance: this.currentInstance,
            });
        },
        generateCertificateSettings() {
            return this.certificatesRemindersEnabled && this.makeCert ? {
                threshold: this.certThreshold,
                image: this.certImage,
                title: this.certTitle,
                text: this.certText
            } : {};
        },
        generateReminderSettings() {
            return this.certificatesRemindersEnabled && this.sendReminders ? {
                interval: this.reminderInterval,
                startDate: this.reminderStartDate,
                endDate: this.reminderEndDate,
                mailText: this.reminderMailText
            } : {};
        },
        generateResetProgressSettings() {
            return this.certificatesRemindersEnabled && this.resetProgress ? {
                interval: this.resetProgressInterval,
                startDate: this.resetProgressStartDate,
                endDate: this.resetProgressEndDate,
                mailText: this.resetProgressMailText
            } : {};
        },
        updateCertImage(file) {
            this.certImage = file.id;
        },
        previewCertificate() {
            window.open(STUDIP.URLHelper.getURL('jsonapi.php/v1/courseware-units/' + this.unit.id + '/certificate'));
        }
    },
    async mounted() {
        this.loadSettings = true;
        await this.loadUnitInstance();
        this.loadSettings = false;
        this.currentInstance = this.instance;
        this.initData();
    }
}
</script>
