import { Plugin } from '@ckeditor/ckeditor5-core';
import WikiLinkUI from './ui.js';
import WikiLinkEditing from './editing.js';

export default class WikiLink extends Plugin {
    static get requires() {
        return [WikiLinkEditing, WikiLinkUI];
    }

    /**
     * @inheritDoc
     */
    static get pluginName() {
        return 'WikiLink';
    }

    /**
     * @inheritDoc
     */
    init() {
        const ui = this.editor.plugins.get('WikiLinkUI');

        ui.on('insert', (event, data) => {
            this.editor.execute('insertStudipWikiLink', data);
            ui.fire('close');
        });
    }
}
