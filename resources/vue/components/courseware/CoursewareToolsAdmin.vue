<template>
    <div class="cw-tools cw-tools-admin">
        <form class="default" @submit.prevent="">
            <fieldset>
                <legend><translate>Allgemeine Einstellungen</translate></legend>
                <label>
                    <span><translate>Art der Inhaltsabfolge</translate></span>
                    <select class="size-s" v-model="currentProgression">
                        <option value="0"><translate>Frei</translate></option>
                        <option value="1"><translate>Sequentiell</translate></option>
                    </select>
                </label>

                <label>
                    <span><translate>Editierberechtigung für Tutor/-innen</translate></span>
                    <select class="size-s" v-model="currentPermissionLevel">
                        <option value="dozent"><translate>Nein</translate></option>
                        <option value="tutor"><translate>Ja</translate></option>
                    </select>
                </label>
            </fieldset>
        </form>
        <button class="button" @click="store"><translate>Übernehmen</translate></button>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'cw-tools-admin',
    data() {
        return {
            currentPermissionLevel: '',
            currentProgression: '',
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
            this.currentPermissionLevel = this.courseware.attributes['editing-permission-level'];
            this.currentProgression = this.courseware.attributes['sequential-progression'] ? '1' : '0';
        },
        store() {
            this.companionSuccess({
                info: this.$gettext('Die Einstellungen wurden übernommen.'),
            })
            this.storeCoursewareSettings({
                permission: this.currentPermissionLevel,
                progression: this.currentProgression,
            });
        },
    },
    mounted() {
        this.initData();
    },
};
</script>
