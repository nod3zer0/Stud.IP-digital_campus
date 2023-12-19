<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 * @var ExternPagePersons $page
 */
?>

<span class="content-title">
    <?= _('Konfiguration für eine Liste der Mitarbeitenden (Personal)') ?>
</span>

<form method="post" action="<?= $controller->store('Persons', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>
    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label class="col-3">
            <?= _('Sortierung') ?>
            <select name="sort" id="data_sort">
                <? foreach ($page->getSortFields() as $sort_field => $field_name) : ?>
                    <option value="<?= htmlReady($sort_field) ?>"
                        <? if ($sort_field === $page->sort) echo 'selected'; ?>>
                        <?= htmlReady($field_name) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label>
            <input type="checkbox" name="grouping" value="1"
                <?= $page->grouping ? 'checked' : '' ?>>
            <?= _('Gruppierung nach Funktionen/Gruppen') ?>
        </label>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Ausgabe der Funktionen / Gruppen (Sichtbarkeit, alternative Gruppennamen)') ?>
        </legend>
        <? $full_group_names = $page->getFullGroupNames() ?>
        <? if (count($full_group_names)) : ?>
            <? foreach ($full_group_names as $group_id => $group_name) : ?>
                <label class="col-5">
                    <input type="checkbox" name="groupsvisible[<?= $group_id ?>]" value="1"
                           <?= $page->groupsvisible[$group_id] ? 'checked' : '' ?>>
                    <?= htmlReady($group_name) ?>
                </label>
                <label class="col-5" aria-label="<?= htmlReady($group_name) ?>">
                    <input type="text" name="groupsalias[<?= $group_id ?>]"
                           value="<?= htmlReady($page->groupsalias[$group_id]) ?>"
                           placeholder="<? printf(_('Alternativer Gruppenname (%s)'), htmlReady($group_name)) ?>">
                </label>
            <? endforeach ?>
        <? else : ?>
            <?= _('Keine Funktionen / Gruppen an dieser Einrichtung vorhanden.') ?>
        <? endif ?>
    </fieldset>

    <?= $this->render_partial('institute/extern/extern_config/_template') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createAccept(_('Speichern und zurück'), 'store_cancel') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL()
        ) ?>
    </footer>

</form>
