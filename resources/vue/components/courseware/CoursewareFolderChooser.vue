<template>
    <select v-model="currentValue" @change="changeSelection">
        <option v-if="unchoose" value=""><translate>kein Ordner ausgewählt</translate></option>
        <optgroup v-if="this.context.type === 'courses'" :label="textOptGroupCourse">
            <option v-for="folder in loadedCourseFolders" :key="folder.id" :value="folder.id">
                {{ folder.attributes.name }}
            </option>
        </optgroup>
        <optgroup v-if="allowUserFolders" :label="textOptGroupUser">
            <option v-for="folder in loadedUserFolders" :key="folder.id" :value="folder.id">
                {{ folder.attributes.name }}
            </option>
        </optgroup>
    </select>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

function filterCourseFolders(folders, { allowHomeworkFolders }) {
    const validatedParents = new Map();

    return folders.filter((folder) => {
        if (validateParentFolder(folder)) {
            switch (folder.attributes['folder-type']) {
                case 'HiddenFolder':
                    if (folder.attributes['data-content']['download_allowed'] === 1) {
                        return true;
                    }
                    break;
                case 'HomeworkFolder':
                    if (allowHomeworkFolders) {
                        return true;
                    }
                    break;
                default:
                    return true;
            }
        }
    });

    function validateParentFolder(folder) {
        let isValid = true;
        if (folder?.relationships?.parent) {
            let parentId = folder.relationships.parent.data.id;
            if (validatedParents.has(parentId)) {
                isValid = validatedParents.get(parentId);
            } else {
                let parent = folders.find((f) => f.id === parentId);
                if (parent) {
                    isValid = hiddenParentFolderValidation(parent);
                    validatedParents.set(parentId, isValid);
                }
            }
        }
        return isValid;
    }

    function hiddenParentFolderValidation(parentFolder) {
        if (parentFolder.attributes['folder-type'] === 'HiddenFolder') {
            return false;
        } else if (parentFolder?.relationships?.parent) {
            // Recursively validating the parents.
            return validateParentFolder(parentFolder);
        } else {
            return true;
        }
    }
}
export default {
    name: 'courseware-folder-chooser',
    props: {
        value: String,
        allowUserFolders: { type: Boolean, default: false },
        allowHomeworkFolders: { type: Boolean, default: false },
        unchoose: { type: Boolean, default: false },
    },
    data() {
        return {
            currentValue: Object,
            textOptGroupCourse: this.$gettext('Dateibereich dieser Veranstaltung'),
            textOptGroupUser: this.$gettext('Persönlicher Dateibereich'),
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            relatedFolders: 'folders/related',
            userId: 'userId',
        }),
        courseObject() {
            return { type: 'courses', id: `${this.context.id}` };
        },
        userObject() {
            return { type: 'users', id: `${this.userId}` };
        },
        loadedCourseFolders() {
            return filterCourseFolders(
                this.relatedFolders({ parent: this.courseObject, relationship: 'folders' }) ?? [],
                {
                    allowHomeworkFolders: this.allowHomeworkFolders,
                }
            );
        },
        loadedUserFolders() {
            let loadedUserFolders = [];
            let UserFolders = this.relatedFolders({ parent: this.userObject, relationship: 'folders' }) ?? [];
            UserFolders.forEach((folder) => {
                if (folder.attributes['folder-type'] === 'PublicFolder') {
                    loadedUserFolders.push(folder);
                }
            });

            return loadedUserFolders;
        },
    },
    methods: {
        ...mapActions({
            loadRelatedFolders: 'folders/loadRelated',
        }),

        changeSelection() {
            this.$emit('input', this.currentValue);
        },

        getCourseFolders() {
            const parent = this.courseObject;
            const relationship = 'folders';
            const options = { 'page[limit]': 10000 };

            return this.loadRelatedFolders({ parent, relationship, options });
        },

        getUserFolders() {
            const parent = this.userObject;
            const relationship = 'folders';
            const options = { 'page[limit]': 10000 };

            return this.loadRelatedFolders({ parent, relationship, options });
        },

        confirmSelectedFolder() {
            const folders = this.loadedUserFolders.concat(this.loadedCourseFolders);

            let folder = folders.find( folder => folder.id === this.currentValue);

            if (this.currentValue !== '' && folder === undefined) {
                this.currentValue = '';
                this.changeSelection();
            }
        }
    },
    async mounted() {
        this.currentValue = this.value;
        if (this.context.type !== 'users') {
            await this.getCourseFolders();
        }
        await this.getUserFolders();
        this.confirmSelectedFolder();
    },
};
</script>
