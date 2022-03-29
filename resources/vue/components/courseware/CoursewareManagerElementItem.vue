<template>
    <div class="cw-manager-element-item-wrapper">
        <a
            v-if="!sortChapters"
            href="#"
            class="cw-manager-element-item"
            :class="[inserter ? 'cw-manager-element-item-inserter' : '']"
            :title="inserter ? $gettextInterpolate('%{ elementTitle } verschieben', {elementTitle: element.attributes.title}) : element.attributes.title"
            @click="clickItem">
                {{ element.attributes.title }}
                <span v-if="task" class="cw-manager-element-item-solver-name">| {{ solverName }}</span>
        </a>
        <div 
            v-else
            class="cw-manager-element-item cw-manager-element-item-sorting"
        >
            {{ element.attributes.title }}
            <div v-if="sortChapters" class="cw-manager-element-item-buttons">
                <a v-if="canMoveUp" href="#" @click="moveUp" :title="$gettext('Element nach oben verschieben')">
                    <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveUp}" shape="arr_2up" size="16" role="clickable" />
                </a>
                <a v-if="canMoveDown" href="#" @click="moveDown" :title="$gettext('Element nach unten verschieben')">
                    <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveDown}" shape="arr_2down" size="16" role="clickable" />
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-manager-element-item',
    props: {
        element: Object,
        inserter: Boolean,
        sortChapters: Boolean,
        type: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean
    },
    computed: {
        ...mapGetters({
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            groupById: 'status-groups/byId',
        }),
        isTask() {
            return this.element.attributes.purpose === 'task';
        },
        task() {
            if (this.element.relationships.task.data) {
                return this.taskById({
                    id: this.element.relationships.task.data.id,
                });
            }

            return null;
        },
        solver() {
            if (this.task) {
                const solver = this.task.relationships.solver.data;
                if (solver.type === 'users') {
                    return this.userById({ id: solver.id });
                }
                if (solver.type === 'status-groups') {
                    return this.groupById({ id: solver.id });
                }
            }

            return null;
        },
        solverName() {
            if (this.solver) {
                if (this.solver.type === 'users') {
                    return this.solver.attributes['formatted-name'];
                }
                if (this.solver.type === 'status-groups') {
                    return this.solver.attributes.name;
                }
            }

            return '';
        },
    },
    methods: {
        ...mapActions({
            loadTask: 'loadTask',
        }),
        clickItem() {
            if (this.sortChapters) {
                return false;
            }
            if (this.inserter) {
                this.$emit('insertElement', {element: this.element, source: this.type});
            } else {
                this.$emit('selectChapter', this.element.id);
            }
        },
        moveUp() {
            if (this.sortChapters) {
                this.$emit('moveUp', this.element.id);
            }
        },
        moveDown() {
            if (this.sortChapters) {
                this.$emit('moveDown', this.element.id);
            }
        },
        loadElementTask() {
            if (this.element.relationships.task.data && this.task === undefined) {
                this.loadTask({
                    taskId: this.element.relationships.task.data.id,
                });
            }
        }
    },
    mounted() {
        this.loadElementTask();
    },
};
</script>
