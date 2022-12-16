<?php
/**
 * @var QuestionnaireQuestion $vote
 */
?>

<div class="description_container">
    <div class="icon_container">
        <?= Icon::create('info-circle', Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <div class="description">
        <? if (isset($vote->questiondata['url']) && trim($vote->questiondata['url'])) : ?>
            <iframe <?= is_internal_url($vote->questiondata['url']) ? 'sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-presentation allow-scripts"' : '' ?>
                    src="<?= htmlReady($vote->questiondata['url']) ?>"></iframe>
        <? endif ?>
        <? if (isset($vote->questiondata['description']) && trim($vote->questiondata['description'])) : ?>
        <article>
            <?= formatReady($vote->questiondata['description']) ?>
        </article>
        <? endif ?>
    </div>
</div>
