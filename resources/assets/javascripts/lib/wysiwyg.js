/**
 * wysiwyg.js - Replace HTML textareas with WYSIWYG editor.
 */
import parseOptions from './parse_options.js';
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
        return this.htmlMarker + '\n' + text;
    },

    getEditor,
    hasEditor,

    replace(textarea) {
        if (!hasEditor(textarea)) {
            if (isTextareaVisible(textarea)) {
                replaceTextarea(textarea);
            }
        } else if (isEditorHidden(textarea)) {
            destroyTextarea(textarea);
        }
    },
};

export default wysiwyg;

function isTextareaVisible(textarea) {
    return $(textarea).is(':visible');
}

function isEditorHidden(textarea) {
    if (!hasEditor(textarea)) {
        return false;
    }
    const editor = getEditor(textarea);
    return editor && editor.ui && $(editor.ui.element).is(':hidden');
}

function replaceTextarea(textarea) {
    setEditor(textarea, {});
    const $textarea = textarea instanceof jQuery ? textarea : $(textarea);

    let options = {};
    if ($textarea.attr('data-editor')) {
        const parsed = parseOptions($textarea.attr('data-editor'));

        if (parsed.removePlugins) {
            options.removePlugins = parsed.removePlugins.split(",")
        }

        if (parsed.extraPlugins) {
            const pluginMap = { WikiLink };
            options.extraPlugins = parsed.extraPlugins.split(",").reduce((memo, plugin) => {
                if (plugin in pluginMap) {
                    memo.push(pluginMap[plugin]);
                }
                return memo;
            }, []);
        }
    }

    return STUDIP.loadChunk('wysiwyg')
        .then(loadMathJax)
        .then(createEditor)
        .then(setEditorInstance)
        .then(enhanceEditor)
        .then(emitLoadEvent);

    function createEditor(ClassicEditor) {
        return ClassicEditor.create(textarea, options);
    }

    function setEditorInstance(ckeditor) {
        setEditor(textarea, ckeditor);
        return ckeditor;
    }

    function enhanceEditor(ckeditor) {
        // make sure HTML marker is always set, in
        // case contents are cut-off by the backend
        $textarea.closest('form').submit(() => {
            ckeditor.sourceElement.value = wysiwyg.markAsHtml(ckeditor.getData());
        });

        // focus the editor if requested
        if ($textarea.is('[autofocus]')) {
            ckeditor.focus();
        }

        ckeditor.ui.focusTracker.on('change:isFocused', (evt, name, isFocused) => {
            if (!isFocused) {
                ckeditor.updateSourceElement();
            }
        });

        // TODO: Kein updateSourceElement im SourceEditing-Modus
        //     $(ckeditor.container.$).on('blur', '.CodeMirror', function (event) {
        //         ckeditor.updateElement(); // also update in source mode
        //     });
    }

    function emitLoadEvent(ckeditor) {
        $textarea.trigger('load.wysiwyg');

        return ckeditor;
    }

    async function loadMathJax(ckeditor) {
        let mathjaxP;

        if (window.MathJax && window.MathJax.Hub) {
            mathjaxP = Promise.resolve(window.MathJax);
        } else if (window.STUDIP && window.STUDIP.loadChunk) {
            mathjaxP = window.STUDIP.loadChunk('mathjax');
        }

        await mathjaxP;

        //console.log('loading MathJaxP...', mathjaxP);
        return ckeditor;
    }
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
