<template>
    <div>
        <div v-if="structureLoadingState === 'done'">
            <public-courseware-structural-element
                :canVisit="true"
                :structural-element="selected"
                :ordered-structural-elements="orderedStructuralElements"
                @select="selectStructuralElement"
            ></public-courseware-structural-element>
        </div>
        <studip-progress-indicator
            v-if="structureLoadingState === 'loading'"
            class="loading-indicator-content"
            :description="$gettext('Lade Lernmaterial...')"
        />
        <courseware-companion-box
            v-if="structureLoadingState === 'error'"
            mood="sad"
            :msgCompanion="loadingErrorMessage"
        />
        <courseware-companion-box
            v-if="wrongPassword"
            mood="sad"
            :msgCompanion="passwordMessage"
        />
        <form v-if="!isAuthenticated" class="default" @submit.prevent="">
            <label>
                <translate>Passwort</translate>
                <input type="password" v-model="password">
            </label>
            <button class="button" @click="submitPassword">
                <translate>Absenden</translate>
            </button>
        </form>
     </div>
</template>

<script>
import PublicCoursewareStructuralElement from './structural-element/PublicCoursewareStructuralElement.vue';
import CoursewareCompanionBox from './layouts/CoursewareCompanionBox.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
     components: {
        PublicCoursewareStructuralElement,
        CoursewareCompanionBox,
        StudipProgressIndicator,
     },
    data() {
        return {
            selected: null,
            structureLoadingState: 'idle',
            loadingErrorStatus: null,
            wrongPassword: false,
            password: '',
            passwordMessage: this.$gettext('Das eingegebene Passwort ist leider falsch.'),
            userIsTeacher: false,
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            isAuthenticated: 'isAuthenticated',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElements: 'courseware-structural-elements/all',
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',

            orderedStructuralElements: 'courseware-structure/ordered',
            childrenById: 'courseware-structure/children',
        }),
        loadingErrorMessage() {
            switch (this.loadingErrorStatus) {
                case 404:
                    return this.$gettext('Die Seite konnte nicht gefunden werden.');
                case 403:
                    return this.$gettext('Diese Seite steht Ihnen leider nicht zur Verfügung.');
                default:
                    return this.$gettext('Beim Laden der Seite ist ein Fehler aufgetreten.');
            }
        },
        selectedId() {
            return this.$route.params?.id;
        }
     },
        methods: {
        ...mapActions({
            loadElements: 'courseware-structural-elements/loadAll',
            buildStructure: 'courseware-structure/build',
            loadStructuralElement: 'loadStructuralElement',
            validatePassword: 'validatePassword',
        }),
        async selectStructuralElement(id) {
            if (!id) {
                return;
            }

            this.loadingErrorStatus = null;
            this.structureLoadingState = 'loading';
            try {
                await this.loadStructuralElement(id);
            } catch(error) {
                this.loadingErrorStatus = error.status;
                this.structureLoadingState = 'error';
                return;
            }
            this.structureLoadingState = 'done';
            this.selected = this.structuralElementById({ id });
        },

        submitPassword() {
            this.validatePassword(this.password);
            if (this.isAuthenticated) {
                this.$router.push({ path: '/', replace: true});
            } else {
                this.wrongPassword = true;
            }
        }
    },
    async mounted() {
        await this.loadElements();
        await this.buildStructure();
        const selectedId = this.$route.params?.id;
        await this.selectStructuralElement(selectedId);
    },

    watch: {
        $route(to) {
            const selectedId = to.params?.id;
            this.selectStructuralElement(selectedId);
            window.scrollTo({ top: 0 });
        },
        password() {
            this.wrongPassword = false;
        },
    },

}
</script>
