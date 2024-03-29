/**
 * wysiwyg.js - Replace HTML textareas with WYSIWYG editor.
 */
import parseOptions from './parse_options.js';
import FindAndReplace from '@ckeditor/ckeditor5-find-and-replace/src/findandreplace';
import StudipBlockQuote from '../cke/studip-quote/StudipBlockQuote.js';
import WikiLink from '../cke/wiki-link/wiki-link.js';

const wysiwyg = {
    // NOTE keep this function in sync with Markup class
    htmlMarker: '<!--HTML-->',
    htmlMarkerRegExp: /^\s*<!--\s*HTML.*?-->/i,

    isHtml: function isHtml(text) {
        // NOTE keep this function in sync with
        // Markup::isHtml in Markup.class.php
        return this.hasHtmlMarker(text);
    },
    hasHtmlMarker: function hasHtmlMarker(text) {
        // NOTE keep this function in sync with
        // Markup::hasHtmlMarker in Markup.class.php
        return this.htmlMarkerRegExp.test(text);
    },
    markAsHtml: function markAsHtml(text) {
        // NOTE keep this function in sync with
        // Markup::markAsHtml in Markup.class.php
        if (this.hasHtmlMarker(text) || text.trim() == '') {
            return text; // marker already set, don't set twice
        }
        return this.htmlMarker + text;
    },

    getEditor,
    hasEditor,

    replace: replaceTextarea,
};

export default wysiwyg;

async function replaceTextarea(textarea) {
    if (hasEditor(textarea)) {
        return getEditor(textarea);
    }

    setEditor(textarea, {});

    await loadMathJax();
    const chunk = await STUDIP.loadChunk('wysiwyg');

    const $textarea = textarea instanceof jQuery ? textarea : $(textarea);
    const { options, editorType } = parseEditorOptions($textarea.attr('data-editor'));
    const editor = await createEditor(chunk, textarea, editorType, options);
    enhanceEditor($textarea, editor);

    setEditor(textarea, editor);
    $textarea.trigger('load.wysiwyg');

    return editor;
}

function destroyTextarea(textarea) {
    if (!hasEditor(textarea)) {
        throw new Error('Trying to destroy a non-existing editor instance.');
    }

    const editor = getEditor(textarea);
    editor.destroy(true);
    unsetEditor(textarea);
}

// create an unused id
function createNewId(prefix) {
    var i = 0;
    while ($('#' + prefix + i).length > 0) {
        i++;
    }
    return prefix + i;
}

const instances = new Map();

function getEditor(textarea) {
    return textarea?.id !== '' ? instances.get(textarea.id) : null;
}

function hasEditor(textarea) {
    return textarea.id !== '' && instances.has(textarea.id);
}

function setEditor(textarea, editor) {
    if (textarea.id === '') {
        textarea.id = createNewId('wysiwyg');
    }
    instances.set(textarea.id, editor);
}

function unsetEditor(textarea) {
    instances.delete(textarea.id);
}

////////////////////////////////////////////////////////////////////////////////
function parseEditorOptions(data) {
    const result = { options: {}, editorType: 'classic' };

    if (data) {
        const parsed = parseOptions(data);

        const toolbar = getToolbarOptions(parsed);
        if (toolbar) {
            result.options.toolbar = toolbar;
        }

        if (parsed.removePlugins) {
            result.options.removePlugins = parsed.removePlugins.split(',');
        }

        if (parsed.extraPlugins) {
            const pluginMap = { FindAndReplace, StudipBlockQuote, WikiLink };
            result.options.extraPlugins = parsed.extraPlugins.split(',').reduce((memo, plugin) => {
                return plugin in pluginMap ? [...memo, pluginMap[plugin]] : memo;
            }, []);
        }

        if (parsed.type) {
            if (['balloon', 'classic'].includes(parsed.type)) {
                result.editorType = parsed.type;
            }
        }
    }

    return result;
}

function getToolbarOptions(parsed) {
    if (parsed.toolbar === 'small') {
        return {
            removeItems: [
                'undo',
                'redo',
                'strikethrough',
                'horizontalLine',
            ],
        };
    } else if (parsed.toolbar === 'minimal') {
        return {
            items: [
                'bold',
                'italic',
                'underline',
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
                'link',
                'math',
                'specialCharacters',
            ],
        };
    }

    return null;
}

function loadMathJax() {
    if (window.MathJax && window.MathJax.Hub) {
        return Promise.resolve(window.MathJax);
    } else if (window.STUDIP && window.STUDIP.loadChunk) {
        return window.STUDIP.loadChunk('mathjax');
    }

    return Promise.reject(new Error('Could not load MathJax'));
}

function createEditor(chunk, textarea, editorType, options) {
    switch (editorType) {
        case 'classic':
            return chunk.createClassicEditorFromTextarea(textarea, options);
        case 'balloon':
            return chunk.createBalloonEditorFromTextarea(textarea, options);
    }

    throw new Error('No such type of WYSIWYG editor.');
}

function enhanceEditor($textarea, ckeditor) {
    // make sure HTML marker is always set, in
    // case contents are cut-off by the backend
    $textarea.closest('form').submit(() => {
        // only trigger if the editor is still attached to the textarea
        if (getEditor($textarea[0]) === ckeditor) {
            ckeditor.setData(wysiwyg.markAsHtml(ckeditor.getData()));
            ckeditor.updateSourceElement();
        }
    });

    // focus the editor if requested
    if ($textarea.is('[autofocus]')) {
        ckeditor.focus();
    }

    ckeditor.ui.focusTracker.on('change:isFocused', (evt, name, isFocused) => {
        if (!isFocused) {
            ckeditor.sourceElement.value = wysiwyg.markAsHtml(ckeditor.getData());
        }
    });

    // Tell MathJax v2.7 to leave the editor alone
    ckeditor.ui.element.classList.add('tex2jax_ignore');

    return ckeditor;
}
