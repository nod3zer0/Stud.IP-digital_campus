<template>
    <div>
        <table class="default documents"
               :data-folder_id="topfolder.folder_id"
               data-shiftcheck>
            <caption>
                <div class="caption-container">
                    <div v-if="breadcrumbs && !table_title">
                        <a v-if="breadcrumbs[0]" :href="breadcrumbs[0].url" :title="$gettext('Zum Hauptordner')">
                            <studip-icon shape="folder-home-full"
                                         role="clickable"
                                         class="text-bottom"
                                         size="30"></studip-icon>
                            <span v-if="breadcrumbs.length === 1">
                                {{ breadcrumbs[0].name }}
                            </span>
                        </a>
                        <span v-for="breadcrumb in breadcrumbs.slice(1)" :key="breadcrumb.folder_id">
                            /<a :href="breadcrumb.url">
                                {{ breadcrumb.name }}
                            </a>
                        </span>
                    </div>
                    <div v-if="table_title">{{table_title}}</div>
                </div>
                <div v-if="topfolder.description" style="font-size: small" v-html="topfolder.description"></div>
            </caption>

            <colgroup>
                <col v-if="show_bulk_actions" style="width: 30px">
                <col style="width: 36px">
                <col>
                <col style="width: 100px" class="responsive-hidden">
                <col v-if="showdownloads" style="width: 100px" class="responsive-hidden">
                <col style="width: 150px" class="responsive-hidden">
                <col style="width: 120px" class="responsive-hidden">
                <col v-for="(name, index) in additionalColumns"
                     :key="index"
                     data-filter-ignore
                     class="responsive-hidden">
                <col style="width: 64px">
            </colgroup>
            <thead>
                <tr class="sortable">
                    <th v-if="show_bulk_actions" data-sort="false">
                        <studip-proxy-checkbox
                            v-model="selectedIds"
                            :total="allIds"
                        ></studip-proxy-checkbox>
                    </th>
                    <th @click="sort('mime_type')" :class="sortClasses('mime_type')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Typ') }}
                        </a>
                    </th>
                    <th @click="sort('name')" :class="sortClasses('name')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Name') }}
                        </a>
                    </th>
                    <th @click="sort('size')" class="responsive-hidden" :class="sortClasses('size')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Größe') }}
                        </a>
                    </th>
                    <th v-if="showdownloads" @click="sort('downloads')" class="responsive-hidden" :class="sortClasses('downloads')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Downloads') }}
                        </a>
                    </th>
                    <th class="responsive-hidden" @click="sort('author_name')" :class="sortClasses('author_name')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Autor/-in') }}
                        </a>
                    </th>
                    <th class="responsive-hidden" @click="sort('chdate')" :class="sortClasses('chdate')">
                        <a href="#" @click.prevent>
                            {{ $gettext('Datum') }}
                        </a>
                    </th>
                    <th v-for="(name, index) in additionalColumns"
                        :key="index"
                        @click="sort(index)"
                        class="responsive-hidden"
                        :class="sortClasses(index)">
                        <a href="#" @click.prevent>
                            {{name}}
                        </a>

                    </th>
                    <th class="actions" data-sort="false">{{ $gettext('Aktionen') }}</th>
                </tr>
            </thead>
            <tbody v-if="!hasData">
                <tr class="empty">
                    <td :colspan="numberOfColumns">
                        {{ $gettext('Dieser Ordner ist leer') }}
                    </td>
                </tr>
            </tbody>
            <tbody v-else-if="displayedFolders.length + displayedFiles.length === 0">
                <tr class="empty">
                    <td :colspan="numberOfColumns">
                        <translate>Keine Ordner oder Dateien entsprechen Ihrem Filter.</translate>
                    </td>
                </tr>
            </tbody>
            <tbody class="subfolders" v-if="displayedFolders.length > 0">
                <tr v-for="folder in displayedFolders"
                    :id="'row_folder_' + folder.id "
                    :data-permissions="folder.permissions"
                    :key="folder.id">
                    <td v-if="show_bulk_actions">
                        <studip-proxied-checkbox
                            name="ids[]"
                            :value="folder.id"
                            v-model="selectedIds"
                        ></studip-proxied-checkbox>
                    </td>
                    <td class="document-icon">
                        <a :href="folder.url">
                            <studip-icon :shape="folder.icon" role="clickable" size="26" class="text-bottom"></studip-icon>
                        </a>
                    </td>
                    <td :class="{'filter-match': valueMatchesFilter(folder.name)}">
                        <a :href="folder.url">
                            <span v-html="highlightString(folder.name)"></span>
                        </a>
                    </td>
                    <td class="responsive-hidden" :data-sort-value="folder.object_count">
                        <studip-folder-size v-if="folder.object_count"
                                            :object_count="folder.object_count"></studip-folder-size>
                    </td>
                    <td v-if="showdownloads"
                        class="responsive-hidden">
                    </td>
                    <td class="responsive-hidden" :class="{'filter-match': valueMatchesFilter(folder.author_name)}">
                        <a v-if="folder.author_url" :href="folder.author_url">
                            <span v-html="highlightString(folder.author_name)"></span>
                        </a>
                        <span v-else v-html="highlightString(folder.author_name)"></span>
                    </td>
                    <td class="responsive-hidden" style="white-space: nowrap;">
                        <studip-date-time :timestamp="folder.chdate" :relative="true"></studip-date-time>
                    </td>
                    <template v-for="(name, index) in additionalColumns">
                        <td v-if="folder.additionalColumns && folder.additionalColumns[index] && folder.additionalColumns[index].html"
                            class="responsive-hidden"
                            v-html="folder.additionalColumns[index].html"
                            :key="index"></td>
                        <td v-else class="responsive-hidden" :key="index"></td>
                    </template>
                    <td class="actions" v-html="folder.actions">
                    </td>
                </tr>
            </tbody>
            <tbody class="files" v-if="displayedFiles.length > 0">
                <tr v-for="file in displayedFiles"
                    :class="file.new ? 'new' : ''"
                    :id="'fileref_' + file.id"
                    role="row"
                    :data-permissions="getPermissions(file)"
                    :key="file.id">
                    <td v-if="show_bulk_actions">
                        <studip-proxied-checkbox
                            name="ids[]"
                            :value="file.id"
                            v-model="selectedIds"
                        ></studip-proxied-checkbox>
                    </td>
                    <td class="document-icon">
                        <a v-if="file.download_url" :href="file.download_url" target="_blank" rel="noopener noreferrer">
                            <studip-icon :shape="file.icon" role="clickable" size="24" class="text-bottom"></studip-icon>
                        </a>
                        <studip-icon v-else :shape="file.icon" role="clickable" size="24"></studip-icon>

                        <a :href="file.download_url"
                           v-if="file.download_url && file.mime_type.indexOf('image/') === 0"
                           class="lightbox-image"
                           data-lightbox="gallery"></a>
                    </td>
                    <td :class="{'filter-match': valueMatchesFilter(file.name)}">
                        <a :href="file.details_url" data-dialog>
                            <span v-html="highlightString(file.name)"></span>
                        </a>

                        <studip-icon v-if="file.restrictedTermsOfUse"
                                     shape="lock-locked"
                                     role="info"
                                     size="16"
                                     :title="$gettext('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')"></studip-icon>
                    </td>
                    <td :data-sort-value="file.size"
                        class="responsive-hidden">
                        <studip-file-size v-if="file.size !== null" :size="parseInt(file.size, 10)"></studip-file-size>
                    </td>
                    <td v-if="showdownloads"
                        class="responsive-hidden">
                        {{file.downloads}}
                    </td>
                    <td class="responsive-hidden" :class="{'filter-match': valueMatchesFilter(file.author_name)}">
                        <a v-if="file.author_url" :href="file.author_url">
                            <span v-html="highlightString(file.author_name)"></span>
                        </a>
                        <span v-else v-html="highlightString(file.author_name)"></span>
                    </td>
                    <td data-sort-value="file.chdate" class="responsive-hidden" style="white-space: nowrap;">
                        <studip-date-time :timestamp="file.chdate" :relative="true"></studip-date-time>
                    </td>
                    <template v-for="(name, index)  in additionalColumns">
                        <td v-if="file.additionalColumns && file.additionalColumns[index] && file.additionalColumns[index].html"
                            class="responsive-hidden"
                            v-html="file.additionalColumns[index].html"
                            :key="index"></td>
                        <td v-else class="responsive-hidden" :key="index"></td>
                    </template>
                    <td class="actions" v-html="file.actions">
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="(topfolder.buttons && show_bulk_actions) || tfoot_link">
                <tr>
                    <td :colspan="numberOfColumns - (tfoot_link ? 1 : 0)">
                        <div class="footer-items">
                            <span v-if="topfolder.buttons && show_bulk_actions"
                                  v-html="topfolder.buttons" class="bulk-buttons" ref="buttons"></span>
                            <span v-if="tfoot_link" :colspan="(topfolder.buttons && show_bulk_actions ? 1 : numberOfColumns)">
                                <a :href="tfoot_link.href">{{tfoot_link.text}}</a>
                            </span>
                            <span v-if="pagination" v-html="pagination" class="pagination"></span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <MountingPortal v-if="allow_filter" mount-to="#table-view-filter .sidebar-widget-content div" name="sidebar-content-toggle">
            <input :placeholder="$gettext('Name oder Autor/in')" type="search" v-model="filter" :disabled="!hasData" />
        </MountingPortal>
    </div>
