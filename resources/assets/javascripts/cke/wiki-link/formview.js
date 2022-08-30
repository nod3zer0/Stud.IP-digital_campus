import {
    View,
    ButtonView,
    FormHeaderView,
    LabeledFieldView,
    FocusCycler,
    createLabeledInputText,
    submitHandler,
    ViewCollection,
    injectCssTransitionDisabler,
    createDropdown,
    addListToDropdown,
} from 'ckeditor5/src/ui';
import { FocusTracker, KeystrokeHandler, Collection, Rect, isVisible } from 'ckeditor5/src/utils';
import { $gettext } from '../../lib/gettext.js';

export default class WikiLinkFormView extends View {
    constructor(locale) {
        super(locale);

        const t = locale.t;

        this._keywordInputView = this._createInputField($gettext('Wikiseite'), $gettext('zum Beispiel "Wiki-Startseite"'));
        this._labelInputView = this._createInputField($gettext('Linktext'), $gettext('optional'));
        this._insertButtonView = this._createButton({
            label: $gettext('Einfügen'),
            withText: true,
        });

        this._keywordFieldsetView = this._createKeywordFieldset();
        this._focusTracker = new FocusTracker();

        this._keystrokes = new KeystrokeHandler();
        this._focusables = new ViewCollection();

        this._focusCycler = new FocusCycler({
            focusables: this._focusables,
            focusTracker: this._focusTracker,
            keystrokeHandler: this._keystrokes,
            actions: {
                focusPrevious: 'shift + tab',
                focusNext: 'tab',
            },
        });

        this.setTemplate({
            tag: 'form',
            attributes: {
                class: ['ck', 'ck-studip-wiki-link-form'],

                tabindex: '-1',
            },
            children: [
                new FormHeaderView(locale, {
                    label: $gettext('Link auf Wikiseite einfügen'),
                }),
                this._keywordFieldsetView,
            ],
        });

        injectCssTransitionDisabler(this);
    }

    render() {
        super.render();

        submitHandler({ view: this });

        this._initFocusCycling();
        this._initKeystrokeHandling();
    }

    destroy() {
        super.destroy();

        this._focusTracker.destroy();
        this._keystrokes.destroy();
    }

    focus() {
        this._focusCycler.focusFirst();
    }

    reset() {
        this._keywordInputView.fieldView.element.value = '';
        this._keywordInputView.errorText = null;
        this._labelInputView.fieldView.element.value = '';
    }

    get _keywordToInsert() {
        return this._keywordInputView.fieldView.element.value;
    }

    get _labelToInsert() {
        return this._labelInputView.fieldView.element.value;
    }

    _createKeywordFieldset() {
        const locale = this.locale;
        const fieldsetView = new View(locale);

        this._insertButtonView.on('execute', this._onInsertButtonExecute.bind(this));

        fieldsetView.setTemplate({
            tag: 'fieldset',
            attributes: {
                class: ['ck'],
            },
            children: [this._keywordInputView, this._labelInputView, this._insertButtonView],
        });

        return fieldsetView;
    }

    _onInsertButtonExecute() {
        if (!this._keywordToInsert) {
            this._keywordInputView.errorText = $gettext('Das Feld für die Wikiseite darf nicht leer sein.');

            return;
        }
        this.fire('insert', {
            keyword: this._keywordToInsert,
            label: this._labelToInsert,
        });
    }

    _initFocusCycling() {
        const childViews = [this._keywordInputView, this._labelInputView, this._insertButtonView];

        childViews.forEach((v) => {
            this._focusables.add(v);
            this._focusTracker.add(v.element);
        });
    }

    _initKeystrokeHandling() {
        const stopPropagation = (data) => data.stopPropagation();
        const stopPropagationAndPreventDefault = (data) => {
            data.stopPropagation();
            data.preventDefault();
        };

        this._keystrokes.listenTo(this.element);
        this._keystrokes.set('enter', (event) => {
            const target = event.target;

            if (
                target === this._keywordInputView.fieldView.element ||
                target === this._labelInputView.fieldView.element
            ) {
                this._insertButtonView.fire('execute');
                stopPropagationAndPreventDefault(event);
            }
        });

        this._keystrokes.set('arrowright', stopPropagation);
        this._keystrokes.set('arrowleft', stopPropagation);
        this._keystrokes.set('arrowup', stopPropagation);
        this._keystrokes.set('arrowdown', stopPropagation);

        this.listenTo(
            this._keywordInputView.element,
            'selectstart',
            (evt, domEvt) => {
                domEvt.stopPropagation();
            },
            { priority: 'high' }
        );
    }

    _createButton(options) {
        const button = new ButtonView(this.locale);
        button.set(options);

        return button;
    }

    _createInputField(label, infoText = '') {
        const labeledInput = new LabeledFieldView(this.locale, createLabeledInputText);
        labeledInput.label = label;
        labeledInput.infoText = infoText;

        return labeledInput;
    }
}
