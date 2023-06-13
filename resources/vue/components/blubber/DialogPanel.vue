<template>
    <div>
        <BlubberPanel :threadId="threadId" :search="search" v-if="threadId" />
    </div>
</template>

<script>
import BlubberPanel from './Panel.vue';

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
    },
    data: () => ({
        threadId: null,
    }),
    methods: {
        onSelectThread(threadId) {
            this.threadId = threadId;
        },
    },
    beforeMount() {
        this.onSelectThread(this.initialThreadId, false);
    },
};
</script>
