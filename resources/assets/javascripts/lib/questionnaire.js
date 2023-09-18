import { $gettext } from '../lib/gettext.js';
import md5 from 'md5';
//import html2canvas from "html2canvas";
//import {jsPDF} from "jspdf";

const Questionnaire = {
    delayedQueue: [],
    Editor: null,
    initEditor () {
        $('.questionnaire_edit:not(.vueified)').addClass('vueified').each(function () {
            STUDIP.Vue.load().then(({createApp}) => {
                let form = this;
                let components = {};
                let questiontypes = $(form).data('questiontypes');
                for (let i in questiontypes) {
                    if (questiontypes[i].component[0] && questiontypes[i].component[1]) {
                        //for plugins to be able to import their vue components:
                        components[questiontypes[i].component[0]] = () => import(/* webpackIgnore: true */ questiontypes[i].component[1]);
                    }
                }
                components.draggable = () => import('vuedraggable');
                components['vote-edit'] = () => import('../../../vue/components/questionnaires/VoteEdit.vue');
                components['freetext-edit'] = () => import('../../../vue/components/questionnaires/FreetextEdit.vue');
                components['likert-edit'] = () => import('../../../vue/components/questionnaires/LikertEdit.vue');
                components['rangescale-edit'] = () => import('../../../vue/components/questionnaires/RangescaleEdit.vue');
                components['questionnaire-info-edit'] = () => import('../../../vue/components/questionnaires/QuestionnaireInfoEdit.vue');
                STUDIP.Questionnaire.Editor = createApp({
                    el: form,
                    components,
                    data() {
                        return {
                            questiontypes,

                            questions: $(form).data('questions_data'),
                            activeTab: 'admin',
                            hoverTab: null,
                            data: $(form).data('questionnaire_data'),
                            form_secured: true,
                            oldData: {
                                questions: [],
                                data: {}
                            },
                            range_type: $(form).data('range_type'),
                            range_id: $(form).data('range_id'),
                            editInternalName: null,
                            tempInternalName: '',
                            validationNotice: false,
                        };
                    },
                    methods: {
                        addQuestion(questiontype) {
                            let id = md5(STUDIP.USER_ID + '_QUESTIONTYPE_' + Math.random());

                            this.questions.push({
                                id: id,
                                questiontype: questiontype,
                                internal_name: '',
                                questiondata: {},
                            });

                            this.activeTab = id;
                        },
                        submit() {
                            if (!this.data.title) {
                                this.switchTab('admin');
                                this.validationNotice = true;
                                return;
                            }
                            let data = {
                                title: this.data.title,
                                copyable: this.data.copyable,
                                anonymous: this.data.anonymous,
                                editanswers: this.data.editanswers,
                                startdate: this.data.startdate,
                                stopdate: this.data.stopdate,
                                resultvisibility: this.data.resultvisibility
                            };
                            let questions = [];
                            for (let i in this.questions) {
                                questions.push({
                                    id: this.questions[i].id,
                                    questiontype: this.questions[i].questiontype,
                                    internal_name: this.questions[i].internal_name,
                                    questiondata: Object.assign({}, this.questions[i].questiondata),
                                });
                            }
                            $.post(STUDIP.URLHelper.getURL('dispatch.php/questionnaire/store/' + (this.data.id || '')), {
                                questionnaire: data,
                                questions_data: questions,
                                range_type: this.range_type,
                                range_id: this.range_id
                            }).done(() => {
                                this.form_secured = false;
                                this.$nextTick(() => {
                                    location.reload();
                                });
                            }).fail(() => {
                                STUDIP.Report.error('Could not save questionnaire.');
                            });
                        },
                        getIndexForQuestion: function (question_id) {
                            for (let i in this.questions) {
                                if (this.questions[i].id === question_id || this.questions[i].id === question_id.substring(5)) {
                                    return typeof i === "string" ? parseInt(i, 10) : i;
                                }
                            }
                        },
                        duplicateQuestion: function (question_id) {
                            let i = this.getIndexForQuestion(question_id);
                            let id = md5(STUDIP.USER_ID + '_QUESTIONTYPE_' + Math.random());
                            this.questions.push({
                                id: id,
                                questiontype: this.questions[i].questiontype,
                                internal_name: this.questions[i].internal_name,
                                questiondata: Object.assign({}, this.questions[i].questiondata)
                            });
                            this.activeTab = id;
                        },
                        deleteQuestion(question_id) {
                            STUDIP.Dialog.confirm(this.$gettext('Wirklich löschen?')).done(() => {
                                this.$delete(this.questions, this.getIndexForQuestion(question_id));
                                this.switchTab('add_question');
                            })
                        },
                        switchTab(tab_id) {
                            this.activeTab = tab_id;
                            this.$nextTick(function () {
                                if (this.$refs.autofocus !== undefined) {
                                    if (Array.isArray(this.$refs.autofocus)) {
                                        if (typeof this.$refs.autofocus[0] !== "undefined") {
                                            this.$refs.autofocus[0].focus();
                                        }
                                    } else {
                                        this.$refs.autofocus.focus();
                                    }
                                }
                            });
                        },
                        objectsEqual(obj1, obj2) {
                            return _.isEqual(obj1, obj2);
                        },
                        renameInternalName(question_id) {
                            this.editInternalName = question_id;
                            let index = this.getIndexForQuestion(question_id);
                            this.tempInternalName = this.questions[index].internal_name;
                            this.$nextTick(() => {
                                this.$refs.editInternalName[0].focus();
                            });
                        },
                        saveInternalName(question_id) {
                            let index = this.getIndexForQuestion(question_id);
                            this.questions[index].internal_name = this.tempInternalName;
                            this.editInternalName = null;
                        },
                        moveQuestionDown(question_id) {
                            let index = this.getIndexForQuestion(question_id);
                            if (index < this.questions.length - 1) {
                                let question = this.questions[index];
                                this.questions[index] = this.questions[index + 1];
                                this.questions[index + 1] = question;
                                this.$forceUpdate();
                            }
                        },
                        moveQuestionUp(question_id) {
                            let index = this.getIndexForQuestion(question_id);
                            if (index > 0) {
                                let question = this.questions[index];
                                this.questions[index] = this.questions[index - 1];
                                this.questions[index - 1] = question;
                                this.$forceUpdate();
                            }
                        }
                    },
                    computed: {
                        activateFormSecure() {
                            let newData = {
                                questions: this.questions,
                                data: this.data
                            };
                            return this.form_secured && !this.objectsEqual(this.oldData, newData);
                        },
                        indexForQuestion() {
                            for (let i in this.questions) {
                                if (
                                    this.questions[i].id === this.activeTab ||
                                    this.questions[i].id === this.activeTab.substring(5)
                                ) {
                                    return typeof i === "string" ? parseInt(i, 10) : i;
                                }
                            }

                            return null;
                        },
                    },
                    mounted() {
                        this.$refs.autofocus.focus();
                        this.oldData = {
                            questions: [...this.questions],
                            data: Object.assign({}, this.data)
                        };
                    },
                });

            });
        });
    },
    delayedInterval: null,
    lastUpdate: null,
    filtered: {},
    initialize() {
        STUDIP.JSUpdater.register(
            'questionnaire',
            Questionnaire.updateQuestionnaireResults,
            Questionnaire.getParamsForPolling,
            15000
        );
    },
    getParamsForPolling: function() {
        var questionnaires = {
            questionnaire_ids: [],
            last_update: Questionnaire.lastUpdate,
            filtered: Questionnaire.filtered
        };
        Questionnaire.lastUpdate = Math.floor(Date.now() / 1000);
        jQuery('.questionnaire_results').each(function() {
            questionnaires.questionnaire_ids.push(jQuery(this).data('questionnaire_id'));
        });
        if (questionnaires.questionnaire_ids.length > 0) {
            return questionnaires;
        }
    },
    updateQuestionnaireResults: function(data) {
        for (var questionnaire_id in data) {
            if (data[questionnaire_id].html) {
                var new_view = jQuery(data[questionnaire_id].html);
                jQuery('.questionnaire_results.questionnaire_' + questionnaire_id).replaceWith(new_view);
                jQuery(document).trigger('dialog-open');
            }
        }
    },
    addFilter: function (questionnaire_id, question_id, answer) {
        Questionnaire.filtered[questionnaire_id] = {
            question_id: question_id,
            filterForAnswer: answer
        };
        $.ajax({
            url: STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/questionnaire/evaluate/' + questionnaire_id),
            data: {
                filtered: {
                    question_id: question_id,
                    filterForAnswer: answer
                }
            },
            success: Questionnaire.updateWidgetQuestionnaire,
            error: function () {
                window.alert('Cannot load page.');
            }
        });
    },
    removeFilter: function (questionnaire_id) {
        delete Questionnaire.filtered[questionnaire_id];
        $.ajax({
            url: STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/questionnaire/evaluate/' + questionnaire_id),
            success: Questionnaire.updateWidgetQuestionnaire,
            error: function () {
                window.alert('Cannot load page.');
            }
        });
    },
    updateOverviewQuestionnaire: function(data) {
        if (jQuery('#questionnaire_overview tr#questionnaire_' + data.questionnaire_id).length > 0) {
            jQuery('#questionnaire_overview tr#questionnaire_' + data.questionnaire_id).replaceWith(data.overview_html);
        } else {
            if (jQuery('#questionnaire_overview').length > 0) {
                jQuery(data.overview_html)
                    .hide()
                    .insertBefore('#questionnaire_overview > tbody > :first-child')
                    .delay(300)
                    .fadeIn();
                jQuery('#questionnaire_overview .noquestionnaires').remove();
            }
            if (data.message) {
                jQuery('.messagebox').hide();
                jQuery('#content').prepend(data.message);
            }
        }
        if (jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).length > 0) {
            if (data.widget_html) {
                jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).replaceWith(
                    data.widget_html
                );
            } else {
                jQuery('.questionnaire_widget .widget_questionnaire_' + data.questionnaire_id).remove();
            }
        } else {
            if (jQuery('.questionnaire_widget').length > 0 && data.widget_html) {
                jQuery('.ui-dialog-content').dialog('close');
                if (jQuery('.questionnaire_widget > article').length > 0) {
                    jQuery(data.widget_html)
                        .hide()
                        .insertBefore(
                            '.questionnaire_widget > article:first-of-type, .questionnaire_widget > section:first-of-type'
                        )
                        .delay(300)
                        .fadeIn();
                } else {
                    jQuery('.questionnaire_widget .noquestionnaires')
                        .replaceWith(data.widget_html)
                        .hide()
                        .delay(300)
                        .fadeIn();
                }
            } else {
                if (data.message) {
                    jQuery('.messagebox').hide();
                    jQuery('#content').prepend(data.message);
                    jQuery.scrollTo('#content', 400);
                }
            }
        }
        jQuery(document).trigger('dialog-open');
    },
    updateWidgetQuestionnaire: function(html) {
        //update the results of a questionnaire
        var questionnaire_id = jQuery(html).data('questionnaire_id');
        jQuery('.questionnaire_' + questionnaire_id).replaceWith(html);
        if (jQuery('.questionnaire_' + questionnaire_id).is('.ui-dialog .questionnaire_results')) {
            jQuery('.questionnaire_' + questionnaire_id + ' [data-dialog-button]').hide();
        }
    },
    beforeAnswer: function() {
        var form = jQuery(this).closest('form')[0];
        var questionnaire_id = jQuery(form)
            .closest('article')
            .data('questionnaire_id');
        let validated = true;

        //validation
        $(form).find("input, select, textarea").each(function () {
            if ($(this).is(":invalid")) {
                validated = false;
            }
        });

        $(form).find(".questionnaire_answer > article").each(function () {
            let question_type = $(this).data("question_type");
            if (typeof STUDIP.Questionnaire[question_type] !== "undefined"
                    && typeof STUDIP.Questionnaire[question_type].validator === "function") {
                if (!STUDIP.Questionnaire[question_type].validator.call(this)) {
                    validated = false;
                }
            }
        });

        if (!validated) {
            $(form).addClass("show_validation_hints");
            STUDIP.Report.warning($gettext("Noch nicht komplett ausgefüllt."), $gettext("Füllen Sie noch die rot markierten Stellen korrekt aus."));
            return false;
        }

        if (jQuery(form).is('.questionnaire_widget form')) {
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/questionnaire/submit/' + questionnaire_id,
                data: new FormData(form),
                cache: false,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function(output) {
                    jQuery(form).replaceWith(output);
                    jQuery(document).trigger('dialog-open');
                }
            });
            jQuery(form).css('opacity', '0.5');
            return false;
        } else {
            return true;
        }
    },
    Test: {
        updateCheckboxValues: function() {
            jQuery('.questionnaire_edit .question.test').each(function() {
                jQuery(this)
                    .find('.options > li')
                    .each(function(index, li) {
                        jQuery(li)
                            .find('input[type=checkbox]')
                            .val(index + 1);
                    });
            });
        }
    },
    Vote: {
        validator: function () {
            if ($(this).find(".mandatory").length > 0) {
                if ($(this).find(":selected, :checked").length === 0) {
                    $(this).find(".invalidation_notice").addClass("invalid");
                    return false;
                } else {
                    $(this).find(".invalidation_notice").removeClass("invalid");
                }
            }
            return true;
        }
    },
    LikertScale: {
        validator: function () {
            if ($(this).find(".mandatory").length > 0) {
                let invalid = false;
                $(this).find('table.answers tbody tr').each(function () {
                    if ($(this).find(':checked').length === 0) {
                        invalid = true;
                    }
                });
                if (invalid) {
                    $(this).find(".invalidation_notice").addClass("invalid");
                    return false;
                } else {
                    $(this).find(".invalidation_notice").removeClass("invalid");
                }
            }
            return true;
        }
    },
    RangeScale: {
        validator: function () {
            return Questionnaire.LikertScale.validator.call(this);
        }
    },


    exportEvaluationAsPDF: function () {
        window.scrollTo(0, 0);
        const html2canvas = import('html2canvas');
        const jsPDF = import('jspdf');
        jsPDF.then(function (jsPDF) {
            let pdfExporter = jsPDF.default;
            html2canvas.then(function (canvas) {
                let canvasCreator = canvas.default;

                let pdf = new pdfExporter({
                    orientation: 'portrait'
                });
                $(".questionnaire_results").addClass('print-view');

                let title = $(".questionnaire_results").data('title');

                let splitTitle = pdf.splitTextToSize(title, 180);
                pdf.text(splitTitle, 25, 20);

                let count_questions = $(".questionnaire_results .question").length;
                let questions_rendered = 0;
                let canvasses = [];

                let blobToDataURL = function (blob, callback) {
                    let a = new FileReader();
                    a.onload = function(e) {callback(e.target.result);}
                    a.readAsDataURL(blob);
                };

                $(".questionnaire_results .question").each(function (index) {
                    canvasCreator(this, {logging: false}).then(canvas => {
                        canvasses[index] = canvas;
                        questions_rendered++;
                        if (questions_rendered === count_questions) {
                            //then all renders are finished:
                            let height_sum = 0;
                            for (let i = 0; i < count_questions; i++) {
                                if (i === 0) {
                                    height_sum += 15;
                                }
                                let imgData = canvasses[i].toDataURL('image/png');

                                let height = Math.floor(160 / canvasses[i].width * canvasses[i].height);
                                if (height_sum + height > 240 && height < 240) {
                                    pdf.addPage();
                                    height_sum = 0;
                                }
                                pdf.addImage(imgData, 'JPEG',
                                    25,
                                    20 + height_sum,
                                    160,
                                    height,
                                    'image_' + i,
                                    'NONE',

                                );
                                height_sum += height + 10;
                            }
                            pdf.save(title + '.pdf');
                        }
                    });
                });
                $(".questionnaire_results").removeClass('print-view');
            })
        });

    },

    addDelayedInit(el, data, isAjax, isMultiple) {
        this.delayedQueue.push({
            el,
            data,
            isAjax,
            isMultiple,
            $el: $(el), // jQueried element (for performance reasons
            visible: false
        });

        if (this.delayedInterval === null) {
            this.delayedInterval = setInterval(() => {
                this.delayedQueue.forEach(item => {
                    if (item.$el.is(':visible')) {
                        this.initVoteEvaluation(item.el, item.data, item.isAjax, item.isMultiple);
                        item.visible = true;
                    }
                });

                this.delayedQueue = this.delayedQueue.filter(item => !item.visible);
                if (this.delayedQueue.length === 0) {
                    clearInterval(this.delayedInterval);
                }
            }, 100);
        }
    },
    initVoteEvaluation: async function (el, data, isAjax, isMultiple) {
        if ($(el).is(':not(:visible)')) {
            if (!$(el).data('vote-evaluation-delayed')) {
                this.addDelayedInit(el, data, isAjax, isMultiple);

                $(el).data('vote-evaluation-delayed', true);
            }

            return;
        }

        const Chartist = await STUDIP.loadChunk('chartist');

        jQuery(enhance);

        function enhance() {
            if (isMultiple) {
                new Chartist.Bar(
                    el,
                    data,
                    { onlyInteger: true, axisY: { onlyInteger: true } }
                );
            } else {
                data.series = data.series[0];
                new Chartist.Pie(
                    el,
                    data,
                    { labelPosition: 'outside' }
                );
            }
        }
    },
    initTestEvaluation: async function (el, data, isAjax, isMultiple) {
        this.initVoteEvaluation(el, data, isAjax, isMultiple);
    },
};

export default Questionnaire;
