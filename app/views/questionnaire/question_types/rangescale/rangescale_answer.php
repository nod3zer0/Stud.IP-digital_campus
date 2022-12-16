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
$responseData = $response['answerdata'] && $response['answerdata']['answers'] ? $response['answerdata']['answers']->getArrayCopy() : [];
?>
<div <?= isset($vote->questiondata['mandatory']) && $vote->questiondata['mandatory'] ? ' class="mandatory"' : "" ?>>
    <?= $this->render_partial('questionnaire/_answer_description_container', ['vote' => $vote, 'iconshape' => 'rangescale']) ?>

    <div class="hidden invalidation_notice">
        <?= _("Diese Frage muss beantwortet werden.") ?>
    </div>

    <table class="default nohover answers">
        <thead>
            <tr>
                <th><?= _('Aussage') ?></th>
                <? for ($i = $vote->questiondata['minimum'] ?? 1; $i <= $vote->questiondata['maximum']; $i++) : ?>
                <th><?= htmlReady($i) ?></th>
                <? endfor ?>
            </tr>
        </thead>
        <tbody>
            <? foreach ($indexMap as $index) : ?>
            <tr>
                <? $html_id = md5(uniqid($index)) ?>
                <td id="<?= $html_id ?>"><?= htmlReady($statements[$index]) ?></td>
                <? for ($i = $vote->questiondata['minimum'] ?? 1; $i <= $vote->questiondata['maximum']; $i++) : ?>
                    <td>
                        <input type="radio"
                               title="<?= htmlReady($i) ?>"
                               aria-labelledby="<?= $html_id ?>"
                               name="answers[<?= $vote->getId() ?>][answerdata][answers][<?= htmlReady($index) ?>]"
                               <?= $responseData[$index] == $i ? 'checked' : '' ?>
                               value="<?= htmlReady($i) ?>">
                    </td>
                <? endfor ?>
            </tr>
            <? endforeach ?>
        </tbody>
    </table>
</div>
