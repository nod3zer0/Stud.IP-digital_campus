const Wiki = {
    updatePageContent(pageContents) {
        if (!pageContents) {
            return;
        }
        for (let page_id in pageContents.contents) {
            $('.wiki_page_content_' + page_id).html(pageContents.contents[page_id]);
        }
    },
    updateEditorStatus(editorStatus) {
        if (!editorStatus) {
            return;
        }
        for (let page_id in STUDIP.Wiki.Editors) {
            STUDIP.Wiki.Editors[page_id].users = editorStatus.users[page_id];
            if (!STUDIP.Wiki.Editors[page_id].editing) {
                STUDIP.Wiki.Editors[page_id].content = editorStatus.contents[page_id];
                STUDIP.Wiki.Editors[page_id].editor.setData(editorStatus.wysiwyg_contents[page_id]);
            }
            if (
                !STUDIP.Wiki.Editors[page_id].editing
                && editorStatus.pages[page_id].editing > 0
            ) {
                STUDIP.Wiki.Editors[page_id].editing = true;
                STUDIP.Wiki.Editors[page_id].focusEditor();
            } else {
                STUDIP.Wiki.Editors[page_id].editing = editorStatus.pages[page_id].editing > 0;
            }
            STUDIP.Wiki.Editors[page_id].lastSaveDate = new Date(editorStatus.pages[page_id].chdate * 1000);
        }

    },
    Editors: {},
    initEditor() {

        let wiki_edit_container = document.querySelectorAll( '.wiki-editor-container');
        for (let edit_container of wiki_edit_container) {
            let page_id = edit_container.dataset.page_id;

            Promise.all([
                STUDIP.Vue.load(),
                import('../../../vue/components/WikiEditorOnlineUsers.vue').then((config) => config.default),
            ]).then(([{ createApp }, WikiEditorOnlineUsers]) => {
                return createApp({
                    el: edit_container,
                    data() {
                        return {
                            page_id: page_id,
                            editing: edit_container.dataset.editing > 0,
                            content: edit_container.dataset.content,
                            users: JSON.parse(edit_container.dataset.users),
                            editor: null,
                            isChanged: false,
                            lastSaveDate: new Date(edit_container.dataset.chdate * 1000),
                            lastChangeDate: 0,
                            lastFocussedDate: 0,
                            autosave: true
                        };
                    },
                    methods: {
                        applyEditing() {
                            const url = STUDIP.URLHelper.getURL('dispatch.php/course/wiki/apply_editing/' + this.page_id)
                            $.post(url).done(output => {
                                if (output.me_online.editing > 0) {
                                    this.editing = true;
                                    this.focusEditor();
                                }
                                this.users = output.users;
                            });
                        },
                        delegateEditMode(user_id) {
                            const url = STUDIP.URLHelper.getURL('dispatch.php/course/wiki/delegate_edit_mode/' + this.page_id + '/' + user_id);
                            $.post(url).done(() => this.editing = false);
                        },
                        focusEditor() {
                            this.$nextTick(() => {
                                this.editor.editing.view.focus();
                            });
                        }
                    },
                    mounted() {
                        let textarea = this.$refs['wiki_editor'];
                        let promise = STUDIP.wysiwyg.replace(textarea);
                        promise.then((editor) => {
                            if (this.editing) {
                                editor.editing.view.focus();
                            }
                            editor.model.document.on('change:data',() => {
                                this.isChanged = true;
                                this.lastChangeDate = new Date();
                            });
                            this.editor = editor;
                        });
                    },
                    computed: {
                        requestingUsers() {
                            return this.users
                                .filter(u => u.editing_request)
                                .sort((a, b) => a.fullname.localeCompare(b.fullname));
                        }
                    },
                    components: { WikiEditorOnlineUsers }
                });
            }).then((app) => {
                STUDIP.Wiki.Editors[page_id] = app;
            });
        }

    }
};

export default Wiki;
