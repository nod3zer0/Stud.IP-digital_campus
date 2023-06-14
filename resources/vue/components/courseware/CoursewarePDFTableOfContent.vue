<template>
    <li class="cw-pdf-toc-item">
        <a href="#" @click.prevent="tocPageNav(item.dest)">
            <strong v-if="item.bold">{{ item.title }}</strong>
            <span v-else>{{ item.title }}</span>
        </a>
        <template v-if="item.items">
            <ul class="cw-pdf-toc-sub-list">
                <courseware-pdf-toc-item v-for="(item, index) in item.items" :item="item" :key="index" @tocPageNav="tocPageNav"></courseware-pdf-toc-item>
            </ul>
        </template>
    </li>
</template>

<script>
export default {
    name: 'courseware-pdf-toc-item',
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    methods: {
        tocPageNav(dest) {
            this.$emit('tocPageNav', dest);
        }
    },
}
</script>
