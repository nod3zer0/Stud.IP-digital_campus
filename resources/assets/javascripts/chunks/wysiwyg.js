import BalloonEditor, { createBalloonEditorFromTextarea } from '../cke/balloon-editor.js';
import ClassicEditor, { createClassicEditorFromTextarea } from '../cke/classic-editor.js';
import { updateVoiceLabel } from '../cke/studip-a11y-dialog/a11y-dialog.js';

import '../../stylesheets/scss/studip-cke-ui.scss';

export {
    BalloonEditor,
    ClassicEditor,
    createBalloonEditorFromTextarea,
    createClassicEditorFromTextarea,
};

updateVoiceLabel();
