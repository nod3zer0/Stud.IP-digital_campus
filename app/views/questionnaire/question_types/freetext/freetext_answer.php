<?php
/**
 * @var QuestionnaireQuestion $vote
 */
    $answer = $vote->getMyAnswer();
    $answerdata = $answer['answerdata'] ? $answer['answerdata']->getArrayCopy() : [];
?>

<label>
    <?= $this->render_partial('questionnaire/_answer_description_container', ['vote' => $vote, 'iconshape' => 'guestbook']) ?>
    <textarea name="answers[<?= $vote->getId() ?>][answerdata][text]"
              <?= isset($vote->questiondata['mandatory']) && $vote->questiondata['mandatory'] ? "required" : "" ?>
              ><?= htmlReady($answerdata['text'] ?? '') ?></textarea>
</label>
