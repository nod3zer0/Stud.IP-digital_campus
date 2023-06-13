<template>
    <SidebarWidget :title="$gettext('Konversationen')" @scroll="handleScroll">
        <template #content>
            <div class="scrollable_area blubber_thread_widget" :class="{ scrolled }" ref="scrollableArea">
                <transition-group name="blubberthreadwidget-list" tag="ol">
                    <li
                        v-for="thread in sortedThreads"
                        :key="thread.id"
                        :data-thread_id="thread.id"
                        :class="threadClasses(thread)"
                        :data-unseen_comments="thread.unseenComments"
                        @click.prevent="changeActiveThread(thread.id)"
                    >
                        <a :href="link(thread.id)">
                            <div class="avatar" :style="{ backgroundImage: 'url(' + thread.avatar + ')' }"></div>
                            <div class="info">
                                <div class="name">
                                    {{ thread.name }}
                                </div>
                                <studip-date-time
                                    :timestamp="threadLatestActivity(thread)"
                                    :relative="true"
                                ></studip-date-time>
                            </div>
                        </a>
                    </li>
                    <li class="more" v-if="hasMoreThreads" key="more" ref="more">
                        <studip-asset-img file="loading-indicator.svg" width="20"></studip-asset-img>
                    </li>
                </transition-group>
            </div>
        </template>

        <template #actions>
            <a :href="urlCompose" data-dialog="width=600;height=300">
                <studip-icon shape="add" class="text-bottom" />
            </a>
        </template>
    </SidebarWidget>
</template>
<script>
import SidebarWidget from '../SidebarWidget.vue';

export default {
    props: {
        hasMoreThreads: {
            type: Boolean,
            default: false,
        },
        threadId: {
            type: String,
            default: null,
        },
        threads: {
            type: Array,
            default: () => [],
        },
    },
    data: () => ({
        scrolled: false,
    }),
    components: {
        SidebarWidget,
    },
    computed: {
        sortedThreads() {
            const sorted = [...this.threads].sort((a, b) => {
                return (
                    new Date(b['latest-activity']) - new Date(a['latest-activity'])
                    || new Date(b['mkdate']) - new Date(a['mkdate'])
                    || b.name.localeCompare(a.name)
                );
            });

            return sorted;
        },
        urlCompose() {
            return STUDIP.URLHelper.getURL('dispatch.php/blubber/compose');
        },
    },
    methods: {
        changeActiveThread(threadId) {
            this.$emit('select-thread', threadId);
        },
        handleScroll({ element }) {
            this.scrolled = element.scrollTop > 0;

            if (
                this.hasMoreThreads
                && element.scrollTop >= element.scrollHeight - this.$refs.more.clientHeight - element.clientHeight
            ) {
                this.$emit('load-more-threads');
            }
        },
        link(thread_id) {
            return STUDIP.URLHelper.getURL(`dispatch.php/blubber/index/${thread_id}`);
        },
        threadClasses(thread) {
            return {
                active: thread.id === this.threadId,
                unseen: thread.unseenComments > 0,
            };
        },
        threadLatestActivity(thread) {
            return new Date(thread['latest-activity']) / 1000;
        },
    },
};
</script>
