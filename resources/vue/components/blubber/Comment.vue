<template>
    <li :class="{ 'talk-bubble-own-post': ownComment }" class="talk-bubble-wrapper">
        <div v-if="!ownComment" class="talk-bubble-avatar">
            <a :href="userProfileURL" :title="comment.author['formatted-name']">
                <img :src="commentAvatar" />
            </a>
        </div>
        <div class="talk-bubble" :class="{ editing }">
            <div class="talk-bubble-content">
                <header v-if="!ownComment" class="talk-bubble-header">
                    <a :href="userProfileURL">{{ comment.author['formatted-name'] }}</a>
                </header>
                <div class="talk-bubble-talktext">
                    <template v-if="!editing">
                        <div ref="html" v-html="comment['content-html']" class="html"></div>
                        <div class="talk-bubble-footer">
                            <span class="talk-bubble-talktext-time"><studip-date-time :timestamp="commentMkdate"
                                    :relative="true"></studip-date-time></span>
                            <a href="#" v-if="comment['is-writable']" @click.prevent.stop="editComment" class="edit_comment"
                                :title="$gettext('Bearbeiten')">
                                <studip-icon shape="edit" :size="14" />
                            </a>
                            <a href="#" @click.prevent="answerComment" class="answer_comment"
                                :title="$gettext('Hierauf antworten')">
                                <studip-icon shape="reply" :size="14" />
                            </a>

                        </div>
                    </template>
                    <div v-else class="talk-bubble-edit">
                    <textarea
                        v-model="localText"
                        ref="textarea"
                        @input="setTextareaSize"
                        @focus="setTextareaSize"
                        @keydown.enter.exact.prevent="saveComment"
                        @keyup.escape.exact="doneEditing"
                    ></textarea>
                        <button @click="saveComment" :title="$gettext('Speichern')">
                            <studip-icon shape="accept" />
                        </button>
                        <button @click="doneEditing" :title="$gettext('Abbrechen')">
                            <studip-icon shape="decline" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </li>
</template>

<script>
export default {
    name: 'BlubberComment',
    data: () => ({
        localText: '',
        commentWidth: 0,
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
        ownComment() {
            return this.comment.isMine();
        }
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
        setTextareaSize() {
            const textarea = this.$refs.textarea;
            textarea.style.width = this.commentWidth + 'px';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
    },
    mounted() {
        this.resetContent();
        this.$nextTick(() => {
            window.STUDIP.Markup.element(this.$refs.html);
            this.commentWidth = this.$refs.html.offsetWidth;
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
