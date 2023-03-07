<?php
/**
 * @var Course_WizardController $controller
 * @var string $content
 * @var string $temp_id
 * @var int $stepnumber
 * @var bool $first_step
 * @var bool|null $dialog
 */
?>
<? if ($content) : ?>
    <form class="default course-wizard-step-<?= $stepnumber ?>" action="<?= $controller->link_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post" data-secure>
        <fieldset>
        <?= $content ?>
        </fieldset>

        <footer data-dialog-button>
            <input type="hidden" name="step" value="<?= $stepnumber ?>">
        <? if (empty($first_step)): ?>
            <?= Studip\Button::create(
                _('Zurück'),
                'back',
                !empty($dialog) ? ['data-dialog' => 'size=50%'] : []
            ) ?>
        <? endif; ?>
            <?= Studip\Button::create(
                _('Weiter'),
                'next',
                !empty($dialog) ? ['data-dialog' => 'size=50%'] : []
            ) ?>
        </footer>
    </form>
<? else : ?>
    <?= Studip\LinkButton::createCancel(
        _('Zurück zu meiner Veranstaltungsübersicht'),
        $controller->url_for($GLOBALS['perm']->have_perm('admin') ? 'admin/courses' : 'my_courses'),
        ['data-dialog-button' => '']
    ) ?>
<? endif ?>
