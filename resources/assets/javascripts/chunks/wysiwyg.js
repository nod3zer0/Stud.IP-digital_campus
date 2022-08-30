/* ckeditor official */
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ClassicEditorBase from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import Code from '@ckeditor/ckeditor5-basic-styles/src/code';
import CodeBlock from '@ckeditor/ckeditor5-code-block/src/codeblock';
import EasyImagePlugin from '@ckeditor/ckeditor5-easy-image/src/easyimage';
import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import FileRepository from '@ckeditor/ckeditor5-upload/src/filerepository';
import FindAndReplace from '@ckeditor/ckeditor5-find-and-replace/src/findandreplace';
import FontBackgroundColor from '@ckeditor/ckeditor5-font/src/fontbackgroundcolor.js';
import FontColor from '@ckeditor/ckeditor5-font/src/fontcolor.js';
import GeneralHtmlSupport from '@ckeditor/ckeditor5-html-support/src/generalhtmlsupport';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';
import HorizontalLine from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import Indent from '@ckeditor/ckeditor5-indent/src/indent';
import IndentBlock from '@ckeditor/ckeditor5-indent/src/indentblock';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import LanguageDe from '@ckeditor/ckeditor5-build-classic/build/translations/de.js';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import List from '@ckeditor/ckeditor5-list/src/list';
import ListProperties from '@ckeditor/ckeditor5-list/src/listproperties';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import RemoveFormat from '@ckeditor/ckeditor5-remove-format/src/removeformat.js';
import SelectAll from '@ckeditor/ckeditor5-select-all/src/selectall';
import SourceEditing from '@ckeditor/ckeditor5-source-editing/src/sourceediting';
import SpecialCharacters from '@ckeditor/ckeditor5-special-characters/src/specialcharacters.js';
import SpecialCharactersCurrency from '@ckeditor/ckeditor5-special-characters/src/specialcharacterscurrency.js';
import SpecialCharactersEssentials from '@ckeditor/ckeditor5-special-characters/src/specialcharactersessentials.js';
import SpecialCharactersLatin from '@ckeditor/ckeditor5-special-characters/src/specialcharacterslatin.js';
import SpecialCharactersMathematical from '@ckeditor/ckeditor5-special-characters/src/specialcharactersmathematical.js';
import SpecialCharactersText from '@ckeditor/ckeditor5-special-characters/src/specialcharacterstext.js';
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough.js';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript.js';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript.js';
import Table from '@ckeditor/ckeditor5-table/src/table.js';
import TableCaption from '@ckeditor/ckeditor5-table/src/tablecaption.js';
import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties';
import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar.js';
import TextTransformation from '@ckeditor/ckeditor5-typing/src/texttransformation';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline.js';
/* ckeditor third party */
import Mathematics from 'ckeditor5-math/src/math';
/* ckeditor elan */
import StudipA11YDialog, { updateVoiceLabel } from '../cke/studip-a11y-dialog/a11y-dialog.js';
import StudipBlockQuote from '../cke/studip-quote/StudipBlockQuote.js';
import StudipUpload from '../cke/StudipUpload.js';
import { StudipSettings } from '../cke/StudipSettings.js';
import StudipWikiLink from '../cke/wiki-link/wiki-link.js';
import SpecialCharactersEmojiFood from '../cke/special_characters/SpecialCharactersEmojiFood.js';
import SpecialCharactersEmojiNature from '../cke/special_characters/SpecialCharactersEmojiNature.js';
import SpecialCharactersEmojiObjects from '../cke/special_characters/SpecialCharactersEmojiObjects.js';
import SpecialCharactersEmojiPeople from '../cke/special_characters/SpecialCharactersEmojiPeople.js';
import SpecialCharactersEmojiSport from '../cke/special_characters/SpecialCharactersEmojiSport.js';
import SpecialCharactersEmojiSymbols from '../cke/special_characters/SpecialCharactersEmojiSymbols.js';
import SpecialCharactersEmojiTraffic from '../cke/special_characters/SpecialCharactersEmojiTraffic.js';
import SpecialCharactersGreek from '../cke/special_characters/SpecialCharactersGreek.js';
import '../../stylesheets/studip-cke-ui.css';

export default class ClassicEditor extends ClassicEditorBase {}

ClassicEditor.builtinPlugins = [
    ImageUploadPlugin,
    Alignment,
    Autoformat,
    BlockQuote,
    BoldPlugin,
    Code,
    CodeBlock,
    EssentialsPlugin,
    FindAndReplace,
    FontColor,
    FontBackgroundColor,
    GeneralHtmlSupport,
    HeadingPlugin,
    HorizontalLine,
    ImageCaptionPlugin,
    ImagePlugin,
    ImageStylePlugin,
    ImageToolbarPlugin,
    Indent,
    IndentBlock,
    ItalicPlugin,
    LinkPlugin,
    ListProperties,
    Mathematics,
    Paragraph,
    RemoveFormat,
    SelectAll,
    SourceEditing,
    SpecialCharacters,
    SpecialCharactersCurrency,
    SpecialCharactersEmojiPeople,
    SpecialCharactersEmojiNature,
    SpecialCharactersEmojiFood,
    SpecialCharactersEmojiSport,
    SpecialCharactersEmojiTraffic,
    SpecialCharactersEmojiObjects,
    SpecialCharactersEmojiSymbols,
    SpecialCharactersEssentials,
    SpecialCharactersGreek,
    SpecialCharactersLatin,
    SpecialCharactersMathematical,
    SpecialCharactersText,
    Strikethrough,
    StudipBlockQuote,
    StudipUpload,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableCellProperties,
    TableProperties,
    TableToolbar,
    TextTransformation,
    Underline,
    FileRepository,
    StudipA11YDialog,
    StudipSettings,
    StudipWikiLink,
];

