import { Plugin } from '@ckeditor/ckeditor5-core';
import A11YDialogCommand from './command.js';

export default class A11YDialogEditing extends Plugin {
    static get pluginName() {
        return 'A11YDialogEditing';
    }

    init() {
        const editor = this.editor;

        editor.commands.add('a11ydialog', new A11YDialogCommand(editor));

        editor.keystrokes.set('ALT+0', 'a11ydialog');
    }
}
