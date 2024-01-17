/**
 * This file contains all wiki related javascript.
 *
 * For now this is the "submit and edit" functionality via ajax.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright Stud.IP Core Group
 * @license   GPL2 or any later version
 * @since     Stud.IP 3.3
 */



STUDIP.domReady(() => {
    STUDIP.JSUpdater.register('wiki_page_content', STUDIP.Wiki.updatePageContent, function () {
        //update the wiki page for readers:
        return Array.from(document.getElementsByClassName('wiki_page_content')).map(node => {
            return node.data.set.page_id;
        });
    });

    if (document.querySelector('.wiki-editor-container') !== null) {
        STUDIP.Wiki.initEditor();
    }

    STUDIP.JSUpdater.register('wiki_editor_status', STUDIP.Wiki.updateEditorStatus, function () {
        let info = {
            page_ids: [],
            focussed: null
        };
        for (let page_id in STUDIP.Wiki.Editors) {
            info.page_ids.push(page_id);
            let editor = STUDIP.Wiki.Editors[page_id].editor;
            if (STUDIP.Wiki.Editors[page_id].isChanged && STUDIP.Wiki.Editors[page_id].autosave) {
                //if either the textarea or the wysiwyg has focus:
                info.page_content = editor.getData();
                STUDIP.Wiki.Editors[page_id].isChanged = false;
                STUDIP.Wiki.Editors[page_id].lastSaveDate = new Date();
            }
            if (editor.editing.view.document.isFocused) {
                STUDIP.Wiki.Editors[page_id].lastFocussedDate = new Date();
            }
            if (new Date() - STUDIP.Wiki.Editors[page_id].lastFocussedDate < 1000 * 60) { //time after inactivity
                info.focussed = page_id;
            } else {
                if (STUDIP.Wiki.Editors[page_id].users.length !== 1) {
                    //then I will likely lose my edit mode so others can obtain it
                    STUDIP.Wiki.Editors[page_id].editing = false;
                }
            }
        }
        return info;
    });
});

