import { Plugin } from 'ckeditor5/src/core';
import { createDropdown } from 'ckeditor5/src/ui';
import WikiLinkFormView from './formview.js';
import { $gettext } from '../../lib/gettext.js';

const wikiIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 54 54"><path class="cls-1" d="M49.83,15a15.17,15.17,0,0,1-10.17,7.9,31.41,31.41,0,0,1,3.45,11.38C46.63,32.05,53.82,25.94,49.83,15ZM4.17,15c-4,10.94,3.2,17,6.72,19.28A31.41,31.41,0,0,1,14.34,22.9,15.17,15.17,0,0,1,4.17,15ZM27,16c-7.1,0-12.85,10.31-12.85,23h25.7C39.85,26.29,34.1,16,27,16Z"/></svg>';

export default class WikiLinkUI extends Plugin {
    static get pluginName() {
        return 'WikiLinkUI';
    }

    constructor(editor) {
        super(editor);
        this.formView = null;
    }

    init() {
        const editor = this.editor;
        editor.ui.componentFactory.add('studip-wiki', (locale) => {
            const dropdown = createDropdown(locale);
            const formView = (this.formView = new WikiLinkFormView(editor.locale));

            dropdown.bind('isEnabled').to(editor.commands.get('insertStudipWikiLink'));
            dropdown.panelView.children.add(formView);

            dropdown.on(
                'change:isOpen',
                (event, name, isOpen) => {
                    if (isOpen) {
                        formView.disableCssTransitions();

                        formView.reset();
                        formView._keywordInputView.fieldView.select();
                        formView.focus();

                        formView.enableCssTransitions();
                    } else {
                        formView.reset();
                        formView.focus();
                    }
                },
                { priority: 'low' }
            );

            this._setupDropdownButton(dropdown);
            this._setupFormView(formView);

            this.on('close', () => (dropdown.isOpen = false));

            return dropdown;
        });
    }

    _setupDropdownButton(dropdown) {
        const editor = this.editor;
        const t = editor.locale.t;

        dropdown.buttonView.set({
            icon: wikiIcon,
            label: $gettext('Link auf Wikiseite einfügen'),
            tooltip: true,
        });
    }

    _setupFormView(formView) {
        formView.delegate('insert').to(this);
    }
}
