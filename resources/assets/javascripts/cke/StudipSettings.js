import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import { createDropdown, ButtonView, View } from 'ckeditor5/src/ui';
import { $gettext } from '../lib/gettext.js';

const settings = {
    url: STUDIP.URLHelper.getURL('dispatch.php/wysiwyg/settings/users/current'),
    save: function (data) {
        return $.ajax({
            url: this.url,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
        });
    },
};

const gearsIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 54 54"><path d="M50.58,22.76l-.95-.4a3.12,3.12,0,0,1,0-5.8l.95-.41h0a.69.69,0,0,0,.37-.89h0l-.7-1.69-1.13-2.75A.71.71,0,0,0,49,10.6a.67.67,0,0,0-.74-.15h0l-.95.39a3.05,3.05,0,0,1-3.43-.65,3.15,3.15,0,0,1-.65-3.45l.4-1a.69.69,0,0,0-.36-.89L38.82,3.05a.67.67,0,0,0-.88.37l-.41,1a3.08,3.08,0,0,1-5.75,0l-.41-1a.68.68,0,0,0-.89-.37L26.07,4.9a.68.68,0,0,0-.37.89l.39,1A3.1,3.1,0,0,1,22,10.85h0l-.95-.4a.68.68,0,0,0-.89.37l-1.83,4.44a.69.69,0,0,0,.37.89l1,.41a3.12,3.12,0,0,1,0,5.79l-.95.42a.69.69,0,0,0-.38.89l1.83,4.44a.7.7,0,0,0,.89.37l1-.4a3.05,3.05,0,0,1,3.42.65,3.12,3.12,0,0,1,.64,3.45h0l-.4,1a.68.68,0,0,0,.37.89l4.41,1.84a.68.68,0,0,0,.89-.36l.4-1a3.08,3.08,0,0,1,5.76,0l.4,1a.68.68,0,0,0,.89.36L43.23,34a.68.68,0,0,0,.37-.89l-.4-1h0a3.15,3.15,0,0,1,.65-3.45,3.06,3.06,0,0,1,3.42-.65h0l.95.4a.69.69,0,0,0,.89-.37L51,23.65A.69.69,0,0,0,50.58,22.76ZM36.89,24.9a5.84,5.84,0,0,1-7.65-3.19A5.91,5.91,0,0,1,32.41,14a5.84,5.84,0,0,1,7.65,3.18A5.91,5.91,0,0,1,36.89,24.9Z"/><path d="M25.82,37.11H25.1a2.14,2.14,0,0,1-2-1.33,2.21,2.21,0,0,1,.5-2.41l.51-.51a.5.5,0,0,0,0-.68l-2.36-2.38a.46.46,0,0,0-.67,0l-.52.52a2.16,2.16,0,0,1-2.39.5,2.19,2.19,0,0,1-1.33-2.06V28a.47.47,0,0,0-.47-.48H13a.48.48,0,0,0-.48.47v.73a2.16,2.16,0,0,1-3.72,1.56h0l-.51-.52a.46.46,0,0,0-.67,0L5.24,32.17a.48.48,0,0,0,0,.68l.5.51a2.19,2.19,0,0,1,.5,2.41,2.14,2.14,0,0,1-2,1.33H3.48a.48.48,0,0,0-.48.48V41a.48.48,0,0,0,.48.48H4.2a2.15,2.15,0,0,1,2,1.34,2.17,2.17,0,0,1-.5,2.4h0l-.51.52a.48.48,0,0,0,0,.67l2.36,2.38a.46.46,0,0,0,.67,0l.52-.51a2.16,2.16,0,0,1,2.39-.5A2.18,2.18,0,0,1,12.5,49.8v.72h0A.49.49,0,0,0,13,51h3.34a.47.47,0,0,0,.48-.48h0V49.8a2.18,2.18,0,0,1,1.33-2.06,2.16,2.16,0,0,1,2.39.5l.51.52a.46.46,0,0,0,.67,0l2.37-2.38a.48.48,0,0,0,0-.67l-.51-.53a2.19,2.19,0,0,1-.5-2.4,2.15,2.15,0,0,1,2-1.34h.72A.48.48,0,0,0,26.3,41V37.59A.47.47,0,0,0,25.82,37.11ZM14.65,43.39a4.12,4.12,0,1,1,4.09-4.12A4.1,4.1,0,0,1,14.65,43.39Z"/></svg>';

export class StudipSettings extends Plugin {
    init() {
        this.editor.ui.componentFactory.add('studipSettings', (locale) => {
            const dropdownView = createDropdown(locale);

            dropdownView.buttonView.set({
                label: $gettext('Stud.IP Einstellungen'),
                icon: gearsIcon,
                tooltip: true,
            });

            dropdownView.render();

            const studipSettingsView = new StudipSettingsView(locale);
            dropdownView.panelView.children.add(studipSettingsView);
            studipSettingsView.on('wysiwyg:change', (eventInfo, disabled) => {
                this._save(studipSettingsView, disabled).then(() => (dropdownView.isOpen = false));
            });

            return dropdownView;
        });
    }

    _save(view, disabled) {
        view.functional = false;

        return settings
            .save({ disabled })
            .fail(function (xhr) {
                console.error("couldn't save changes");
            })
            .always(() => {
                view.functional = true;
            });
    }
}

class StudipSettingsView extends View {
    constructor(locale) {
        super(locale);

        const bind = this.bindTemplate;
        this.set({
            checked: false,
            functional: true,
        });

        const button = createButton();
        this.button = button;
        button.on('execute', () => {
            this.fire('wysiwyg:change', this.checked);
        });

        this.on('checking', (...args) => {
            this.checked = !this.checked;
        });

        this.setTemplate({
            tag: 'form',
            attributes: {
                class: ['default ck-studip-settings-form'],
                tabindex: '-1',
                style: 'max-width: 20em; padding: 1em;',
            },
            children: [createCheckbox(), createHelpText(), button],
        });

        function createCheckbox() {
            return {
                tag: 'label',
                children: [
                    {
                        tag: 'input',
                        attributes: {
                            id: 'disable',
                            type: 'checkbox',
                            checked: bind.to('checked'),
                            style: 'margin-right: 0.5em'
                        },
                        on: {
                            change: bind.to('checking'),
                        },
                    },
                    {
                        text: $gettext('WYSIWYG Editor ausschalten'),
                    },
                ],
            };
        }

        function createHelpText() {
            return {
                tag: 'p',
                attributes: {
                    style: 'white-space: normal; font-size: 1em; line-height: 1.5em; margin-bottom: 1em;',
                },
                children: [
                    {
                        text: $gettext(
                            'Mit dieser Einstellung können Sie den WYSIWYG Editor ausschalten. Dadurch müssen Sie gegebenenfalls Texte in HTML schreiben. Der Editor wird erst vollständig entfernt, wenn die Seite neu geladen wird.'
                        ),
                    },
                ],
            };
        }

        function createButton() {
            const button = new ButtonView(locale);

            button.set({
                label: $gettext('Speichern'),
                withText: true,
                isEnabled: bind.to('functional'),
            });

            return button;
        }
    }
}