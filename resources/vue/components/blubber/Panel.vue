<template>
    <StudipProgressIndicator
        class="cw-loading-indicator-content"
        :description="$gettext('Lade Kommentare...')"
        v-if="!doneFetching"
    />

    <div class="blubber_panel" v-else-if="thread">
        <div id="blubber_stream_container">
            <BlubberThread
                ref="thread"
                :comments="comments"
                :thread="thread"
                :moreCommentsDown="moreCommentsDown(thread.id)"
                :moreCommentsUp="moreCommentsUp(thread.id)"
                :uploadProgress="uploadProgress"
                @load-older="onLoadOlder"
                @load-newer="onLoadNewer"
                @add-posting="onAddPosting"
                @change-comment="onChangeComment"
                @pick-files="onPickFiles"
                @remove-comment="onRemoveComment"
                @subscribe-thread="onSubscribeThread"
            ></BlubberThread>
        </div>
        <BlubberSideInfo :thread="thread" />
    </div>
</template>

<script>
import axios from 'axios';
import { mapActions, mapGetters } from 'vuex';
import BlubberSideInfo from './SideInfo.vue';
import BlubberThread from './Thread.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';

export default {
    props: {
        search: {
            type: String,
            default: '',
        },
        threadId: {
            type: String,
            required: true,
        },
    },
    components: {
        BlubberSideInfo,
        BlubberThread,
        StudipProgressIndicator,
    },
    data: () => ({
        doneFetching: false,
        selectHandler: null,
        uploadProgress: 0,
    }),
    computed: {
        ...mapGetters({
            getComments: 'studip/blubber/comments',
            moreCommentsDown: 'studip/blubber/moreNewer',
            moreCommentsUp: 'studip/blubber/moreOlder',
            getThread: 'studip/blubber/thread',
        }),
        comments() {
            return this.threadId ? this.getComments(this.threadId) : [];
        },
        thread() {
            return this.threadId ? this.getThread(this.threadId) : null;
        },
    },
    methods: {
        ...mapActions({
            changeThreadSubscription: 'studip/blubber/changeThreadSubscription',
            createComment: 'studip/blubber/createComment',
            destroyComment: 'studip/blubber/destroyComment',
            fetchThread: 'studip/blubber/fetchThread',
            loadNewerComments: 'studip/blubber/loadNewerComments',
            loadOlderComments: 'studip/blubber/loadOlderComments',
            markThreadAsSeen: 'studip/blubber/markThreadAsSeen',
            setThreadAsDefault: 'studip/blubber/setThreadAsDefault',
            updateComment: 'studip/blubber/updateComment',
        }),

        onAddPosting(content) {
            this.createComment({ id: this.threadId, content })
                .then(() => {
                    this.$refs.thread.scrollDown();
                })
                .catch((error) => {
                    STUDIP.Report.error(
                        this.$gettext('Fehler beim Erstellen Ihres Kommentars'),
                        [
                            this.$gettext(
                                'Ein technisches Problem verhindert, dass Ihr Kommentar erstellt werden konnte.'
                            ),
                        ].join(' ')
                    );
                    console.error('Could not create comment', error);
                });
        },
        onChangeComment(comment) {
            this.updateComment(comment);
        },
        onLoadNewer() {
            this.loadNewerComments({ id: this.threadId, search: this.search });
        },
        onLoadOlder() {
            this.loadOlderComments({ id: this.threadId, search: this.search });
        },
        onPickFiles(files) {
            const data = new FormData();
            for (let i in files) {
                if (files[i].size > 0) {
                    data.append(`file_${i}`, files[i], files[i].name.normalize());
                }
            }

            axios({
                method: 'POST',
                url: STUDIP.URLHelper.getURL('dispatch.php/blubber/upload_files'),
                data,
                onUploadProgress: ({ loaded, position, lengthComputable, total }) => {
                    if (lengthComputable) {
                        this.uploadProgress = Math.ceil(((loaded || position) / total) * 100);
                    }
                },
            })
                .then(({ data }) => {
                    this.onAddPosting(data.inserts.join(' '));
                })
                .catch((error) => {
                    STUDIP.Report(
                        this.$gettext('Fehler beim Hochladen'),
                        [
                            this.$gettext(
                                'Ein technisches Problem verhindert, dass Ihre Datei hochgeladen werden konnte.'
                            ),
                        ].join(' ')
                    );
                    console.error('Could not upload files', error);
                })
                .finally(() => {
                    this.uploadProgress = 0;
                });
        },
        onRemoveComment(comment) {
            this.destroyComment(comment);
        },
        onSubscribeThread(subscribeThread) {
            this.changeThreadSubscription({ id: this.threadId, subscribe: subscribeThread });
        },
        selectThread(threadId) {
            this.doneFetching = false;
            this.fetchThread({ id: threadId, search: this.search }).then(() => {
                this.doneFetching = true;
                this.markThreadAsSeen({ id: threadId });
                this.thread.unseenComments = 0;
                this.setThreadAsDefault({ id: threadId });
            });
        },
    },
    mounted() {
        this.selectThread(this.threadId);
    },
    watch: {
        threadId(newId) {
            this.selectThread(newId);
        },
    },
};
</script>
