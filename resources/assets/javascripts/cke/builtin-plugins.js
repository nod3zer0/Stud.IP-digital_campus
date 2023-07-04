/* ckeditor official */
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
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
import HtmlComment from '@ckeditor/ckeditor5-html-support/src/htmlcomment';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageResizePlugin from '@ckeditor/ckeditor5-image/src/imageresize';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import Indent from '@ckeditor/ckeditor5-indent/src/indent';
import IndentBlock from '@ckeditor/ckeditor5-indent/src/indentblock';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import List from '@ckeditor/ckeditor5-list/src/list';
import ListProperties from '@ckeditor/ckeditor5-list/src/listproperties';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import RemoveFormat from '@ckeditor/ckeditor5-remove-format/src/removeformat.js';
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
import StudipWikiLink from '../cke/wiki-link/wiki-link.js';
import SpecialCharactersSmiley from '../cke/special_characters/SpecialCharactersSmiley.js';
import SpecialCharactersGreek from '../cke/special_characters/SpecialCharactersGreek.js';

import { BlockToolbar } from '@ckeditor/ckeditor5-ui';

const builtinPlugins = [
    ImageUploadPlugin,
    Alignment,
    Autoformat,
    BlockQuote,
    BoldPlugin,
    CodeBlock,
    EssentialsPlugin,
    FindAndReplace,
    FontColor,
    FontBackgroundColor,
    GeneralHtmlSupport,
    HeadingPlugin,
    HorizontalLine,
    HtmlComment,
    ImageCaptionPlugin,
    ImagePlugin,
    ImageResizePlugin,
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
    SourceEditing,
    SpecialCharacters,
    SpecialCharactersCurrency,
    SpecialCharactersSmiley,
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
    BlockToolbar,
];

export { builtinPlugins };
