<?php
/**
 * @var array $my_inst
 * @var string $current_institut_id
 * @var string $sem_name_prefix
 * @var string $current_semester_id
 */
?>
<form action="?" method="post" name="institute_choose" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Anmeldesets auflisten') ?></legend>

        <?= $this->render_partial('admission/institute-select.php', [
            'institutes' => $my_inst,
            'current_institut_id' => $current_institut_id,
        ]) ?>

        <label>
            <?=_("Präfix des Veranstaltungsnamens / Nummer:")?>
            <input type="text" name="sem_name_prefix" value="<?=htmlReady($sem_name_prefix)?>" size="40">
        </label>

        <label>
            <?=_("Veranstaltungen aus diesem Semester:")?>
            <?= Semester::getSemesterSelector(['name'=>'select_semester_id'], $current_semester_id, 'semester_id', false)?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Auswählen'), 'choose_institut', ['title' => _("Einrichtung auswählen")]) ?>
    </footer>
</form>
