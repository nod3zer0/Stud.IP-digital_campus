<?php
/**
 * @var QuestionnaireQuestion $vote
 * @var string $iconshape
 */
?>
<div class="description_container">
    <div class="icon_container">
        <?= Icon::create($iconshape, Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <article class="description">
        <? if (isset($vote->questiondata['mandatory']) && $vote->questiondata['mandatory']) : ?>
            <?= Icon::create('star', Icon::ROLE_ATTENTION)->asImg(20, ['class' => 'text-bottom', 'alt' => '']) ?>
            <?= _('Pflichtantwort') ?>
        <? endif ?>
        <?= formatReady($vote->questiondata['description']) ?>
    </article>
</div>