const customColorPalette = [
    { color: '#000000' },
    { color: '#6c737a' }, //75%
    { color: '#a7abaf' }, //45%
    { color: '#c4c7c9' }, //30%
    { color: '#ffffff', hasBorder: true },

    { color: '#cb1800' }, //red
    { color: '#f26e00' }, //pumpkin
    { color: '#ffbd33' }, //yellow
    { color: '#8bbd40' }, // apple green
    { color: '#00962d' }, //green

    { color: '#41afaa' }, //verdigris
    { color: '#a9b6cb' }, // blue 40%
    { color: '#28497c' }, // blue
    { color: '#bf5796' }, // mulberry
    { color: '#8656a2' }, // royal purple
];

ClassicEditor.defaultConfig = {
    toolbar: {
        items: [
            'undo',
            'redo',
            '|',
            'findAndReplace',
            'selectAll',
            '|',
            'specialCharacters',
            'horizontalLine',
            '|',
            'insertBlockQuote',
            'splitBlockQuote',
            'removeBlockQuote',
            '|',
            'link',
            'insertTable',
            'uploadImage',
            'codeBlock',
            'math',
            'studip-wiki',
            '|',
            'sourceEditing',
            '-',
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'subscript',
            'superscript',
            'code',
            'removeFormat',
            '|',
            'fontColor',
            'fontBackgroundColor',
            '|',
            'alignment:left',
            'alignment:right',
            'alignment:center',
            'alignment:justify',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'outdent',
            'indent',
            '|',
            'studipSettings',
            'open-a11y-dialog',
        ],
        shouldNotGroupWhenFull: true,
    },
    fontColor: {
        colors: customColorPalette,
    },
    fontBackgroundColor: {
        colors: customColorPalette,
    },
    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
        ],
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
            { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
            { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' },
            { model: 'code', view: 'code', title: 'Code', class: 'ck-heading_code' },
        ],
    },
    table: {
        contentToolbar: [
            'toggleTableCaption',
            'tableColumn',
            'tableRow',
            'mergeTableCells',
            'tableCellProperties',
            'tableProperties',
        ],
        tableProperties: {
            borderColors: customColorPalette,
            backgroundColors: customColorPalette,
        },
        tableCellProperties: {
            borderColors: customColorPalette,
            backgroundColors: customColorPalette,
        },
    },
    typing: {
        transformations: {
            remove: ['quotes'],
            extra: [
                { from: ':)', to: '🙂' },
                { from: '(:', to: '🙃' },
                { from: ':(', to: '🙁' },
                { from: ':D', to: '😄' },
                { from: 'C:', to: '😍' },
                { from: ':P', to: '😜' },
                { from: 'XD', to: '😂' },
                { from: ':O', to: '😮' },
                { from: '=O', to: '😲' },
                { from: ';)', to: '😉' },
                { from: ':S', to: '😟' },
                { from: ':=(', to: '😭' },
                { from: ":'(", to: '😢' },
                { from: ':$', to: '😳' },
                { from: ':X', to: '🤐' },
                { from: '8)', to: '😎' },
                { from: '<3', to: '❤️' },
                { from: ':*', to: '😘' },
                { from: ':+1:', to: '👍' },
                { from: ':-1:', to: '👎' },
                { from: ':rofl:', to: '🤣' },
                { from: ':heart_eyes:', to: '😍' },
                { from: ':sob:', to: '😭' },
                { from: ':cry:', to: '😢' },
                { from: ':fire:', to: '🔥' },
            ],
        },
    },
    list: {
        properties: {
            styles: true,
            startIndex: true,
            reversed: true,
        },
    },
    math: {
        engine: 'mathjax',
        outputType: 'span',
        forceOutputType: false,
        enablePreview: false,
    },
    link: {
        defaultProtocol: 'https://',
        decorators: {
            addTargetToExternalLinks: {
                mode: 'automatic',
                callback: url => /^(https?:)?\/\//.test( url ),
                attributes: {
                    target: '_blank',
                    rel: 'noopener noreferrer'
                }
            }
        }
    },
    language: 'de',
    htmlSupport: {
        allow: [
            /* HTML features to allow */
            {
                name: 'div',
                classes: 'author',
            },
        ],
        disallow: [
            /* HTML features to disallow */
        ],
    },
};

updateVoiceLabel();
