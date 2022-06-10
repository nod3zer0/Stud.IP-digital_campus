<template>
    <div class="cw-manager-link-selector">
        <courseware-manager-element
            v-if="ownId !== null"
            type="link"
            :currentElement="ownElement"
            :elementsOnly="true"
            @selectElement="setOwnId"
            @loadSelf="loadSelf"
        />
    </div>
</template>

<script>
import CoursewareManagerElement from './CoursewareManagerElement.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-manager-link-selector',
    components: {
        CoursewareManagerElement,
        CoursewareCompanionBox,
    },

    data() {
        return {
            ownCoursewareInstance: {},
            ownId: null,
            ownElement: {},
        }
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',
        }),
    },

    methods: {
        ...mapActions({
            loadAnotherCourseware: 'courseware-structure/loadAnotherCourseware',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
        }),
        async loadOwnCourseware() {
            this.ownCoursewareInstance = await this.loadAnotherCourseware({ id: this.userId, type: 'users' });
            if (this.ownCoursewareInstance !== null) {
                await this.setOwnId(this.ownCoursewareInstance.relationships.root.data.id);
            } else {
                this.ownId = '';
            }
        },
        async setOwnId(target) {
            this.ownId = target;
            const options = {
                include: 'children'
            };
            await this.loadStructuralElementById({ id: this.ownId, options });
            this.initOwn();
        },
        initOwn() {
            this.ownElement = this.structuralElementById({ id: this.ownId });
        },
        reloadElement() {
            this.$emit('reloadElement');
        },
        loadSelf(data) {
            this.$emit('loadSelf', data);
        },
    },

    async mounted() {
        await this.loadOwnCourseware();
    },
}
</script>