<?php
/**
 * @var Admin_DatafieldsController $controller
 * @var string $object_typ
 * @var string $type_name
 * @var string $object_type
 * @var array $institutes
 * @var Institute $item
 * @var DataField $datafield
 */

use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/datafields/new/' . $object_typ) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= sprintf(_('Einen neuen Datentyp für die Kategorie "%s" erstellen'), $type_name) ?>
        </legend>

        <label>
            <span class="required"><?= _('Name') ?></span>

            <?= I18N::input('datafield_name', $datafield->name, [
                'required'  => '',
                'size'      => 60,
                'maxlength' => 254,
            ]) ?>
        </label>

        <label>
            <?= _('Feldtyp') ?>

            <select name="datafield_type">
           <? foreach (DataFieldEntry::getSupportedTypes($object_typ) as $param): ?>
                <option><?= htmlReady($param) ?></option>
            <? endforeach; ?>
            </select>
        </label>

        <label for="object_class">
        <? if ($object_typ === 'sem'): ?>
            <?= _('Veranstaltungskategorie') ?>
        <? elseif ($object_typ === 'inst'): ?>
            <?= _('Einrichtungstyp') ?>
        <? elseif ($object_typ === 'moduldeskriptor' || $object_type === 'modulteildeskriptor') : ?>
            <?= _('Sprache') ?>
        <? elseif ($object_typ === 'studycourse'): ?>
            <?= _('Typ/Abschnitt') ?>
        <? else: ?>
            <?= _('Nutzerstatus') ?>
        <? endif; ?>

        <? if ($object_typ === 'sem'): ?>
            <select name="object_class[]">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach (SemClass::getClasses() as $key => $val): ?>
                <option value="<?= $key ?>">
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
        <? elseif ($object_typ === 'inst'): ?>
            <select name="object_class[]">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach ($GLOBALS['INST_TYPE'] as $key => $val): ?>
                <option value="<?= $key ?>">
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
        <? elseif ($object_typ === 'moduldeskriptor') : ?>
            <select multiple name="object_class[]" required>
                <option value="NULL" selected><?= _('alle (mehrsprachige Eingabe bei Feldtyp textline, textarea, textmarkup)') ?></option>
            <? foreach ((array) $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'] as $key => $value) : ?>
                <option value="<?= htmlReady($key) ?>"><?= htmlReady($value['name']) ?></option>
            <? endforeach; ?>
        <? elseif ($object_typ === 'modulteildeskriptor') : ?>
            <select multiple name="object_class[]" required>
                <option value="NULL" selected><?= _('alle (mehrsprachige Eingabe bei Feldtyp textline, textarea, textmarkup)') ?></option>
            <? foreach ((array) $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'] as $key => $value) : ?>
                <option value="<?= htmlReady($key) ?>"><?= htmlReady($value['name']) ?></option>
            <? endforeach; ?>
        <? elseif ($object_typ === 'studycourse') : ?>
            <select name="object_class" required>
                <option value="all_settings" selected><?= _('alle (Abschnitt "Einstellungen")') ?></option>
                <option value="all_info"><?= _('alle (Abschnitt "Inhalte und Informationen")') ?></option>
                <option value="einfach_settings"><?= _('Einfach-Studiengänge (Abschnitt "Einstellungen")') ?></option>
                <option value="einfach_info"><?= _('Einfach-Studiengänge (Abschnitt "Inhalte und Informationen")') ?></option>
                <option value="mehrfach_settings"><?= _('Mehrfach-Studiengänge (Abschnitt "Einstellungen")') ?></option>
                <option value="mehrfach_info"><?= _('Mehrfach-Studiengänge (Abschnitt "Inhalte und Informationen")') ?></option>
        <? else: ?>
             <select multiple size="7" name="object_class[]" required>
                <option value="NULL" selected><?= _('alle') ?></option>
            <? foreach ($controller->user_status as $perm => $value): ?>
                <option value="<?= $value ?>"><?= $perm ?></option>
            <? endforeach; ?>
        <? endif; ?>
            </select>
        </label>

        <label>
            <?= _('Benötigter Status zum Bearbeiten') ?>

            <select name="edit_perms">
            <? foreach (array_keys($controller->user_status) as $perm): ?>
                <option><?= $perm ?></option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Sichtbarkeit') ?>

            <select name="visibility_perms">
            <? foreach (array_keys($controller->user_status) as $perm): ?>
                <option><?= $perm ?></option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Einrichtung') ?>
            <select name="institut_id" class="nested-select">
                <option value="" class="is-placeholder"></option>
                <? foreach ($institutes as $institute): ?>
                    <option value="<?= htmlReady($institute['Institut_id']) ?>"
                        class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>"
                            <?= isset($item) && $item->institut_id === $institute['Institut_id'] ? 'selected' : ''?>>
                        <?= htmlReady(my_substr($institute['Name'],0,80)) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

    <? if ($object_typ === 'user') :?>
        <label>
            <input type="hidden" name="system" value="0">
            <input type="checkbox" name="system" value="1">

           <?= _('Systemfeld') ?>
           <?= tooltipIcon(_('Nur für die Person selbst sichtbar, wenn der '
                           . 'benötigte Status zum Bearbeiten oder die '
                           . 'Sichtbarkeit ausreichend ist')) ?>
        </label>
    <? endif; ?>

        <label>
            <?= _('Position') ?>

            <input type="text" name="priority"
                   maxlength="10" size="2" class="size-s">
        </label>

    <? if ($object_typ === 'sem'): ?>
        <label>
            <input type="checkbox" name="is_required" value="true">

           <?= _('Pflichtfeld') ?>
        </label>

        <label>
            <?= _('Beschreibung') ?>

            <textarea name="description"></textarea>
        </label>

    <? endif; ?>
    <? if ($object_typ === 'user'): ?>
        <label>
            <input type="checkbox" name="is_userfilter" value="1">

           <?= _('Mögliche Bedingung für Anmelderegel') ?>
        </label>
    <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Anlegen'), 'anlegen', ['title' => _('Neues Datenfeld anlegen')]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields'), ['title' => _('Zurück zur Übersicht')]) ?>
    </footer>
</form>
