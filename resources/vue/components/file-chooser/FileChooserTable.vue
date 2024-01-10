<template>
    <table class="default">
        <colgroup>
            <col style="width: 36px" />
            <col />
            <col style="width: 100px" />
            <col class="responsive-hidden" style="width: 150px" />
            <col style="width: 126px" />
        </colgroup>
        <thead>
            <tr class="sortable">
                <th>{{ $gettext('Typ') }}</th>
                <th :class="getSortClass('name')" @click="sort('name')">
                    <a href="#">{{ $gettext('Name') }}</a>
                </th>
                <th :class="getSortClass('size')" @click="sort('size')">
                    <a href="#">{{ $gettext('Größe') }}</a>
                </th>
                <th class="responsive-hidden" :class="getSortClass('owner')" @click="sort('owner')">
                    <a href="#">{{ $gettext('Autor/-in') }}</a>
                </th>
                <th :class="getSortClass('mkdate')" @click="sort('mkdate')">
                    <a href="#">{{ $gettext('Datum') }}</a>
                </th>
            </tr>
        </thead>
        <tbody v-if="empty">
            <tr class="empty">
                <td colspan="5">{{ $gettext('Dieser Ordner ist leer') }}</td>
            </tr>
        </tbody>
        <template v-else>
            <tbody class="subfolders">
                <file-chooser-folder-item v-for="folder in sortedFolders" :key="folder.id" :folder="folder" tag="tr" />
            </tbody>
            <tbody class="files">
                <file-chooser-file-item
                    v-for="file in sortedFiles"
                    :key="file.id"
                    :file="file"
                    tag="tr"
                    @selectId="$emit('selectId')"
                />
            </tbody>
        </template>
    </table>
</template>

<script>
import folderIconMixin from '@/vue/mixins/file-chooser/folder-icon.js';
import fileChooserFileItem from './FileChooserFileItem.vue';
import fileChooserFolderItem from './FileChooserFolderItem.vue';

export default {
    name: 'file-chooser-table',
    mixins: [folderIconMixin],
    components: {
        fileChooserFileItem,
        fileChooserFolderItem,
    },
    props: {
        files: {
            type: Array,
            required: false,
        },
        subfolders: {
            type: Array,
            required: false,
        },
        empty: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            sortBy: 'name',
            sortASC: true,
        };
    },
    computed: {
        sortedFiles() {
            let files = this.files;
            switch (this.sortBy) {
                case 'name':
                    files = files.sort((a, b) => {
                        const aName = a.attributes.name.toUpperCase();
                        const bName = b.attributes.name.toUpperCase();
                        if (this.sortASC) {
                            return aName.localeCompare(bName, 'de', { sensitivity: 'base' });
                        } else {
                            return bName.localeCompare(aName, 'de', { sensitivity: 'base' });
                        }
                    });
                    break;
                case 'size':
                    files = files.sort((a, b) => {
                        if (this.sortASC) {
                            return a.attributes.filesize < b.attributes.filesize ? -1 : 1;
                        } else {
                            return a.attributes.filesize > b.attributes.filesize ? -1 : 1;
                        }
                    });
                    break;
                case 'owner':
                    files = files.sort((a, b) => {
                        const aName = (a.relationships.owner?.meta?.name ?? '').toUpperCase();
                        const bName = (b.relationships.owner?.meta?.name ?? '').toUpperCase();
                        if (this.sortASC) {
                            return aName.localeCompare(bName, 'de', {
                                sensitivity: 'base',
                            });
                        } else {
                            return bName.localeCompare(aName, 'de', {
                                sensitivity: 'base',
                            });
                        }
                    });
                    break;
                case 'mkdate':
                    files = files.sort((a, b) => {
                        if (this.sortASC) {
                            return new Date(a.attributes.mkdate) < new Date(b.attributes.mkdate) ? -1 : 1;
                        } else {
                            return new Date(a.attributes.mkdate) > new Date(b.attributes.mkdate) ? -1 : 1;
                        }
                    });
                    break;
            }
            return files;
        },
        sortedFolders() {
            let folders = this.subfolders;
            switch (this.sortBy) {
                case 'name':
                    folders = folders.sort((a, b) => {
                        const aName = a.attributes.name.toUpperCase();
                        const bName = b.attributes.name.toUpperCase();
                        if (this.sortASC) {
                            return aName.localeCompare(bName, 'de', { sensitivity: 'base' });
                        } else {
                            return bName.localeCompare(aName, 'de', { sensitivity: 'base' });
                        }
                    });
                    break;
                case 'size':
                    folders = folders.sort((a, b) => {
                        const aObjects = a.relationships['file-refs'].meta.count + a.relationships.folders.meta.count;
                        const bObjects = b.relationships['file-refs'].meta.count + b.relationships.folders.meta.count;
                        if (this.sortASC) {
                            return aObjects < bObjects ? -1 : 1;
                        } else {
                            return aObjects > bObjects ? -1 : 1;
                        }
                    });
                    break;
                case 'owner':
                    folders = folders.sort((a, b) => {
                        const aName = (a.relationships.owner?.meta?.name ?? '').toUpperCase();
                        const bName = (b.relationships.owner?.meta?.name ?? '').toUpperCase();
                        if (this.sortASC) {
                            return aName.localeCompare(bName, 'de', {
                                sensitivity: 'base',
                            });
                        } else {
                            return bName.localeCompare(aName, 'de', {
                                sensitivity: 'base',
                            });
                        }
                    });
                    break;
                case 'mkdate':
                    folders = folders.sort((a, b) => {
                        if (this.sortASC) {
                            return new Date(a.attributes.mkdate) < new Date(b.attributes.mkdate) ? -1 : 1;
                        } else {
                            return new Date(a.attributes.mkdate) > new Date(b.attributes.mkdate) ? -1 : 1;
                        }
                    });
                    break;
            }
            return folders;
        },
    },
    methods: {
        sort(sortBy) {
            if (this.sortBy === sortBy) {
                this.sortASC = !this.sortASC;
            } else {
                this.sortBy = sortBy;
            }
        },
        getSortClass(col) {
            if (col === this.sortBy) {
                return this.sortASC ? 'sortasc' : 'sortdesc';
            }
        },
    },
};
</script>
