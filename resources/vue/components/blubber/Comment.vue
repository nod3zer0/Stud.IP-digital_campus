<template>
    <li :class="commentClass">
        <a
            :href="userProfileURL"
            class="avatar"
            :title="comment.author['formatted-name']"
            :style="{ backgroundImage: 'url(' + commentAvatar + ')' }"
        ></a>
        <div class="content" :class="{ editing }">
            <a :href="userProfileURL" class="name">{{ comment.author['formatted-name'] }}</a>
            <div ref="html" v-html="comment['content-html']" class="html"></div>
            <textarea
                ref="textarea"
                class="edit"
                v-model="localText"
                @keydown.enter.exact.prevent="saveComment"
                @keyup.escape.exact="doneEditing"
            ></textarea>
        </div>
        <div class="time">
            <studip-date-time :timestamp="commentMkdate" :relative="true"></studip-date-time>
            <a
                href=""
                v-if="comment['is-writable']"
                @click.prevent.stop="editComment"
                class="edit_comment"
                :title="$gettext('Bearbeiten.')"
            >
                <studip-icon shape="edit" :size="14" role="inactive"></studip-icon>
            </a>
            <a href="" @click.prevent="answerComment" class="answer_comment" :title="$gettext('Hierauf antworten.')">
                <studip-icon shape="export" :size="14" role="inactive"></studip-icon>
            </a>
        </div>
    </li>
</template>
<script>
export default {
    name: 'BlubberComment',
    data: () => ({
        localText: '',
    }),
    props: {
        comment: {
            type: Object,
            required: true,
        },
        editing: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        commentAvatar() {
            return this.comment.author?.avatar.small ?? '';
        },
        commentClass() {
            return this.comment.isMine() ? 'mine' : 'theirs';
        },
        commentMkdate() {
            return new Date(this.comment.mkdate) / 1000;
        },
        userProfileURL() {
            const user_id = this.comment.author.id;
            const username = this.comment.author.username;
            if (username) {
                return window.STUDIP.URLHelper.getURL('dispatch.php/profile', { username });
            } else {
                return window.STUDIP.URLHelper.getURL('dispatch.php/profile/extern/' + user_id);
            }
        },
    },
    methods: {
        answerComment() {
            this.$emit('answer-comment', this.comment);
        },
        editComment() {
            this.$emit('edit-comment', this.comment);
            this.resetContent();
            this.focusContent();
        },
        doneEditing() {
            this.resetContent();
            this.$emit('edit-comment', null);
        },
        focusContent() {
            this.$nextTick(() => {
                const textarea = this.$refs.textarea;
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            });
        },
        resetContent() {
            this.localText = this.comment.content;
        },
        saveComment() {
            if (this.localText.trim().length > 0) {
                this.$emit('change-comment', { ...this.comment, content: this.localText });
            } else {
                this.$emit('remove-comment', this.comment);
            }
        },
    },
    mounted() {
        this.resetContent();
        this.$nextTick(() => {
            window.STUDIP.Markup.element(this.$refs.html);
        });
    },
    watch: {
        editing(newValue, oldValue) {
            if (!oldValue && newValue) {
                this.focusContent();
            }
        },
    },
};
</script>
