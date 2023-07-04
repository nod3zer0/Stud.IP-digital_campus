import BalloonEditorBase from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';
import { builtinPlugins } from './builtin-plugins.js';
import { defaultConfig } from './default-config.js';

export default class BalloonEditor extends BalloonEditorBase {}
export { createBalloonEditorFromTextarea };

BalloonEditor.builtinPlugins = builtinPlugins;
BalloonEditor.defaultConfig = {
    ...defaultConfig,
    balloonToolbar: {
        items: [
            'bold',
            'italic',
            'underline',
            'subscript',
            'superscript',
            '|',
            'removeFormat',
            '|',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'link',
            'math',
            'specialCharacters',
        ],
        shouldNotGroupWhenFull: true,
    },
    blockToolbar: [
        'paragraph',
        'heading1',
        'heading2',
        '|',
        'bulletedList',
        'numberedList',
        '|',
        'alignment:left',
        'alignment:right',
        'alignment:center',
        'alignment:justify',
    ],
};

function createBalloonEditorFromTextarea(textarea, options) {
    const replacement = document.createElement('div');
    replacement.classList.add('wysiwyg-balloon');
    replacement.innerHTML = textarea.value;
    textarea.parentNode.insertBefore(replacement, textarea.nextSibling);
    textarea.style.display = 'none';

    return BalloonEditor.create(replacement);
}
