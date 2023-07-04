<template>
    <div>
        <table class="default">
            <caption>
                <div class="caption-container">
                    <div>
                        <studip-icon shape="folder-public-full" :size="30" alt="" />
                        <span>{{ caption }}</span>
                    </div>
                </div>
            </caption>
            <thead>
                <tr>
                    <th>
                        <label>
                            <input type="checkbox" ref="checkAll" :checked="allChecked" @change="onCheckedAllChange" />
                            <span class="sr-only">{{ $gettext('Alle Bilder auswählen') }}</span>
                        </label>
                    </th>
                    <th>{{ $gettext('Name') }}</th>
                    <th>{{ $gettext('Format') }}</th>
                    <th>{{ $gettext('Größe') }}</th>
                    <th>{{ $gettext('Abmessungen') }}</th>
                </tr>
            </thead>
            <tbody v-if="paged.length">
                <ImagesListItem
                    :stock-image="stockImage"
                    v-for="stockImage in paged"
                    :key="stockImage.id"
                    :is-checked="checkedImages.includes(stockImage.id)"
                    @checked="$emit('checked', stockImage)"
                    @search="(query) => $emit('search', query)"
                    @select="$emit('select', stockImage)"
                />
            </tbody>
            <tbody v-else>
                <tr>
                    <td colspan="5">
                        <span v-if="query.length">{{
                            $gettext('Zu diesem Suchbegriff konnten keine Bilder gefunden werden.')
                        }}</span>
                        <span v-else>{{ $gettext('Es konnten keine Bilder gefunden werden.') }}</span>
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="paged.length">
                <tr>
                    <td colspan="5">
                        <button
                            type="button"
                            class="button"
                            @click="showConfirmDelete = true"
                            :disabled="!checkedImages.length"
                        >
                            {{ $gettext('Löschen') }}
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <studip-dialog
            v-if="showConfirmDelete"
            :title="
                this.$ngettext(
                    'Ausgewähltes Bild unwideruflich löschen?',
                    'Ausgewählte Bilder unwideruflich löschen?',
                    checkedImages.length
                )
            "
            :question="
                $ngettext(
                    'Möchten Sie das ausgewählte Bild wirklich löschen?',
                    'Möchten Sie die ausgewählten Bilder wirklich löschen?',
                    checkedImages.length
                )
            "
            height="200"
            width="450"
            @confirm="onDelete"
            @close="showConfirmDelete = false"
        ></studip-dialog>
    </div>
</template>

<script>
import ImagesListItem from './ImagesListItem.vue';
import { mapActions } from 'vuex';

export default {
    props: {
        checkedImages: {
            type: Array,
            required: true,
        },
        perPage: {
            type: Number,
            default: 10,
        },
        page: {
            type: Number,
            default: 1,
        },
        query: {
            type: String,
            default: '',
        },
        stockImages: {
            type: Array,
            required: true,
        },
    },
    components: {
        ImagesListItem,
    },
    data: () => ({
        latestMkdate: null,
        showConfirmDelete: false,
    }),
    computed: {
        allChecked() {
            return this.paged.length && this.paged.every(({ id }) => this.checkedImages.includes(id));
        },
        caption() {
            const n = this.stockImages.length;
            return this.$gettextInterpolate(this.$ngettext('%{ n } Bild gefunden', '%{ n } Bilder gefunden', n), { n });
        },
        paged() {
            return this.stockImages.slice((this.page - 1) * this.perPage, this.page * this.perPage);
        },
        totalItems() {
            return this.stockImages.length;
        },
    },
    methods: {
        ...mapActions({ deleteStockImage: 'studip/stockImages/delete' }),
        checkAll() {
            this.paged
                .filter(({ id }) => !this.checkedImages.includes(id))
                .forEach((image) => this.$emit('checked', image));
        },
        checkNone() {
            this.paged
                .filter(({ id }) => this.checkedImages.includes(id))
                .forEach((image) => this.$emit('checked', image));
        },
        onCheckedAllChange() {
            this.allChecked ? this.checkNone() : this.checkAll();
        },
        onDelete() {
            const checkedImages = [...this.checkedImages];
            this.showConfirmDelete = false;
            this.checkNone();
            Promise.allSettled(checkedImages.map((id) => this.deleteStockImage(id))).then(() => {
                this.revalidatePage();
            });
        },
        revalidatePage() {
            if (this.totalItems < this.page * this.perPage) {
                this.$emit('open-page', Math.ceil(this.totalItems / this.perPage));
            }
        },
    },
    watch: {
        checkedImages({ length }) {
            this.$refs.checkAll.indeterminate = 0 < length && length < this.paged.length;
        },
    },
};
</script>

<style scoped>
table.default {
    height: 100%;
}

.caption-container div {
    display: flex;
    gap: 0.5em;
}

thead th input {
    margin-inline: 1em;
}

thead th:first-child {
    width: 3em;
}
</style>
