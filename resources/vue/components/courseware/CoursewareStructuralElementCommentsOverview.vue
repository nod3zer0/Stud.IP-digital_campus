<template>
    <div class="cw-structural-element-comments-overview-wrapper">
        <table class="default">
            <caption>
                {{ $gettext('Seiten') }}
            </caption>
            <colgroup>
                <col style="width: 16em">
                <col style="width: 16em">
                <col style="width: 8em">
                <col class="responsive-hidden" style="width: 8em">
                <col class="responsive-hidden" style="width: 8em">
                <col style="width: 2em">
            </colgroup>
            <thead>
                <tr class="sortable">
                    <th :class="getSortClass('units')" @click="sort('units')">
                        <a href="#">{{ $gettext('Lernmaterial') }}</a>
                    </th>
                    <th :class="getSortClass('structural-elements')" @click="sort('structural-elements')">
                        <a href="#">{{ $gettext('Seite') }}</a>
                    </th>
                    <th></th>
                    <th class="responsive-hidden" :class="getSortClass('comments')" @click="sort('comments')">
                        <a href="#">{{ $gettext('Kommentare') }}</a>
                    </th>
                    <th class="responsive-hidden" :class="getSortClass('feedback')" @click="sort('feedback')">
                        <a href="#">{{ $gettext('Anmerkungen') }}</a>
                    </th>
                    <th class="actions">
                        {{ $gettext('Aktionen') }}
                    </th>
                </tr>
            </thead>
            <tbody v-if="filteredElements.length > 0">
                <tr v-for="element in filteredElements" :key="element.id">
                    <td>{{ element.unitName }}</td>
                    <td>
                        <a :href="element.url">
                            {{ element.attributes.title }}
                        </a>
                    </td>
                    <td></td>
                    <td class="responsive-hidden">
                        <a
                            href="#"
                            :title="$gettext('Kommentare anzeigen')"
                            @click.prevent="enableCommentsDialog(element)"
                        >
                            {{ $gettextInterpolate(
                                $ngettext('%{length} Kommentar', '%{length} Kommentare', element.comments.length),
                                {length: element.comments.length}
                            ) }}
                        </a>
                    </td>
                    <td class="responsive-hidden">
                        <a
                            v-if="element.attributes['can-edit'] && element.feedbacks.length > 0"
                            href="#"
                            :title="$gettext('Anmerkungen anzeigen')"
                            @click.prevent="enableFeedbackDialog(element)"
                        >
                            {{ $gettextInterpolate(
                                $ngettext('%{length} Anmerkung', '%{length} Anmerkungen', element.feedbacks.length),
                                {length: element.feedbacks.length}
                            ) }}
                        </a>
                        <template v-else>
                            -
                        </template>
                    </td>
                    <td class="actions">
                        <studip-action-menu
                            :items="getMenuItems(element)"
                            :context="$gettext('Seiten')"
                            @showComments="enableCommentsDialog(element)"
                            @showFeedback="enableFeedbackDialog(element)"
                        />
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr class="empty">
                    <td colspan="6">
                        {{ $gettext('Es wurden keine Kommentare oder Anmerkungen gefunden') }}
                    </td>
                </tr>
            </tbody>
        </table>
        <courseware-comments-overview-dialog
            v-if="showCommentsDialog"
            item-type="structuralElement"
            com-type="comment"
            :item="currentDialogElement"
            @close="closeCommentsDialog"
        />
        <courseware-comments-overview-dialog
            v-if="showFeedbackDialog"
            item-type="structuralElement"
            com-type="feedback"
            :item="currentDialogElement"
            @close="closeFeedbackDialog"
        />
    </div>
</template>

