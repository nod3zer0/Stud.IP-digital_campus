<?php
/**
 * @var QuestionnaireQuestion $vote
 */

$answers = $vote->questiondata['options'] ?? [];
$statements = $vote->questiondata['statements'] ?? [];
$indexMap = count($statements) ? range(0, count($statements) - 1) : [];
if ($vote->questiondata['randomize']) {
    shuffle($indexMap);
}

$response = $vote->getMyAnswer();
$responseData = isset($response->answerdata['answers']) ? $response->answerdata['answers']->getArrayCopy() : [];
?>
<div <?= isset($vote->questiondata['mandatory']) && $vote->questiondata['mandatory'] ? ' class="mandatory"' : "" ?>>
    <?= $this->render_partial('questionnaire/_answer_description_container', ['vote' => $vote, 'iconshape' => 'question-likert']) ?>

    <div class="hidden invalidation_notice">
        <?= _("Diese Frage muss beantwortet werden.") ?>
    </div>

    <table class="default nohover answers">
        <thead>
            <tr>
                <th><?= _('Aussage') ?></th>
                <? foreach ($answers as $answer) : ?>
                <th><?= htmlReady($answer) ?></th>
                <? endforeach ?>
            </tr>
        </thead>
        <tbody>
            <? foreach ($indexMap as $index) : ?>
            <tr>
                <? $html_id = md5(uniqid($index)) ?>
                <td id="<?= $html_id ?>"><?= htmlReady($statements[$index]) ?></td>
                <? foreach ($answers as $answer_index => $answer) : ?>
                    <td>
                        <input type="radio"
                               title="<?= htmlReady($answer) ?>"
                               aria-labelledby="<?= $html_id ?>"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers][<?= htmlReady($index) ?>]"
                                <?= isset($responseData[$index]) && $responseData[$index] === $answer_index ? 'checked' : '' ?>
                               value="<?= htmlReady($answer_index) ?>">
                    </td>
                <? endforeach ?>
            </tr>
            <? endforeach ?>
        </tbody>
    </table>
</div>
