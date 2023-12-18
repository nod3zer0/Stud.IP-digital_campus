<template>
    <section class="cw-block-comments" :class="[emptyComments ? 'cw-block-comments-empty' : '']">
        <span class="sr-only" aria-live="polite">{{ srMessage }}</span>
        <div class="cw-block-features-content">
            <div class="cw-block-comments-items" v-show="!emptyComments" ref="commentsRef">
                <courseware-talk-bubble
                    v-for="comment in comments"
                    :key="comment.id"
                    :payload="buildPayload(comment)"
                    @delete="deleteComment(comment)"
                />
            </div>
            <div class="cw-block-comment-create">
                <textarea v-model="createComment" :placeholder="placeHolder" spellcheck="true"></textarea>
                <button class="button" @click="postComment"><translate>Senden</translate></button>
            </div>
        </div>
    </section>
</template>

<script>
import CoursewareTalkBubble from '../layouts/CoursewareTalkBubble.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-comments',
    components: {
        CoursewareTalkBubble,
    },
    props: {
        block: Object,
    },
    data() {
        return {
            createComment: '',
            placeHolder: this.$gettext('Stellen Sie eine Frage oder kommentieren Sie...'),
            srMessage: ''
        };
    },
    computed: {
        ...mapGetters({
            relatedUser: 'users/related',
            userId: 'userId',
            getComments: 'courseware-block-comments/related',
        }),
        comments() {
            const parent = {
                type: this.block.type,
                id: this.block.id,
            };

            return this.getComments({ parent, relationship: 'comments' });
        },
        emptyComments() {
            if (this.comments === null || this.comments.length === 0) {
                return true;
            }

            return false;
        }
    },
    methods: {
        ...mapActions({
            createComments: 'courseware-block-comments/create',
            loadRelatedComments: 'courseware-block-comments/loadRelated',
            deleteBlockComment: 'courseware-block-comments/delete'
        }),
        async loadComments() {
            const parent = {
                type: this.block.type,
                id: this.block.id,
            };
            await this.loadRelatedComments({
                parent,
                relationship: 'comments',
                options: {
                    include: 'user',
                },
            });
        },
        async postComment() {
            this.updateSrMessage(this.$gettext('Kommentar gesendet'));
            const data = {
                attributes: {
                    comment: this.createComment
                },
                relationships: {
                    block: {
                        data: {
                            id: this.block.id,
                            type: this.block.type
                        }
                    }
                }
            };

            await this.createComments(data);
            this.loadComments();
            this.createComment = '';
        },
        deleteComment(comment) {
            this.deleteBlockComment({id: comment.id, type: comment.type });
        },
        buildPayload(comment) {
            const commenter = this.relatedUser({
                parent: { id: comment.id, type: comment.type },
                relationship: 'user',
            });

            const payload = {
                id: comment.id,
                own: comment.relationships.user.data.id === this.userId,
                content: comment.attributes.comment,
                chdate: comment.attributes.chdate,
                mkdate: comment.attributes.mkdate,
                user_id: commenter.id,
                user_formatted_name: commenter.attributes['formatted-name'],
                username: commenter?.attributes?.username ?? '',
                user_avatar: commenter.meta.avatar.small,
            };

            return payload;
        },
        updateSrMessage(message) {
            this.srMessage = '';
            this.srMessage = message;
        }
    },
    mounted() {
        this.loadComments();
    },
    updated() {
        let ref = this.$refs["commentsRef"];
        ref.scrollTop = ref.scrollHeight;
    },
    watch: {
        comments() {
            if (this.comments && this.comments.length > 0) {
                this.$emit('hasComments');
            }
        }
    }
};
</script>
