<?php
/**
 * @var QuestionnaireQuestion $vote
 */

$answers = $vote->questiondata['options'];
$indexMap = count($answers) ? range(0, count($answers) - 1) : [];
if ($vote->questiondata['randomize']) {
    shuffle($indexMap);
}

$response = $vote->getMyAnswer();
$responseData = $response['answerdata'] ? $response['answerdata']->getArrayCopy() : [];
?>
<div <?= isset($vote->questiondata['mandatory']) && $vote->questiondata['mandatory'] ? ' class="mandatory"' : "" ?>>
    <?= $this->render_partial('questionnaire/_answer_description_container', ['vote' => $vote, 'iconshape' => 'vote']) ?>

    <div class="hidden invalidation_notice">
        <?= _("Diese Frage muss beantwortet werden.") ?>
    </div>

    <ul class="clean">
        <? foreach ($indexMap as $index) : ?>
            <li>
                <label>
                    <? if ($vote->questiondata['multiplechoice']) : ?>

                        <input type="checkbox"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers][<?= $index ?>]"
                               value="<?= $index ?>"
                               <?= isset($responseData['answers']) && in_array($index, (array) $responseData['answers']) ? 'checked' : '' ?>>

                    <? else : ?>

                        <input type="radio"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers]"
                               value="<?= $index ?>"
                               <?= isset($responseData['answers']) && $index == $responseData['answers'] ? 'checked' : '' ?>>
                    <? endif ?>

                    <?= htmlReady($answers[$index]) ?>

                </label>
            </li>
        <? endforeach ?>
    </ul>
</div>
