<?php
# Lifter010: TODO

/**
 * @var Admin_SpecificationController $controller
 * @var AuxLockRule $rule
 * @var array $semFields
 * @var DataField[] $entries_semdata
 * @var DataField[] $entries_user
 */
use Studip\Button, Studip\LinkButton;
?>
<form action="<?= $controller->store($rule) ?>" method="post" class="default" data-secure>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
        <? if ($rule->isNew()) : ?>
            <?= _('Eine neue Regel definieren') ?>
        <? else : ?>
            <?= sprintf(_('Regel "%s" editieren'), htmlReady($rule['name'])) ?>
        <? endif ?>
        </legend>
        <label>
            <span class="required">
                <?= _('Name der Regel') ?>
            </span>
            <?= I18N::input('name', $rule->name, [
                'required' => '',
            ]) ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textarea('description', $rule->description, [
                'class' => 'wysiwyg',
            ]) ?>
        </label>
    </fieldset>

<? if (count($entries_semdata) > 0) : ?>
    <fieldset>
        <legend>
            <?= _('Zusatzinformationen') ?>
        </legend>
    <? foreach ($entries_semdata as $id => $entry) : ?>
        <?= $this->render_partial('admin/specification/_field', [
            'rule'        => $rule,
            'id'          => $entry->datafield_id,
            'name'        => $entry->name,
            'required'    => true,
            'institution' => $entry->institution,
        ]) ?>
    <? endforeach ?>
    </fieldset>
<? endif ?>

    <fieldset>
        <legend>
            <?= _('Veranstaltungsinformationen') ?>
        </legend>
    <? foreach ($semFields as $id => $name) : ?>
        <?= $this->render_partial('admin/specification/_field', compact('rule', 'id', 'name')) ?>
    <? endforeach ?>
    </fieldset>

<? if (count($entries_user) > 0) : ?>
    <fieldset>
        <legend>
            <?= _('Personenbezogene Informationen') ?>
        </legend>
    <? foreach ($entries_user as $id => $entry) : ?>
        <?= $this->render_partial('admin/specification/_field', [
            'rule' => $rule,
            'id'   => $entry->datafield_id,
            'name' => $entry->name,
        ]) ?>
    <? endforeach ?>
    </fieldset>
<? endif ?>

    <footer>
    <? if ($rule->isNew()) : ?>
        <?= Button::createAccept(_('Erstellen'), 'erstellen', ['title' => _('Neue Regel erstellen')]) ?>
    <? else : ?>
        <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen übernehmen')]) ?>
    <? endif ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->indexURL(), ['title' => _('Zurück zur Übersicht')]) ?>
    </footer>
</form>
