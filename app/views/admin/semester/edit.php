<?php
/**
 * @var Admin_SemesterController $controller
 * @var Semester $semester
 */
?>
<form method="post" action="<?= $controller->url_for('admin/semester/edit/' . $semester->id) ?>" data-dialog="size=auto" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= PageLayout::getTitle() ?>
        </legend>

        <label>
            <?= _('Name des Semesters') ?>

            <?= I18N::input('name', $semester->name, [
                'id'    => 'name',
                'class' => isset($errors['name']) ? 'invalid' : '',
            ]) ?>
        </label>

        <label>
            <?= _('Kürzel') ?>

            <?= I18N::input('token', $semester->semester_token, [
                'id' => 'token',
            ]) ?>
        </label>

        <label>
            <?= _('Beschreibung') ?>

            <?= I18N::textarea('description', $semester->description, [
                'id' => 'description',
            ]) ?>
        </label>

        <label>
            <?= _('Externe ID') ?>

            <input type="text" name="external_id" value="<?= htmlReady($semester->external_id) ?>" maxlength="50">
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Semesterzeitraum') ?>
        </legend>

        <label class="col-3">
            <span class="required"><?= _('Beginn') ?></span>

            <? if ($semester->absolute_seminars_count > 0): ?>
                <?= tooltipIcon(_('Das Startdatum kann nur bei Semestern geändert werden, in denen keine Veranstaltungen liegen!'), true) ?>
            <? endif; ?>

            <? if ($semester->absolute_seminars_count > 0): ?>
                <input type="text" name="beginn" value="<?= date('d.m.Y', $semester->beginn) ?>" readonly>
            <? else: ?>
                <input required type="text" id="beginn" name="beginn"
                       <? if (isset($errors['beginn'])) echo 'class="invalid"'; ?>
                       data-date-picker
                       value="<? if ($semester->beginn) echo date('d.m.Y', $semester->beginn) ?>">
            <? endif; ?>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Ende') ?></span>

            <input required type="text" id="ende" name="ende"
                   <? if (isset($errors['ende'])) echo 'class="invalid"'; ?>
                   data-date-picker='{">":"#beginn"}'
                   value="<? if ($semester->ende) echo date('d.m.Y', $semester->ende); ?>">
       </label>
   </fieldset>

   <fieldset>
       <legend>
            <?= _('Vorlesungszeitraum') ?>
        </legend>

        <label class="col-3">
            <span class="required"><?= _('Beginn') ?></span>

            <input required type="text" id="vorles_beginn" name="vorles_beginn"
                   <? if (isset($errors['vorles_beginn'])) echo 'class="invalid"'; ?>
                   data-date-picker='{"<":"#vorles_ende",">=":"#beginn"}'
                   value="<? if ($semester->vorles_beginn) echo date('d.m.Y', $semester->vorles_beginn); ?>">
        </label>

        <label class="col-3">
            <span class="required"><?= _('Ende') ?></span>

            <input required type="text" id="vorles_ende" name="vorles_ende"
                    <? if (isset($errors['vorles_ende'])) echo 'class="invalid"'; ?>
                   data-date-picker='{">":"#vorles_beginn","<=":"#ende"}'
                   value="<? if ($semester->vorles_ende) echo date('d.m.Y', $semester->vorles_ende); ?>">
        </label>
   </fieldset>
    <fieldset>
        <legend>
         <?= _('Tatsächlicher Semesterwechsel') ?>
        </legend>

        <label class="col-3">
            <span><?= _('Beginn') ?></span>
            <?= tooltipIcon(_('Optional. Wird kein Datum angegeben, wird das Wochen-Offset in SEMESTER_TIME_SWITCH berücksichtigt.')) ?>
            <input type="text" id="semesterwechsel" name="semesterwechsel"
                <? if (isset($errors['semesterwechsel'])) echo 'class="invalid"'; ?>
                   data-date-picker='{"<=":"#beginn"}'
                   value="<? if ($semester->sem_wechsel) echo date('d.m.Y', $semester->sem_wechsel) ?>">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('admin/semester'))?>
    </footer>
</table>
</form>
