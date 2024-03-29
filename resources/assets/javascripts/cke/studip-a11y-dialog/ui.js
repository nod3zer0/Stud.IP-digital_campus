import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import { Plugin } from '@ckeditor/ckeditor5-core';
import { $gettext } from '../../lib/gettext';

const a11yIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 54 54"><path d="M32.5,43h-11a1.5,1.5,0,0,0,0,3h11a1.5,1.5,0,0,0,0-3Z"/><path d="M31.5,48h-9a1.5,1.5,0,0,0,0,3h9a1.5,1.5,0,0,0,0-3Z"/><path d="M27,3a18.54,18.54,0,0,0-2,.11,17,17,0,0,0-6.95,31.37A2,2,0,0,1,19,36.13v3.34A1.5,1.5,0,0,0,20.5,41h13a1.5,1.5,0,0,0,1.5-1.5V36.12a2,2,0,0,1,.9-1.67A17,17,0,0,0,27,3Zm7.33,28.92A5,5,0,0,0,32,36.12V38H22V36.13a5,5,0,0,0-2.33-4.24,14,14,0,0,1,5.7-25.83A14.84,14.84,0,0,1,27,6a14,14,0,0,1,7.33,25.92Z"/><path d="M32.39,9.05A12.51,12.51,0,0,0,27.24,8a12.66,12.66,0,0,0-10.37,5.4,1.73,1.73,0,0,0,.42,2.41,1.69,1.69,0,0,0,1,.32,1.73,1.73,0,0,0,1.42-.74,9.21,9.21,0,0,1,7.54-3.93,9.08,9.08,0,0,1,3.74.8,1.73,1.73,0,1,0,1.41-3.16Z"/><path d="M17,16.31A1.73,1.73,0,0,0,15,17.58a12.38,12.38,0,0,0-.37,3,12.68,12.68,0,0,0,.28,2.67,1.74,1.74,0,0,0,1.69,1.36,1.55,1.55,0,0,0,.37,0,1.74,1.74,0,0,0,1.33-2.06A8.92,8.92,0,0,1,18,20.61a9.08,9.08,0,0,1,.27-2.2A1.74,1.74,0,0,0,17,16.31Z"/></svg>';

export default class A11YDialogUI extends Plugin {
    static get pluginName() {
        return 'A11YDialogUI';
    }

    init() {
        const editor = this.editor;
        editor.ui.componentFactory.add('open-a11y-dialog', (locale) => {
            const view = new ButtonView(locale);

            view.set({
                label: $gettext('Informationen zur Bedienung'),
                icon: a11yIcon,
                keystroke: 'ALT+0',
                tooltip: true,
            });

            view.on('execute', () => {
                editor.execute('a11ydialog');
            });

            return view;
        });
    }
}
