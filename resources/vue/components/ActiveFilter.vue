<template>
    <span class="activefilter">
        <slot></slot>
        <button
            @click="onRemoveActiveFilter"
            type="button"
            :title="$gettextInterpolate($gettext('Filter \'%{name}\' entfernen'), { name })"
        >
            <StudipIcon class="text-bottom" shape="decline" role="presentation" alt="" />
        </button>
    </span>
</template>

<script>
import StudipIcon from './StudipIcon.vue';
export default {
    props: {
        name: {
            type: String,
            required: true,
        },
    },
    methods: {
        onRemoveActiveFilter() {
            this.$emit('remove');
        },
    },
};
</script>

<style scoped>
.activefilter {
    align-items: center;
    background-color: var(--content-color-20);
    border: solid thin var(--black);
    display: flex;
    gap: 4px;
    justify-content: space-between;
    margin: 3px;
    padding: 5px;
    padding-inline-end: 0;
    white-space: nowrap;
}

button {
    align-items: center;
    background-color: var(--content-color-20);
    border: none;
    display: flex;
    justify-content: center;
    margin-inline: 0;
    padding-inline-start: 4px;
}
</style>
