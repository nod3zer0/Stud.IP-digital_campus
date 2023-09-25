<?php
/**
 * @var QuestionnaireQuestion $vote
 * @var QuestionnaireAnswer[] $answers
 */
?>
<div class="description_container">
    <div class="icon_container">
        <?= Icon::create('guestbook', Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <div class="description">
        <?= formatReady($vote->questiondata['description']) ?>
    </div>
</div>

<ul class="clean">
<? foreach ($answers as $answer) : ?>
    <? if (trim($answer['answerdata']['text'])) : ?>
    <li style="border: #d0d7e3 thin solid; margin: 10px; padding: 10px;">
    <? if (!$vote->questionnaire['anonymous']) : ?>
        <div style="margin-bottom: 7px;">
            <? if ($answer['user_id'] && $answer['user_id'] !== 'nobody') : ?>
                <?= Avatar::getAvatar($answer['user_id'])->getImageTag(Avatar::SMALL) ?>
                <span style="color: #888888; font-weight: bold; font-size: 0.8em;"><?= get_fullname($answer['user_id']) ?></span>
            <? else : ?>
                <?= Avatar::getAvatar($answer['user_id'])->getImageTag(Avatar::SMALL) ?>
                <span style="color: #888888; font-weight: bold; font-size: 0.8em;"><?= get_fullname($answer['user_id']) ?></span>
            <? endif ?>
        </div>
    <? endif ?>
        <?= htmlReady($answer['answerdata']['text']) ?>
    </li>
    <? endif ?>
<? endforeach ?>
</ul>
