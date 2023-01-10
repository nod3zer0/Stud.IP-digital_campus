<template>
    <div class="cw-unit-items">
        <ul v-if="hasUnits" class="cw-tiles">
            <courseware-unit-item v-for="unit in units" :key="unit.id" :unit="unit"/>
        </ul>
        <template v-if="!hasUnits && inCourseContext">
            <div v-if="userIsTeacher" class="cw-contents-overview-teaser">
                <div class="cw-contents-overview-teaser-content">
                    <header>{{ $gettext('Lernmaterialien') }}</header>
                    <p>
                        {{ $gettext('Mit Courseware können Sie interaktive, multimediale Lerninhalte erstellen und nutzen. ' +
                                    'Die Lerninhalte lassen sich hierarchisch unterteilen und können aus Texten, Videosequenzen, ' +
                                    'Aufgaben, Kommunikationselementen und einer Vielzahl weiterer Elemente bestehen. ' +
                                    'Fertige Lerninhalte können exportiert und in andere Kurse oder andere Installationen importiert werden. ' +
                                    'Courseware ist nicht nur für digitale Formate geeignet, sondern kann auch genutzt werden, ' +
                                    'um klassische Präsenzveranstaltungen mit Online-Anteilen zu ergänzen. Formate wie integriertes Lernen ' +
                                    '(Blended Learning) lassen sich mit Courseware ideal umsetzen. Kollaboratives Lernen kann dank Schreibrechtevergabe ' +
                                    'und dem Einsatz von Courseware in Studiengruppen realisiert werden.') }}
                    </p>
                    <button class="button" @click="setShowUnitAddDialog(true)">
                        {{ $gettext('Neues Lernmaterial anlegen') }}
                    </button>
                </div>
            </div>
            <courseware-companion-box
                v-else
                :msgCompanion="$gettext('Es wurden leider noch keine Lernmaterialien angelegt.')"
                mood="sad"
            />
        </template>
        <div v-if="!hasUnits && !inCourseContext" class="cw-contents-overview-teaser">
            <div class="cw-contents-overview-teaser-content">
                <header>{{ $gettext('Ihre persönlichen Lernmaterialien') }}</header>
                <p>{{ $gettext('Erstellen und verwalten Sie hier Ihre eigenen persönlichen Lernmaterialien in Form von ePorfolios,' +
                               'Vorlagen für Veranstaltungen oder einfach nur persönliche Inhalte für das Studium.' +
                               'Entwickeln Sie Ihre eigenen (Lehr-)Materialien für Studium oder die Lehre und teilen diese mit anderen Nutzenden.') }}</p>
                <button class="button" @click="setShowUnitAddDialog(true)">
                    {{ $gettext('Neues Lernmaterial anlegen') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareUnitItem from './CoursewareUnitItem.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-items',
    components: {
        CoursewareCompanionBox,
        CoursewareUnitItem,
    },
    computed: {
        ...mapGetters({
            context: 'context',
            coursewareUnits: 'courseware-units/all',
            userIsTeacher: 'userIsTeacher'
        }),
        units() {
            return this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.context.id) ?? [];
        },
        hasUnits() {
            return this.units.length > 0;
        },
        inCourseContext() {
            return this.context.type === 'courses';
        }
    },
    methods: {
        ...mapActions({
            setShowUnitAddDialog: 'setShowUnitAddDialog',
        }),
    }
}
</script>
