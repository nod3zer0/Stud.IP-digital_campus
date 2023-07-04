<template>
    <studip-dialog
        width="890"
        :title="$gettext('Bild auswählen')"
        :closeText="$gettext('Schließen')"
        height="640"
        @close="onClose"
    >
        <template v-slot:dialogContent>
            <Selector :stock-images="stockImages" @select="onSelectImage" />
        </template>
    </studip-dialog>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import Selector from './Selector.vue';

export default {
    data: () => ({
        query: '',
        selectedImage: null,
    }),
    components: { Selector },
    computed: {
        ...mapGetters({
            stockImages: 'stock-images/all',
            stockImagesMeta: 'stock-images/lastMeta',
        }),
    },
    methods: {
        ...mapActions({
            loadStockImages: 'stock-images/loadWhere',
        }),
        async fetchStockImages() {
            const loadLimit = 30;
            await this.loadPage(0, loadLimit);
            const total = this.stockImagesMeta.page.total;

            const pages = [];
            for (let page = 1; page * loadLimit < total; page++) {
                pages.push(this.loadPage(page * loadLimit, loadLimit));
            }

            return Promise.all(pages);
        },
        loadPage(offset, limit) {
            return this.loadStockImages({
                filter: {},
                options: {
                    'page[offset]': offset,
                    'page[limit]': limit,
                },
            });
        },
        onClose() {
            this.$emit('close');
        },
        onSelectImage(stockImage) {
            this.$emit('select', stockImage);
        },
    },
    created() {
        this.fetchStockImages();
    },
};
</script>
