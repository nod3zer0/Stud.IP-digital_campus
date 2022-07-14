<template>
    <li class="cw-activity-item">
        <p v-if="item.username" class="cw-activity-item-user">
            <a :href="userUrl"><studip-icon role="inactive" shape="headache" />{{ item.username }}</a>
        </p>
        <p v-if="item.date" class="cw-activity-item-date">
            <studip-icon role="inactive" shape="timetable" />{{ item.date }}
        </p>
        <p class="cw-activity-item-element">
            <a :href="linkUrl" :title="item.complete_breadcrumb"><studip-icon role="inactive" :shape="shape" />{{ item.element_breadcrumb }}</a>
        </p>
        <p v-if="text" class="cw-activity-item-text">
            <span v-html="text"></span>
        </p>
    </li>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';

export default {
    name: 'courseware-activity-item',
    components: {
        StudipIcon,
    },
    props: {
        item: Object,
    },
    computed: {
        text() {
            if (this.item.content == null || this.item.content == '') {
                return this.item.text;
            }

            switch (this.item.type) {
                case 'interacted':
                    return this.item.username + ' commented: ' + this.item.content; //TODO: Localization
                case 'answered':
                    return this.item.username + ' added feedback: ' + this.item.content; //TODO: Localization
                default:
                    return this.item.text;
            }
        },

        userUrl() {
            return STUDIP.URLHelper.base_url + 'dispatch.php/profile?username=' + this.item.username;
        },

        linkUrl() {
            return STUDIP.URLHelper.base_url + 'dispatch.php/course/courseware/?cid=' + this.item.context_id + '#/structural_element/' + this.item.element_id;
        },
        shape() {
            switch (this.item.type) {
                case 'interacted':
                    return 'item';
                case 'answered':
                    return 'support';
                case 'created':
                    return 'add';
                case 'edited':
                    return 'edit';
                default:
                    return 'question-circle-full';
            }
        },
    },
};
</script>
