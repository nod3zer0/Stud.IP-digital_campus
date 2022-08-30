import { Plugin } from 'ckeditor5/src/core';
import InsertCommand from './insertcommand';

export default class WikiLinkEditing extends Plugin {
    static get pluginName() {
        return 'WikiLinkEditing';
    }

    init() {
        this._defineCommands();
    }

    _defineCommands() {
        this.editor.commands.add('insertStudipWikiLink', new InsertCommand(this.editor));
    }
}