</template>
<script>
import sanitizeHTML from 'sanitize-html';

export default {
    name: 'files-table',
    props: {
        topfolder: Object,
        folders: {
            type: Array,
            required: false,
            default: () => [],
        },
        files: Array,
        breadcrumbs: {
            type: Array,
            required: false,
            default: () => [],
        },
        showdownloads: {
            type: Boolean,
            required: false,
            default: true
        },
        table_title: {
            type: String,
            required: false,
            default: ''
        },
        show_bulk_actions: {
            type: Boolean,
            required: false,
            default: true
        },
        tfoot_link: {
            type: Object,
            required: false,
            default: null
        },
        pagination: {
            type: String,
            required: false,
            default: ''
        },
        initial_sort: {
            type: Object,
            required: false,
            default: () => ({sortedBy: 'name', sortDirection: 'asc'})
        },
        allow_filter: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            selectedIds: [undefined], // Includes invalid value to trigger watch on mounted
            sortedBy: this.initial_sort.sortedBy,
            sortDirection: this.initial_sort.sortDirection,
            allFiles: this.files,
            allFolders: this.folders,
            filter: ''
        };
    },
    methods: {
        sort (column) {
            let oldDirection = this.sortDirection;
            if (this.sortedBy === column) {
                this.sortDirection = oldDirection === "asc" ? "desc" : "asc";
            }
            this.sortedBy = column;
        },
        sortClasses (column) {
            let classes = [];
            if (this.sortedBy === column) {
                classes.push(this.sortDirection === 'asc' ? 'sortasc' : 'sortdesc');
            }
            return classes;
        },
        removeFile (id) {
            this.allFiles = this.allFiles.filter(file => file.id != id)
        },
        removeFolder (id) {
            this.allFolders = this.allFolders.filter(folder => folder.id != id)
        },
        sortArray (array) {
            if (!array.length) {
                return [];
            }

            // Determine whether the sorted array items have the key to sort by
            const arrayHasKey = Object.keys(array.find(item => true)).includes(this.sortedBy);

            // Define sort direction by this factor
            const directionFactor = this.sortDirection === "asc" ? 1 : -1;

            // Default sort function by string comparison of field
            const collator = new Intl.Collator(String.locale, {numeric: true, sensitivity: 'base'});
            let sortFunction = (a, b) => collator.compare(a[this.sortedBy], b[this.sortedBy]);

            // Sort numerically by field
            if (["size", "downloads", "chdate"].includes(this.sortedBy) && arrayHasKey) {
                sortFunction = (a, b) => parseInt(a[this.sortedBy], 10) - parseInt(b[this.sortedBy], 10);
            }

            // Additional sorting
            if (this.topfolder.additionalColumns[this.sortedBy] !== undefined && arrayHasKey) {
                const is_string = array.some(item => {
                    return typeof item.additionalColumns[this.sortedBy].order === "string"
                        && !isNaN(parseFloat(item.additionalColumns[this.sortedBy].order));
                });
                if (is_string) {
                    sortFunction = (a, b) => collator.compare(a.additionalColumns[this.sortedBy].order, b.additionalColumns[this.sortedBy].order);
                } else {
                    sortFunction = (a, b) => a.additionalColumns[this.sortedBy].order - b.additionalColumns[this.sortedBy].order;
                }
            }

            // Actual sort on copy of array
            return array.concat().sort((a, b) => directionFactor * sortFunction(a, b));
        },
        getPermissions (file) {
            let permissions = '';
            if (file.download_url) {
                permissions += 'dr';
            }
            if (file.isEditable) {
                permissions += 'w';
            }
            return permissions;
        },
        valueMatchesFilter(string) {
            if (this.needleForFilter.length === 0) {
                return false;
            }
            return string.toLowerCase().includes(this.needleForFilter);
        },
        highlightString (string) {
            let highlighted = sanitizeHTML(string);
            if (this.needleForFilter.length > 0) {
                // Escape needle for regexp, see https://stackoverflow.com/a/3561711
                const pattern = this.needleForFilter.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&')
                const regExp = new RegExp(pattern, 'gi');
                highlighted = highlighted.replace(regExp, '<span class="filter-match">$&</span>');
            }
            return highlighted;
        }
    },
    computed: {
        numberOfColumns () {
            return 7
                + (this.showdownloads ? 1 : 0)
                + Object.keys(this.topfolder.additionalColumns).length;
        },
        additionalColumns () {
            return this.topfolder.additionalColumns || [];
        },
        sortedFiles () {
            return this.sortArray(this.files);
        },
        sortedFolders () {
            return this.sortArray(this.folders);
        },
        allIds () {
            return [].concat(this.files.map(file => file.id)).concat(this.folders.map(folder => folder.id));
        },
        displayedFiles () {
            let files = [...this.allFiles];
            if (this.needleForFilter.length > 0) {
                files = files.filter(file => {
                    return this.valueMatchesFilter(file.name)
                        || this.valueMatchesFilter(file.author_name);
                });
            }
            return this.sortArray(files);
        },
        displayedFolders () {
            let folders = [...this.allFolders];
            if (this.needleForFilter.length > 0) {
                folders = folders.filter(folder => {
                    return this.valueMatchesFilter(folder.name)
                        || this.valueMatchesFilter(folder.author_name);
                });
            }
            return this.sortArray(folders);
        },
        needleForFilter () {
            return this.filter.trim().toLowerCase();
        },
        hasData () {
            return this.folders.length + this.files.length > 0;
        }
    },
    mounted () {
        // Trigger watch
        this.selectedIds = [];
    },
    watch: {
        selectedIds (current) {
            const activated = current.length > 0;
            if (this.$refs.buttons) {
                this.$nextTick(() => { // needs to be wrapped since we check the dom
                    this.$refs.buttons.querySelectorAll('.multibuttons .button').forEach(element => {
                        let condition = element.dataset.activatesCondition;
                        if (!condition || !activated) {
                            element.disabled = !activated;
                        } else {
                            condition = condition.replace(/:has\((.*?)\)/g, ' $1');
                            condition = condition.replace(':checkbox', 'input[type="checkbox"]');

                            element.disabled = this.$el.querySelector(condition) === null;
                        }
                    });
                });
            }
        },
    }
}
</script>
<style lang="scss">
#table-view-filter {
    input[type="search"] {
        width: 100%;
    }
}
table.documents {
    td.filter-match {
        background-color: var(--yellow-20);
    }
    span.filter-match {
        font-weight: bold;
        text-decoration: underline;
    }
}
</style>
