const folderIconMixin = {
    computed: {
        folderName() {
            return this.folder.attributes.name;
        },
        folderType() {
            return this.folder.attributes['folder-type'];
        },
        folderIsEmpty() {
            return this.folder.attributes['is-empty'];
        },
        folderIsReadable() {
            return this.folder.attributes['is-readable'];
        },
        folderIcon() {
            let shape = 'folder';

            switch (this.folderType) {
                case 'HomeworkFolder':
                case 'HiddenFolder':
                    shape = 'folder-lock';
                    break;
                case 'CourseGroupFolder':
                    shape = 'folder-group';
                    break;
                case 'TimedFolder':
                    shape = 'folder-date';
                    break;
                case 'CourseDateFolder':
                    shape = 'folder-topic';
                    break;
                case 'MaterialFolder':
                    return 'download';
                case 'PublicFolder':
                case 'CoursePublicFolder':
                    shape = 'folder-public';
                    break;
                case 'InboxFolder':
                case 'InboxOutboxFolder':
                    shape = 'folder-inbox';
                    break;
            }

            if (this.folderIsEmpty) {
                shape += '-empty';
            } else {
                shape += '-full';
            }

            return shape;
        }
    },

};

export default folderIconMixin;