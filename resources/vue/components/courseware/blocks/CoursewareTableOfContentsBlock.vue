<template>
    <div class="cw-block cw-block-table-of-contents">
        <courseware-default-block :block="block" :canEdit="canEdit" :isTeacher="isTeacher" :preview="true"
            @showEdit="initCurrentData" @storeEdit="storeText" @closeEdit="initCurrentData">
            <template #content>
                <div v-if="childElementsWithTasks.length > 0">
                    <div v-if="currentStyle !== 'tiles' && currentTitle !== ''" class="cw-block-title">
                        {{ currentTitle }}
                    </div>
                    <ul v-if="currentStyle === 'list-details' || currentStyle === 'list'"
                        :class="['cw-block-table-of-contents-' + currentStyle]">
                        <li v-for="child in childElementsWithTasks" :key="child.id">
                            <router-link :to="'/structural_element/' + child.id">
                                <div class="cw-block-table-of-contents-title-box" :class="[child.attributes.payload.color]">
                                    {{ child.attributes.title }}
                                    <span v-if="child.attributes.purpose === 'task'"> | {{ child.solverName }}</span>
                                    <p v-if="currentStyle === 'list-details'">
                                        {{ child.attributes.payload.description }}
                                    </p>
                                </div>
                            </router-link>
                        </li>
                    </ul>
                    <ul v-if="currentStyle === 'tiles'" class="cw-block-table-of-contents-tiles cw-tiles">
                        <li v-for="child in childElementsWithTasks" :key="child.id">
                            <router-link :to="'/structural_element/' + child.id" :title="child.attributes.purpose === 'task'
                                    ? child.attributes.title + ' | ' + child.solverName
                                    : child.attributes.title
                                ">
                                <courseware-tile tag="div" :color="child.attributes.payload.color"
                                    :title="child.attributes.title" :imageUrl="getChildImageUrl(child)">
                                    <template #description>
                                        {{ child.attributes.payload.description }}
                                    </template>
                                    <template #footer>
                                        {{
                                            $gettextInterpolate(
                                                $ngettext(
                                                    '%{length} Seite',
                                                    '%{length} Seiten',
                                                    countChildChildren(child)
                                                ),
                                                { length: countChildChildren(child) })
                                        }}
                                    </template>
                                </courseware-tile>
                            </router-link>
                        </li>
                    </ul>
                </div>
                <courseware-companion-box v-if="viewMode === 'edit' && childElementsWithTasks.length === 0" :msgCompanion="$gettext(
                    'Es sind noch keine Unterseiten vorhanden. ' +
                    'Sobald Sie weitere Unterseiten anlegen, erscheinen diese automatisch hier im Inhaltsverzeichnis.'
                )
                    " mood="pointing" />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Überschrift') }}
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        {{ $gettext('Layout') }}
                        <select v-model="currentStyle">
                            <option value="list">{{ $gettext('Liste') }}</option>
                            <option value="list-details">{{ $gettext('Liste mit Beschreibung') }}</option>
                            <option value="tiles">{{ $gettext('Kacheln') }}</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>{{ $gettext('Informationen zum Inhaltsverzeichnis-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import BlockComponents from './block-components.js';
import CoursewareTile from '../layouts/CoursewareTile.vue';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-table-of-contents-block',
    mixins: [blockMixin],
    components: Object.assign(BlockComponents, { CoursewareTile }),
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentStyle: '',
        };
    },
    computed: {
        ...mapGetters({
            childrenById: 'courseware-structure/children',
            structuralElementById: 'courseware-structural-elements/byId',
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            groupById: 'status-groups/byId',
            viewMode: 'viewMode',
        }),
        structuralElement() {
            return this.structuralElementById({ id: this.$route.params.id });
        },
        childElements() {
            return this.childrenById(this.structuralElement.id).map((id) => this.structuralElementById({ id }));
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        style() {
            return this.block?.attributes?.payload?.style;
        },
        childElementsWithTasks() {
            let children = [];
            this.childElements.forEach((element) => {
                if (element.relationships.task.data) {
                    let solverName = this.getSolverName(element.relationships.task.data.id);
                    if (solverName) {
                        element.solverName = solverName;
                        children.push(element);
                    }
                } else {
                    children.push(element);
                }
            });

            return children;
        },
    },
    mounted() {
        this.initCurrentData();
        this.childElements.forEach((element) => {
            if (element.relationships.task.data) {
                const taskId = element.relationships.task.data.id;
                try {
                    this.loadTask({
                        taskId: taskId,
                    });
                } catch (error) {
                    // nothing to do here
                }
            }
        });
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadTask: 'loadTask',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentStyle = this.style;
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.style = this.currentStyle;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        getChildImageUrl(child) {
            return child.relationships?.image?.meta?.['download-url'];
        },
        countChildChildren(child) {
            return this.childrenById(child.id).length + 1;
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },

        getSolverName(taskId) {
            const task = this.taskById({ id: taskId });
            if (task === undefined) {
                return false;
            }
            const solver = task.relationships.solver.data;
            if (solver.type === 'users') {
                const user = this.userById({ id: solver.id });

                return user.attributes['formatted-name'];
            }
            if (solver.type === 'status-groups') {
                const group = this.groupById({ id: solver.id });

                return group.attributes.name;
            }

            return false;
        },
    },
};
</script>
<style scoped lang="scss">
@import '../../../../assets/stylesheets/scss/courseware/blocks/table-of-contents.scss';
</style>
