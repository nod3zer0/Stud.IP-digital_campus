import { Plugin } from 'ckeditor5/src/core';
import { add } from '@ckeditor/ckeditor5-utils/src/translation-service';
import { $gettext } from '../../lib/gettext.js';
import A11YDialogEditing from './editing.js';
import A11YDialogUI from './ui.js';

export function updateVoiceLabel() {
    add('de', {
        'Rich Text Editor': 'Rich Text Editor (Um Bedienhinweise zu erhalten, drücken Sie ALT+0 im Eingabefeld.)',
    });
}

export default class A11YDialog extends Plugin {
    static get requires() {
        return [A11YDialogUI, A11YDialogEditing];
    }

    /**
     * @inheritDoc
     */
    static get pluginName() {
        return 'A11YDialog';
    }
}