<script>
import CoursewareCommentsOverviewDialog from './CoursewareCommentsOverviewDialog.vue';
import commentsOverviewMixin from '@/vue/mixins/courseware/comments-overview-helper.js';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-comments-overview',
    components: {
        CoursewareCommentsOverviewDialog
    },
    mixins: [commentsOverviewMixin],
    data() {
        return {
            elementsWithRelations: [],
            currentDialogElement: null,
            showCommentsDialog: false,
            showFeedbackDialog: false,
            sortBy: 'units',
            sortASC: true,
        }
    },
    computed: {
        ...mapGetters({
            units: 'courseware-units/all',
            elements: 'courseware-structural-elements/all',
            elementComments: 'courseware-structural-element-comments/all',
            elementFeedbacks: 'courseware-structural-element-feedback/all',
            elementById: 'courseware-structural-elements/byId',
            unitById: 'courseware-units/byId',
            context: 'context',
            createdFilter: 'createdFilter',
            unitFilter: 'unitFilter'
        }),
        filteredElements() {
            let filteredElements = this.elementsWithRelations;
            if (this.unitFilter !== 'all') {
                filteredElements = filteredElements.filter(block => block.unit.id === this.unitFilter);
            }
            return this.sortElements(filteredElements);
        }
    },
    methods: {
        collectElementRelations() {
            this.elementsWithRelations = _.cloneDeep(this.elements);
            this.elementsWithRelations.forEach(element => {
                element.comments = this.elementComments.filter(comment => comment.relationships['structural-element'].data.id === element.id);
                element.comments.oneDay = 0;
                element.comments.oneWeek = 0;
                element.comments.forEach(comment => {
                    comment.created = this.calcCreated(comment.attributes.mkdate);
                    if (comment.created.oneDay) {
                        element.comments.oneDay++;
                    }
                    if (comment.created.oneWeek) {
                        element.comments.oneWeek++;
                    }
                });
                element.feedbacks = this.elementFeedbacks.filter(feedback => feedback.relationships['structural-element'].data.id === element.id);
                element.feedbacks.forEach(feedback => {
                    feedback.created = this.calcCreated(feedback.attributes.mkdate);
                });
                if (element.comments.length === 0 && element.feedbacks.length === 0) {
                    element.empty = true;
                } else {
                    element.unit = this.unitById({ id: element.relationships.unit.data.id });
                    const unitRoot = this.elementById({ id: element.unit.relationships['structural-element'].data.id});
                    element.unitName = unitRoot.attributes.title;
                    element.url = STUDIP.URLHelper.getURL(`dispatch.php/course/courseware/courseware/${element.unit.id}?cid=${this.context.id}#/structural_element/${element.id}`);
                }
            });
            this.elementsWithRelations = this.elementsWithRelations.filter(element => !element.empty);
        },
        enableCommentsDialog(element) {
            this.currentDialogElement = element;
            this.showCommentsDialog = true;
        },
        closeCommentsDialog() {
            this.showCommentsDialog = false;
            this.currentDialogElement = null;
            this.collectElementRelations();
        },
        enableFeedbackDialog(element) {
            this.currentDialogElement = element;
            this.showFeedbackDialog = true;
        },
        closeFeedbackDialog() {
            this.showFeedbackDialog = false;
            this.currentDialogElement = null;
            this.collectElementRelations();
        },
        getMenuItems(element) {
            let menuItems = [];
            menuItems.push({ id: 1, label: this.$gettext('Kommentare anzeigen'), icon: 'comment2', emit: 'showComments' });
            if (element.attributes['can-edit']) {
                menuItems.push({ id: 2, label: this.$gettext('Anmerkungen anzeigen'), icon: 'comment2', emit: 'showFeedback' });
            }

            return menuItems;
        },
        sort(sortBy) {
            if (this.sortBy === sortBy) {
                this.sortASC = !this.sortASC;
            } else {
                this.sortBy = sortBy;
            }
        },
        getSortClass(col) {
            if (col === this.sortBy) {
                return this.sortASC ? 'sortasc' : 'sortdesc';
            }
        },
        sortElements(elements) {
            switch (this.sortBy) {
                case 'units':
                    elements = elements.sort((a, b) => {
                        if (this.sortASC) {
                            return a.unitName < b.unitName ? -1 : 1;
                        } else {
                            return a.unitName > b.unitName ? -1 : 1;
                        }
                    });
                    break;
                case 'structural-elements':
                    elements = elements.sort((a, b) => {
                        if (this.sortASC) {
                            return a.attributes.title < b.attributes.title ? -1 : 1;
                        } else {
                            return a.attributes.title > b.attributes.title ? -1 : 1;
                        }
                    });
                    break;
                case 'comments':
                    elements = elements.sort((a, b) => {
                        if (this.sortASC) {
                            return a.comments.length - b.comments.length;
                        } else {
                            return  b.comments.length - a.comments.length;
                        }
                    });
                    break;
                case 'feedback':
                    elements = elements.sort((a, b) => {
                            if (this.sortASC) {
                                return a.feedbacks.length - b.feedbacks.length;
                            } else {
                                return  b.feedbacks.length - a.feedbacks.length;
                            }
                        });
                    break;
            }

            return elements;
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.collectElementRelations();
        });
    }
}
</script>
