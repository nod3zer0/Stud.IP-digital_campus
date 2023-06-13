<template>
    <div class="sidebar-widget">
        <div class="sidebar-widget-header" v-if="title">
            {{ title }}
            <div class="actions" v-if="this.$slots.actions">
                <slot name="actions"></slot>
            </div>
        </div>
        <div class="sidebar-widget-content" ref="scrollable">
            <slot name="content" />
        </div>
    </div>
</template>

<script>
export default {
    name: 'sidebar-widget',
    props: {
        title: String,
    },
    methods: {
        handleScroll(event) {
            this.$emit('scroll', { event, element: this.$refs.scrollable });
        },
    },
    mounted() {
        this.handleDebouncedScroll = _.debounce(this.handleScroll, 100);
        this.$refs.scrollable.addEventListener('scroll', this.handleDebouncedScroll);
    },
    beforeDestroy() {
        this.$refs.scrollable.removeEventListener('scroll', this.handleDebouncedScroll);
    },
};
</script>

<style scoped>
.actions {
    float: right;
}
</style>
