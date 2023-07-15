<template>
    <div>
        <ImagesPagination :per-page="perPage" :stock-images="filteredStockImages" v-model="page">
            <ImagesList
                :checked-images="checkedImages"
                :page="page"
                :per-page="perPage"
                :stock-images="filteredStockImages"
                @checked="onCheckboxChange"
                @open-page="(newPage) => (page = newPage)"
                @search="onSearch"
                @select="onSelectImage"
            />
        </ImagesPagination>
        <MountingPortal mountTo="#stock-images-widget" name="sidebar-stock-images">
            <SearchWidget :query="query" @search="onSearch" />
            <OrientationFilterWidget v-model="filters" />
            <ColorFilterWidget v-model="filters" />
            <ActionsWidget @initiateUpload="onUploadDialogShow" />
        </MountingPortal>
        <EditDialog
            :stock-image="selectedImage"
            :suggested-tags="suggestedTags"
            @confirm="onEditDialogConfirm"
            @cancel="selectedImage = null"
        />
        <UploadDialog
            :show="showUpload"
            :suggested-tags="suggestedTags"
            @confirm="onUploadDialogConfirm"
            @cancel="showUpload = false"
        />
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import ActionsWidget from './ActionsWidget.vue';
import ColorFilterWidget from './ColorFilterWidget.vue';
import EditDialog from './EditDialog.vue';
import ImagesList from './ImagesList.vue';
import ImagesPagination from './ImagesPagination.vue';
import OrientationFilterWidget from './OrientationFilterWidget.vue';
import SearchWidget from './SearchWidget.vue';
import UploadDialog from './UploadDialog.vue';
import { searchFilterAndSortImages } from './filters.js';

export default {
    components: {
        ActionsWidget,
        ColorFilterWidget,
        EditDialog,
        ImagesList,
        ImagesPagination,
        OrientationFilterWidget,
        SearchWidget,
        UploadDialog,
    },
    data: () => ({
        checkedImages: [],
        filters: {
            orientation: 'any',
            colors: [],
        },
        page: 1,
        perPage: 10,
        query: '',
        selectedImage: null,
        showUpload: false,
    }),
    computed: {
        ...mapGetters({
            stockImages: 'stock-images/all',
            stockImagesMeta: 'stock-images/lastMeta',
            suggestedTags: 'studip/stockImages/allTags',
        }),
        filteredStockImages() {
            return searchFilterAndSortImages(this.stockImages, this.query, this.filters);
        },
    },
    methods: {
        ...mapActions({
            createStockImage: 'studip/stockImages/create',
            loadStockImages: 'stock-images/loadWhere',
            updateStockImage: 'studip/stockImages/update',
        }),
        onCheckboxChange(image) {
            if (!this.checkedImages.includes(image.id)) {
                this.checkedImages.push(image.id);
            } else {
                this.checkedImages = this.checkedImages.filter((id) => id !== image.id);
            }
        },
        onEditDialogConfirm(attributes) {
            this.updateStockImage({ stockImage: this.selectedImage, attributes });
            this.selectedImage = null;
        },
        onSearch(query) {
            this.query = query;
        },
        onSelectImage(image) {
            this.selectedImage = image;
        },
        onUploadDialogConfirm({ file, metadata }) {
            this.createStockImage([file, metadata])
                .then(() => {
                    this.showUpload = false;
                })
                .catch((error) => {
                    console.error('Could not create stock image', error);
                });
        },
        onUploadDialogShow() {
            this.showUpload = true;
        },
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
    },
    created() {
        this.fetchStockImages();
    },
    watch: {
        query(newQuery, oldQuery) {
            if (newQuery !== oldQuery && this.page !== 1) {
                this.page = 1;
            }
        },
        filters(newFilters, oldFilters) {
            if (!_.isEqual(newFilters, oldFilters) && this.page !== 1) {
                this.page = 1;
            }
        },
    },
};
</script>
