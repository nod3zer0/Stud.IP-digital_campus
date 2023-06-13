<template>
    <div class="blubber_sideinfo responsive-hidden" v-if="thread['context-info']">
        <div class="context_info" v-html="thread['context-info']"></div>
    </div>
</template>

<script>
export default {
    props: {
        thread: {
            type: Object,
            required: true,
        },
    },
};
</script>
