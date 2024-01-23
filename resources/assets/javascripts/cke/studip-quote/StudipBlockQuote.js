import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import { $gettext } from '../../lib/gettext';
import { Command, icons } from '@ckeditor/ckeditor5-core';

const divideIcon =
    '<svg version="1.1" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="m9.3 2h2v16h-2zm-7.2987 8.423a6.5 6.5 0 0 1 6.056-6.408l0.038 0.67c-2.646 0.738-3.74 2.978-3.874 5.315h3.78c0.552 0 0.5 0.432 0.5 0.986v4.511c0 0.554-0.448 0.503-1 0.503h-5c-0.552 0-0.5-0.449-0.5-1.003zm10 0a6.5 6.5 0 0 1 6.056-6.408l0.038 0.67c-2.646 0.739-3.74 2.979-3.873 5.315h3.779c0.552 0 0.5 0.432 0.5 0.986v4.511c0 0.554-0.448 0.503-1 0.503h-5c-0.552 0-0.5-0.449-0.5-1.003z" stroke-width="1.1664"/></svg>';

const removeIcon =
    '<svg version="1.1" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="m16.594 14.6 1.7829 1.7824a0.42044 0.42042 0 0 1-0.59459 0.59456l-1.7825-1.7828-1.7829 1.7828a0.42029 0.42027 0 0 1-0.59417-0.59456l1.7829-1.7824-1.7829-1.7828a0.42021 0.42018 0 0 1 0.59417-0.59414l1.7829 1.7828 1.7825-1.7828a0.42029 0.42027 0 1 1 0.59459 0.59414zm-13.594-4.1774a6.5 6.5 0 0 1 6.056-6.408l0.038 0.67c-2.646 0.738-3.74 2.978-3.874 5.315h3.78c0.552 0 0.5 0.432 0.5 0.986v4.511c0 0.554-0.448 0.503-1 0.503h-5c-0.552 0-0.5-0.449-0.5-1.003zm8 0c0.04006-3.3879 2.6758-6.1768 6.056-6.408l0.038 0.67c-2.646 0.739-3.74 2.979-3.873 5.315h3.779c0.552 0 0.49078 0.43208 0.5 0.986l0.0098 0.59148c-5.3063 0.37805-4.512-1.1664-4.5961 4.4172l-1.4137 0.005319c-0.552 0.002077-0.5-0.449-0.5-1.003z" stroke-width=".42019"/></svg>';

export default class StudipBlockQuote extends Plugin {
    init() {
        const editor = this.editor;

        editor.commands.add('insertStudipQuote', new InsertStudipQuoteCommand(editor));
        editor.commands.add('splitStudipQuote', new SplitStudipQuoteCommand(editor));
        editor.commands.add('removeStudipQuote', new RemoveStudipQuoteCommand(editor));

        editor.ui.componentFactory.add('insertBlockQuote', (locale) => {
            const view = new ButtonView(locale);

            view.set({
                label: $gettext('Zitat einfügen'),
                icon: icons.quote,
                tooltip: true,
            });

            const command = editor.commands.get('removeStudipQuote');
            view.bind('isEnabled').to(command, 'isEnabled');

            // Callback executed once the image is clicked.
            view.on('execute', () => {
                editor.execute('insertStudipQuote');
            });

            return view;
        });

        editor.ui.componentFactory.add('splitBlockQuote', (locale) => {
            const view = new ButtonView(locale);

            view.set({
                label: $gettext('Zitat teilen'),
                icon: divideIcon,
                keystroke: 'Ctrl+Shift+Enter',
                tooltip: true,
                withText: false,
            });

            const command = editor.commands.get('removeStudipQuote');
            view.bind('isEnabled').to(command, 'isEnabled');

            // Callback executed once the image is clicked.
            view.on('execute', () => {
                editor.execute('splitStudipQuote');
            });

            return view;
        });

        editor.ui.componentFactory.add('removeBlockQuote', (locale) => {
            const view = new ButtonView(locale);

            view.set({
                label: $gettext('Zitat löschen'),
                icon: removeIcon,
                tooltip: true,
                withText: false,
            });

            const command = editor.commands.get('removeStudipQuote');
            view.bind('isEnabled').to(command, 'isEnabled');

            // Callback executed once the image is clicked.
            view.on('execute', () => {
                editor.execute('removeStudipQuote');
            });

            return view;
        });
    }
}

class InsertStudipQuoteCommand extends Command {
    execute() {
        var writtenBy = $gettext('%s hat geschrieben:');

        const content =
            '<blockquote><div class="author">' +
            writtenBy.replace('%s', $gettext('"Name"')) +
            '</div><p>&nbsp</p></blockquote><p>&nbsp;</p>';
        const viewFragment = this.editor.data.processor.toView(content);
        const modelFragment = this.editor.data.toModel(viewFragment);
        this.editor.model.insertContent(modelFragment);
    }
}

class SplitStudipQuoteCommand extends Command {
    execute() {
        const position = this.editor.model.document.selection.getFirstPosition();
        const quote = position.findAncestor('blockQuote');

        if (quote !== null) {
            this.editor.model.change((writer) => {
                const limitElement = quote.parent;
                const split = writer.split(position, limitElement);
                writer.insertElement('paragraph', split.position);
            });
        }
    }
}

class RemoveStudipQuoteCommand extends Command {
    execute() {
        const position = this.editor.model.document.selection.getFirstPosition();
        const quote = position.findAncestor('blockQuote');

        if (quote !== null) {
            this.editor.model.change((writer) => {
                // Remove the top "written by" bar
                for (var child of quote.getChildren()) {
                    if (
                        child.is('element', 'htmlDivParagraph') &&
                        child.getAttribute('htmlAttributes').classes.includes('author')
                    ) {
                        writer.remove(child);
                    }
                }
                // Only remove the current quote - save all children
                const range = writer.createRangeIn(quote);
                writer.move(range, quote, 'after');
                writer.remove(quote);
            });
        }
    }

    static get pluginName() {
        return 'StudipBlockQuote';
    }
}
