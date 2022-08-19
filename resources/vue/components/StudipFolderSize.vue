<template>
    <span>{{ folder_size }}</span>
</template>

<script>
export default {
    name: 'studip-folder-size',
    props: {
        object_count: Number
    },
    computed: {
        folder_size() {
            if (this.object_count < 1) {
                return '';
            }

            return this.$gettextInterpolate(
                this.$ngettext('%{count} Objekt', '%{count} Objekte', this.object_count),
                {count: this.object_count}
            );
        }
    }
}
</script>
