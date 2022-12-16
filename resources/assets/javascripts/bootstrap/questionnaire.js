import {dialogReady, ready} from "../lib/ready";
STUDIP.ready(() => {
    STUDIP.Questionnaire.initEditor();
});

jQuery(document).on('change', '.show_validation_hints .questionnaire_answer [data-question_type=Vote] input', function() {
    STUDIP.Questionnaire.Vote.validator.call($(this).closest("article")[0]);
});
