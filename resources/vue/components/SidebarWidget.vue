<template>
    <div class="sidebar-widget">
        <div class="sidebar-widget-header" v-if="title">
            {{ title }}
        </div>
        <div class="sidebar-widget-content">
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
}
</script>
