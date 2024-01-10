<template>
    <div class="feedback-entry-box" v-show="!loadingUser">
        <div class="feedback-entry-box-avatar">
            <img :src="avatarUrl" />
        </div>
        <div class="feedback-entry-box-content">
            <h4>{{ title }}</h4>
            <studip-five-stars :amount="parseInt(entry.attributes.rating)" :size="16" />
            <p>{{ entry.attributes.comment }}</p>
        </div>
        <div>
            <button v-if="canEdit" class="as-link" @click="$emit('edit')">
                <studip-icon shape="edit" />
            </button>
            <button v-if="canDelete" class="as-link" @click="deleteEntry">
                <studip-icon shape="trash" />
            </button>
        </div>
    </div>
</template>
<script>
import StudipFiveStars from './StudipFiveStars.vue';

import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'feedback-entry-box',
    components: {
        StudipFiveStars,
    },
    props: {
        entry: {
            type: Object,
            required: true,
        },
        name: {
            type: String,
            required: false,
        },
        canEdit: {
            type: Boolean,
            default: false,
        },
        canDelete: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            loadingUser: false,
        };
    },
    computed: {
        ...mapGetters({
            getUser: 'users/byId',
        }),
        title() {
            return this.name ?? this.userName;
        },
        userName() {
            return this.user?.attributes?.['formatted-name'] ?? 'Anonym';
        },
        user() {
            if (this.anonymous) {
                return null;
            }
            const userId = this.entry.relationships?.author?.data?.id;
            return this.getUser({ id: userId });
        },
        avatarUrl() {
            return (
                this.user?.meta?.avatar?.small ?? STUDIP.URLHelper.getURL('assets/images/avatars/user/nobody_small.webp', {}, true)
            );
        },
        anonymous() {
            return this.entry.attributes.anonymous;
        }
    },
    methods: {
        ...mapActions({
            loadUser: 'users/loadById',
        }),
        getEntryUser() {
            this.loadingUser = true;
            const userId = this.entry.relationships?.author?.data?.id;
            const user = this.getUser({ id: userId });
            if (user) {
                this.loadingUser = false;
                return;
            }

            this.loadUser({ id: userId }).then(() => {
                this.loadingUser = false;
            });
        },
        deleteEntry() {
            this.$emit('delete', { id: this.entry.id });
        },
    },
    mounted() {
        if (!this.anonymous) {
            this.getEntryUser();
        }
    },
};
</script>
