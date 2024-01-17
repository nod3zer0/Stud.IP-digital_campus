import { Command } from '@ckeditor/ckeditor5-core';

export default class InsertCommand extends Command {
    refresh() {
        const model = this.editor.model;
        const selection = model.document.selection;
        const allowedIn = model.schema.findAllowedParent(selection.getFirstPosition(), '$text');
        this.isEnabled = allowedIn !== null;
    }

    execute({ keyword, label }) {
        this.editor.model.change((writer) => {
            this.editor.model.insertContent(
                writer.createText(label !== '' ? `[[ ${keyword} | ${label} ]]` : `[[ ${keyword} ]]`)
            );
        });
    }
}
