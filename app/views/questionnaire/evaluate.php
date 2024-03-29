<?php
/**
 * @var Questionnaire $questionnaire
 * @var array $filtered
 */
$only_user_ids = null;
if (isset($filtered[$questionnaire->getId()]) && $filtered[$questionnaire->getId()]['question_id']) {
    foreach ($questionnaire->questions as $question) {
        if ($question->getId() === $filtered[$questionnaire->getId()]['question_id']) {
            $only_user_ids = $question->getUserIdsOfFilteredAnswer($filtered[$questionnaire->getId()]['filterForAnswer']);
            break;
        }
    }
}

?>
<div class="questionnaire_results questionnaire_<?= $questionnaire->getId() ?>"
     data-questionnaire_id="<?= $questionnaire->getId() ?>"
     data-title="<?= htmlReady($questionnaire['title']) ?>">

    <? if ($questionnaire->resultsVisible()) : ?>
        <? foreach ($questionnaire->questions as $question) : ?>
            <article class="question question_<?= $question->getId() ?>">
                <? $template = $question->getResultTemplate(
                    $only_user_ids,
                    (
                        isset($filtered[$questionnaire->getId()]['question_id'])
                        && $filtered[$questionnaire->getId()]['question_id'] === $question->getId()
                    ) ? $filtered[$questionnaire->getId()]['filterForAnswer'] : null
                ) ?>
                <?= $template ? $template->render(['anonAnswers' => $anonAnswers ?? '']) : _("Ergebnisse konnten nicht ausgewertet werden.") ?>
            </article>
        <? endforeach ?>
    <? else : ?>
        <div style="margin-top: 13px;">
            <? if ($questionnaire['resultvisibility'] === "afterending") : ?>
                <?= MessageBox::info(_("Die Ergebnisse des Fragebogens werden veröffentlich, wenn die Befragung abgeschlossen ist.")) ?>
            <? else : ?>
                <?= MessageBox::info(_("Die Ergebnisse der Befragung werden nicht über Stud.IP ausgewertet.")) ?>
           <? endif ?>
        </div>
    <? endif ?>

    <div class="terms">
        <? if ($questionnaire['anonymous']) : ?>
            <?= _("Die Teilnahme ist anonym.") ?>
        <? else : ?>
            <?= _("Die Teilnahme ist nicht anonym.") ?>
        <? endif ?>
        <? if ($questionnaire['stopdate']) : ?>
            <?= sprintf(_("Sie können den Fragebogen beantworten bis zum %s um %s Uhr."), date("d.m.Y", $questionnaire['stopdate']), date("H:i", $questionnaire['stopdate'])) ?>
        <? endif ?>
    </div>

    <script>
        STUDIP.Questionnaire.lastUpdate = Math.floor(Date.now() / 1000);
        STUDIP.Questionnaire.initialize();
    </script>
    <div data-dialog-button style="max-height: none; opacity: 1; text-align: center;">
        <? if ($questionnaire->isAnswerable() && $questionnaire['editanswers']) : ?>
            <?= \Studip\LinkButton::create($questionnaire->isAnswered() ? _("Antwort ändern") : _("Beantworten"), URLHelper::getURL("dispatch.php/questionnaire/answer/".$questionnaire->getId()), ['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable()) : ?>
            <?= \Studip\LinkButton::create(_("Ergebnisse herunterladen"), URLHelper::getURL("dispatch.php/questionnaire/export/".$questionnaire->getId())) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && (!$questionnaire->isRunning() || !$questionnaire->countAnswers())) : ?>
            <?= \Studip\LinkButton::create(_("Bearbeiten"), URLHelper::getURL("dispatch.php/questionnaire/edit/".$questionnaire->getId()), ['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable()) : ?>
            <?= \Studip\LinkButton::create(_("Kontext auswählen"), URLHelper::getURL("dispatch.php/questionnaire/context/".$questionnaire->getId()), ['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($questionnaire->isCopyable()) : ?>
            <?= \Studip\LinkButton::create(_("Kopieren"), URLHelper::getURL("dispatch.php/questionnaire/copy/".$questionnaire->getId()), ['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && !$questionnaire->isRunning()) : ?>
            <?= \Studip\LinkButton::create(_("Starten"), URLHelper::getURL("dispatch.php/questionnaire/start/".$questionnaire->getId())) ?>
        <? endif ?>
        <? if ($questionnaire->resultsVisible()) : ?>
            <?= \Studip\LinkButton::create(_('PDF exportieren'), '#', ['onclick' => "STUDIP.Questionnaire.exportEvaluationAsPDF(); return false;"]) ?>
        <? endif ?>
        <? if ($questionnaire->isEditable() && $questionnaire->isRunning()) : ?>
            <?= \Studip\LinkButton::create(_("Beenden"), URLHelper::getURL("dispatch.php/questionnaire/stop/".$questionnaire->getId())) ?>
        <? endif ?>

    </div>
</div>
