<template>
    <div>
        <StudipPagination
            :style="{ visibility: totalItems <= perPage ? 'hidden' : 'visible' }"
            :currentOffset="offset"
            :totalItems="totalItems"
            :itemsPerPage="perPage"
            @updateOffset="onUpdateOffset"
        />
        <slot></slot>
        <StudipPagination
            :style="{ visibility: totalItems <= perPage ? 'hidden' : 'visible' }"
            :currentOffset="offset"
            :totalItems="totalItems"
            :itemsPerPage="perPage"
            @updateOffset="onUpdateOffset"
        />
    </div>
</template>

<script>
import StudipPagination from '../StudipPagination.vue';

export default {
    components: { StudipPagination },
    model: {
        prop: 'page',
        event: 'change',
    },
    props: {
        stockImages: {
            type: Array,
            required: true,
        },
        page: {
            type: Number,
            required: true,
        },
        perPage: {
            type: Number,
            default: 10,
        },
    },
    computed: {
        offset() {
            return this.page - 1;
        },
        totalItems() {
            return this.stockImages.length;
        },
    },
    methods: {
        onUpdateOffset(offset) {
            this.$emit('change', offset + 1);
        },
    },
};
</script>
