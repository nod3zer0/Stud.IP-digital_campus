<template>
    <div>
        <BlubberPanel :threadId="threadId" :search="search" v-if="threadId" />

        <MountingPortal mountTo="#blubber-search-widget" name="sidebar-blubber-search">
            <BlubberSearchWidget :search="search" />
        </MountingPortal>
        <MountingPortal mountTo="#blubber-threads-widget" name="sidebar-blubber-threads">
            <BlubberThreadsWidget
                :hasMoreThreads="hasMoreThreads"
                :threadId="threadId"
                :threads="threads"
                @load-more-threads="onLoadMoreThreads"
                @select-thread="onSelectThread"
                class="blubber_threads_widget"
            />
        </MountingPortal>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import BlubberPanel from './Panel.vue';
import BlubberSearchWidget from './SearchWidget.vue';
import BlubberThreadsWidget from './ThreadsWidget.vue';

export default {
    props: {
        initialThreadId: {
            type: String,
            required: true,
        },
        search: {
            type: String,
            default: '',
        },
    },
    components: {
        BlubberPanel,
        BlubberSearchWidget,
        BlubberThreadsWidget,
    },
    data: () => ({
        handleSelectBlubberThread: null,
        threadId: null,
    }),
    computed: {
        ...mapGetters({
            hasMoreThreads: 'studip/blubber/hasMoreThreads',
            threads: 'studip/blubber/threads',
        }),
    },
    methods: {
        ...mapActions({
            fetchThreads: 'studip/blubber/fetchThreads',
        }),
        onLoadMoreThreads() {
            this.fetchThreads({ search: this.search, more: true });
        },
        onSelectThread(threadId, changeHistory = true) {
            if (changeHistory) {
                const url = window.STUDIP.URLHelper.getURL(`dispatch.php/blubber/index/${threadId}`);
                window.history.pushState({ threadId }, '', url);
            }
            this.threadId = threadId;
        },
    },
    async beforeMount() {
        await this.fetchThreads({ search: this.search });
        this.onSelectThread(this.initialThreadId, false);

        this.handleSelectBlubberThread = (threadId) => {
            this.onSelectThread(threadId);
            this.fetchThreads({ search: this.search });
        };
        this.globalOn('studip:select-blubber-thread', this.handleSelectBlubberThread);
    },
    created() {
        window.addEventListener('popstate', (event) => {
            if ('threadId' in event.state) {
                this.onSelectThread(event.state.threadId, false);
            }
        });
    },
    beforeDestroy() {
        this.globalOff('studip:select-blubber-thread', this.handleSelectBlubberThread);
    },
};
</script>
