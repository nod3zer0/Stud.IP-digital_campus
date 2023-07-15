<?php
/**
 * @var Questionnaire $questionnaire
 */
$questiontypes = [];
$questiontypes['Vote'] = [
    'name' => Vote::getName(),
    'type' => Vote::class,
    'icon' => Vote::getIconShape(),
    'component' => Vote::getEditingComponent()
];
foreach (get_declared_classes() as $class) {
    if (is_subclass_of($class, 'QuestionType')) {
        if (!isset($questiontypes[$class])) {
            $questiontypes[$class] = [
                'name' => $class::getName(),
                'type' => $class,
                'icon' => $class::getIconShape(),
                'component' => $class::getEditingComponent()
            ];
        }
    }
}
$questionnaire_data = [
    'id' => $questionnaire->getId(),
    'title' => $questionnaire['title'],
    'startdate' => $questionnaire->isNew() ? _('sofort') : $questionnaire['startdate'],
    'stopdate' => $questionnaire['stopdate'],
    'copyable' => $questionnaire['copyable'],
    'anonymous' => $questionnaire['anonymous'],
    'editanswers' => $questionnaire['editanswers'],
    'resultvisibility' => $questionnaire['resultvisibility'],
];
$questions_data = [];
foreach ($questionnaire->questions as $question) {
    $questions_data[] = [
        'id' => $question->getId(),
        'questiontype' => $question['questiontype'],
        'internal_name' => $question['internal_name'],
        'questiondata' => $question['questiondata']->getArrayCopy()
    ];
}
?>
<form action="<?= URLHelper::getLink("dispatch.php/questionnaire/edit/".(!$questionnaire->isNew() ? $questionnaire->getId() : "")) ?>"
      method="post"
      enctype="multipart/form-data"
      class="questionnaire_edit default"
      data-questiontypes="<?= htmlReady(json_encode($questiontypes)) ?>"
      data-questionnaire_data="<?= htmlReady(json_encode($questionnaire_data)) ?>"
      data-questions_data="<?= htmlReady(json_encode($questions_data)) ?>"
      data-range_type="<?= htmlReady(Request::get('range_type')) ?>"
      data-range_id="<?= htmlReady(Request::get('range_id')) ?>"
    <?= Request::isAjax() ? "data-dialog" : "" ?>
      :data-secure="activateFormSecure">


    <div class="editor">
        <div class="rightside" aria-live="polite" tabindex="0" ref="rightside">
            <div class="admin" v-if="activeTab === 'admin'">

                <article aria-live="assertive"
                         class="validation_notes studip">
                    <header>
                        <h1>
                            <?= Icon::create('info-circle', Icon::ROLE_INFO)->asImg(17, ['class' => "text-bottom validation_notes_icon"]) ?>
                            <?= _('Hinweise zum Ausfüllen des Formulars') ?>
                        </h1>
                    </header>
                    <div class="required_note">
                        <div aria-hidden="true">
                            <?= _('Pflichtfelder sind mit Sternchen gekennzeichnet.') ?>
                        </div>
                        <div class="sr-only">
                            <?= _('Dieses Formular enthält Pflichtfelder.') ?>
                        </div>
                    </div>
                    <div v-if="validationNotice && !data.title">
                        <?= _('Folgende Angaben müssen korrigiert werden, um das Formular abschicken zu können:') ?>
                        <ul>
                            <li aria-describedby="questionnaire_title"><?= _('Titel des Fragebogens') ?></li>
                        </ul>
                    </div>
                </article>

                <div class="formpart">
                    <label class="studiprequired" for="questionnaire_title">
                        <span class="textlabel"><?= _('Titel des Fragebogens') ?></span>
                        <span title="Dies ist ein Pflichtfeld" aria-hidden="true" class="asterisk">*</span>
                    </label>
                    <input type="text" id="questionnaire_title" v-model="data.title" ref="autofocus">
                </div>

                <div class="hgroup">
                    <label>
                        <?= _('Startzeitpunkt') ?>
                        <datetimepicker v-model="data.startdate"></datetimepicker>
                    </label>
                    <label>
                        <?= _('Endzeitpunkt') ?>
                        <datetimepicker v-model="data.stopdate"></datetimepicker>
                    </label>
                </div>
                <label>
                    <input type="checkbox" v-model="data.copyable" true-value="1" false-value="0">
                    <?= _('Fragebogen zum Kopieren freigeben') ?>
                </label>
                <label>
                    <input type="checkbox" v-model="data.anonymous" true-value="1" false-value="0">
                    <?= _('Teilnehmende anonymisieren') ?>
                </label>
                <label>
                    <input type="checkbox" v-model="data.editanswers" true-value="1" false-value="0">
                    <?= _('Teilnehmende dürfen ihre Antworten revidieren') ?>
                </label>
                <label>
                    <?= _('Ergebnisse einsehbar') ?>
                    <select v-model="data.resultvisibility">
                        <option value="always"><?= _('Immer') ?></option>
                        <option value="afterending"><?= _('Nach Ende der Befragung') ?></option>
                        <option value="afterparticipation"><?= _('Nach der Teilnahme') ?></option>
                        <option value="never"><?= _('Niemals') ?></option>
                    </select>
                </label>
            </div>
            <div class="add_question file_select_possibilities" v-else-if="activeTab === 'add_question'">
                <div>
                    <button v-for="(questiontype, key) in questiontypes" :key="key"
                       :ref="key == Object.keys(questiontypes)[0] ? 'autofocus' : ''"
                       href=""
                       @click.prevent="addQuestion(questiontype.type)">
                        <studip-icon :shape="questiontype.icon" :size="40" role="clickable"></studip-icon>
                        {{questiontype.name}}
                    </button>
                </div>
            </div>
            <div v-else>
                <? foreach ($questiontypes as $questiontype) : ?>
                <component is="<?= htmlReady($questiontype['component'][0]) ?>"
                           v-if="questiontypes[questions[getIndexForQuestion(activeTab)].questiontype].component[0] === '<?= htmlReady($questiontype['component'][0]) ?>'"
                           v-model="questions[getIndexForQuestion(activeTab)].questiondata"
                           :question_id="questions[getIndexForQuestion(activeTab)].id">
                </component>
                <? endforeach ?>
            </div>
        </div>
        <aside>
            <a :class="activeTab === 'admin' ? 'admin active' : 'admin'"
               href=""
               @click.prevent="switchTab('admin')">
                <span class="icon"><studip-icon shape="evaluation" role="clickable" :size="30" alt=""></studip-icon></span>
                <?= _('Einstellungen') ?>
            </a>
            <draggable v-if="questions.length > 0" v-model="questions" handle=".drag-handle" group="questions" class="questions_container questions">
                <div v-for="question in questions"
                     :key="question.id"
                     @mouseenter="hoverTab = question.id"
                     @mouseleave="hoverTab = null"
                     :class="(activeTab === question.id || activeTab === 'meta_' + question.id ? 'active' : '') + (hoverTab === question.id ? ' hovered' : '')">
                    <a href="#"
                       @click.prevent="switchTab(question.id)">
                        <span class="drag-handle"></span>
                        <span class="icon type">
                            <studip-icon :shape="questiontypes[question.questiontype].icon" role="clickable" :size="30" alt=""></studip-icon>
                        </span>

                        <div v-if="editInternalName !== question.id">{{ question.internal_name || questiontypes[question.questiontype].name}}</div>
                        <div v-else class="inline_editing">
                            <input type="text" ref="editInternalName" v-model="tempInternalName" class="inlineediting_internal_name">
                            <button @click="saveInternalName(question.id)">
                                <studip-icon shape="accept" role="clickable" :size="20" title="<?= _('Internen Namen speichern') ?>"></studip-icon>
                            </button>
                            <button @click="editInternalName = null">
                                <studip-icon shape="decline" role="clickable" :size="20" title="<?= _('Internen Namen nicht speichern') ?>"></studip-icon>
                            </button>
                        </div>
                    </a>

                    <studip-action-menu :items="[{label: '<?= _('Umbenennen') ?>', icon: 'edit', emit: 'rename'}, {label: '<?= _('Frage kopieren') ?>', icon: 'copy', emit: 'copy'}, {label: '<?= _('Frage nach oben verschieben') ?>', icon: 'arr_1up', emit: 'moveup'}, {label: '<?= _('Frage nach unten verschieben') ?>', icon: 'arr_1down', emit: 'movedown'}, {label: '<?= _('Frage löschen') ?>', icon: 'trash', emit: 'delete'}]"
                                        @copy="duplicateQuestion(question.id)"
                                        @rename="renameInternalName(question.id)"
                                        @moveup="moveQuestionUp(question.id)"
                                        @movedown="moveQuestionDown(question.id)"
                                        @delete="askForDeletingTheQuestion(question.id)"></studip-action-menu>
                </div>
            </draggable>
            <a :class="activeTab === 'add_question' ? 'add_question active' : 'add_question'"
               href=""
               @click.prevent="switchTab('add_question')">
                <span class="icon"><studip-icon shape="add" role="clickable" :size="30" alt=""></studip-icon></span>
                <?= _('Element hinzufügen') ?>
            </a>
        </aside>
    </div>


    <studip-dialog
        v-if="askForDeletingQuestions"
        title="<?= _('Bitte bestätigen Sie die Aktion') ?>"
        question="<?= _('Wirklich löschen?') ?>"
        confirmText="<?= _('Ja') ?>"
        closeText="<?= _('Nein') ?>"
        closeClass="cancel"
        height="180"
        @confirm="deleteQuestion"
        @close="askForDeletingQuestions = false"
    >
    </studip-dialog>

    <footer data-dialog-button>
        <?= \Studip\LinkButton::create(_("Speichern"), 'questionnaire_store', ['onclick' => 'STUDIP.Questionnaire.Editor.submit(); return false;']) ?>
    </footer>
</form>
