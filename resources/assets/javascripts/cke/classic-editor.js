import ClassicEditorBase from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import { builtinPlugins } from './builtin-plugins.js';
import { defaultConfig } from './default-config.js';
import eventBus from '../lib/event-bus.ts';

export default class ClassicEditor extends ClassicEditorBase {}
export { createClassicEditorFromTextarea };

ClassicEditor.builtinPlugins = builtinPlugins;
ClassicEditor.defaultConfig = {
    ...defaultConfig,
    toolbar: {
        items: [
            'undo',
            'redo',
            'findAndReplace',
            '|',
            'heading',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'removeFormat',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'outdent',
            'indent',
            '|',
            'alignment:left',
            'alignment:right',
            'alignment:center',
            'alignment:justify',
            '|',
            'link',
            'insertTable',
            'uploadImage',
            'codeBlock',
            'math',
            'studip-wiki',
            'specialCharacters',
            'horizontalLine',
            '|',
            'insertBlockQuote',
            'splitBlockQuote',
            'removeBlockQuote',
            '|',
            'sourceEditing',
        ],
        shouldNotGroupWhenFull: false,
    },
};

function createClassicEditorFromTextarea(textarea, options) {
    return ClassicEditor.create(textarea, options)
        .then((editor) => {
            const updateOffsetTop = createUpdater(editor);

            updateOffsetTop();

            eventBus.on('toggle-compact-navigation', updateOffsetTop);
            eventBus.on('switch-focus-mode', updateOffsetTop);

            editor.on('destroy', () => {
                eventBus.off('toggle-compact-navigation', updateOffsetTop);
                eventBus.off('switch-focus-mode', updateOffsetTop);
            });

            return editor;
        })
        .then((editor) => {
            const button = editor.ui.view.toolbar?.items.find((item) => item.class === 'ck-source-editing-button');
            if (button) {
                button.withText = false;
            }

            return editor;
        });
}

function createUpdater(editor) {
    // This needs to be delayed since some events will fire before
    // changing the DOM
    return () =>
        setTimeout(() => {
            editor.ui.viewportOffset = { top: getViewportOffsetTop() };
            editor.ui.update();
        }, 50);
}

function getViewportOffsetTop() {
    const topBar = document.getElementById('top-bar');
    const responsiveContentbar = document.getElementById('responsive-contentbar');

    let top = topBar.clientHeight + topBar.clientTop;
    if (responsiveContentbar) {
        top += responsiveContentbar?.clientHeight + responsiveContentbar.clientTop;
    }

    return top;
}
