<template>
    <ol class="file-chooser-breadcrumb">
        <li v-for="(folder, index) in breadcrumbItems" :key="folder.id">
            <a href="#" @click.prevent="selectFolder(folder)">
                <template v-if="rootId !== folder.id">
                    {{ folder.attributes.name }}
                </template>
                <studip-icon v-else shape="home" :title="homeTitle"/>
            </a>
            <span v-if="breadcrumbItems.length > 1 && index !== breadcrumbItems.length - 1">/</span>
        </li>
    </ol>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'file-chooser-breadcrumb',
    props: {
        folders: Array,
    },
    data() {
        return {
            breadcrumbItems: [],
            rootId: ''
        };
    },
    computed: {
        ...mapGetters({
            activeFolderId: 'file-chooser/activeFolderId',
            activeFolder: 'file-chooser/activeFolder',
            folderById: 'folders/byId',
        }),
        homeTitle() {
            return this.activeFolderRangeType === 'users' ? this.$gettext('Arbeitsplatz') : this.$gettext('Diese Veranstaltung');
        }
    },
    methods: {
        ...mapActions({
            setActiveFolderId: 'file-chooser/setActiveFolderId',
            setSelectedFolderId: 'file-chooser/setSelectedFolderId'
        }),
        selectFolder(folder) {
            this.setActiveFolderId(folder.id);
        },
        updateBreadcrumb() {
            this.breadcrumbItems = [];
            this.addBreadcrumbItem(this.activeFolder);
            this.breadcrumbItems = this.breadcrumbItems.reverse();
        },
        addBreadcrumbItem(folder) {
            this.breadcrumbItems.push(folder);
            if (folder.relationships.parent) {
                const id = folder.relationships.parent.data.id;
                const parent = this.folderById({ id });
                this.addBreadcrumbItem(parent);
            } else {
                this.rootId = folder.id;
            }
        },
    },
    watch: {
        activeFolderId(newId) {
            this.updateBreadcrumb();
            this.setSelectedFolderId('');
        },
    },
};
</script>

<style lang="scss">
.file-chooser-breadcrumb {
    display: flex;
    flex-direction: row;
    padding: 0;
    margin: 0;
    li {
        list-style: none;
        a img {
            vertical-align: text-bottom;
        }
        span {
            padding: 0 4px 0 0;
        }
    }
}
</style>
