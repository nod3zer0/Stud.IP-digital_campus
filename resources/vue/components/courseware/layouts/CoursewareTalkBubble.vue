<template>
    <div :class="{ 'cw-talk-bubble-own-post': payload.own }" class="cw-talk-bubble-wrapper">
        <div v-if="!payload.own" class="cw-talk-bubble-avatar">
            <img :src="payload.user_avatar" />
        </div>
        <div class="cw-talk-bubble">
            <div class="cw-talk-bubble-content">
                <header v-if="!payload.own" class="cw-talk-bubble-header">
                    <a :href="userProfileUrl">{{ payload.user_formatted_name }}</a>
                </header>
                <div class="cw-talk-bubble-talktext">
                    <span>{{ payload.content }}</span>
                    <div class="cw-talk-bubble-footer">
                        <span class="cw-talk-bubble-talktext-time"><iso-date :date="payload.chdate" /></span>
                        <button v-if="userIsTeacher || payload.own" :title="$gettext('Löschen')"
                            @click="showDeleteDialog = true">
                            <studip-icon shape="trash" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <studip-dialog v-if="showDeleteDialog" :title="$gettext('Eintrag löschen')"
            :question="$gettext('Möchten Sie diesen Eintrag löschen?')" height="180" width="360" @confirm="deletePost"
            @close="closeDeleteDialog">
        </studip-dialog>
    </div>
</template>

<script>
import IsoDate from './IsoDate.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-talk-bubble',
    components: { IsoDate },
    props: {
        payload: Object,
    },
    data() {
        return {
            showDeleteDialog: false
        }
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher'
        }),
        userProfileUrl() {
            const username = this.payload.username;
            return STUDIP.URLHelper.getURL('dispatch.php/profile', { username });
        }
    },
    methods: {
        closeDeleteDialog() {
            this.showDeleteDialog = false;
        },
        deletePost() {
            this.closeDeleteDialog();
            this.$emit('delete');
        }
    }
};
</script>
