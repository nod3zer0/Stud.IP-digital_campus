<template>
    <div class="cw-block cw-block-table-of-contents">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeText"
            @closeEdit="closeEdit"
        >
            <template #content>
                <div v-if="currentStyle !== 'tiles' && currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <ul
                    v-if="currentStyle === 'list-details' || currentStyle === 'list'"
                    :class="['cw-block-table-of-contents-' + currentStyle]"
                >
                    <li v-for="child in childElementsWithTasks" :key="child.id">
                        <router-link :to="'/structural_element/' + child.id">
                            <div class="cw-block-table-of-contents-title-box" :class="[child.attributes.payload.color]">
                                {{ child.attributes.title }}
                                <span v-if="child.attributes.purpose === 'task'"> | {{ child.solverName }}</span>
                                <p v-if="currentStyle === 'list-details'">{{ child.attributes.payload.description }}</p>
                            </div>
                        </router-link>
                    </li>
                </ul>
                <ul
                    v-if="currentStyle === 'tiles'" 
                    class="cw-block-table-of-contents-tiles cw-tiles"
                >
                    <li
                        v-for="child in childElementsWithTasks"
                        :key="child.id"
                        class="tile"
                        :class="[child.attributes.payload.color]"
                    >
                        <router-link :to="'/structural_element/' + child.id" :title="child.attributes.purpose === 'task' ? child.attributes.title + ' | ' + child.solverName : child.attributes.title"> 
                            <div
                                class="preview-image"
                                :class="[hasImage(child) ? '' : 'default-image']"
                                :style="getChildStyle(child)"
                            >
                                <div v-if="child.attributes.purpose === 'task'" class="overlay-text">{{ child.solverName }}</div>
                            </div>
                            <div class="description">
                                <header
                                    :class="[child.attributes.purpose !== '' ? 'description-icon-' + child.attributes.purpose : '']"
                                >
                                    {{ child.attributes.title || "–"}}
                                </header>
                                <div class="description-text-wrapper">
                                    <p>{{ child.attributes.payload.description }}</p>
                                </div>
                                <footer>
                                    {{ countChildChildren(child) }}
                                    <translate
                                        :translate-n="countChildChildren(child)"
                                        translate-plural="Seiten"
                                    >
                                       Seite
                                    </translate>
                                </footer>
                            </div>
                        </router-link>
                    </li>
                </ul>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Layout</translate>
                        <select v-model="currentStyle">
                            <option value="list"><translate>Liste</translate></option>
                            <option value="list-details"><translate>Liste mit Beschreibung</translate></option>
                            <option value="tiles"><translate>Kacheln</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Inhaltsverzeichnis-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-table-of-contents-block',
    components: {
        CoursewareDefaultBlock,
    },
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
            context: 'context',
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            groupById: 'status-groups/byId',
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
            this.childElements.forEach(element => {
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
        }
    },
    mounted() {
        this.initCurrentData();
        this.childElements.forEach(element => {
            if (element.relationships.task.data) {
                const taskId = element.relationships.task.data.id;
                try {
                    this.loadTask({
                        taskId: taskId,
                    });
                } catch(error) {
                    console.debug(error);
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
        closeEdit() {
            this.initCurrentData();
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
        getChildStyle(child) {
            let url = child.relationships?.image?.meta?.['download-url'];

            if(url) {
                return {'background-image': 'url(' + url + ')'};
            } else {
                return {};
            }
        },
        countChildChildren(child) {
            return this.childrenById(child.id).length + 1;
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },

        getSolverName(taskId) {
            const task = this.taskById({ id: taskId});
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
        }
    },
};
</script>
