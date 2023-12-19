<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>
<? if (isset($error_msg)): ?>
    <?= MessageBox::error($error_msg) ?>
<? endif ?>

<form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <? if(isset($edit_rubric)): ?>
                <?= _('Neue Rubrik anlegen') ?>
            <? else : ?>
                <?= _('Neue Seite anlegen') ?>
            <? endif ?>
        </legend>

        <? if(isset($edit_rubric)): ?>
            <label>
                <?= _('Titel der Rubrik') ?>
                <input type="text" name="rubric_name" id="rubric_name">
            </label>
            <label>
                <?= _('Position der Rubrik') ?>
                <input type="number" name="rubric_position" id="rubric_position">
            </label>
        <? else: ?>
            <label>
                <?= _('Rubrik-Zuordnung') ?>
                <select name="rubric_id">
                    <? foreach ($rubrics as $option) : ?>
                    <option value="<?= $option['rubric_id'] ?>"<? if($currentrubric==$option['rubric_id']){echo " selected";} ?>><?= htmlReady(language_filter($option['name'])) ?></option>
                    <? endforeach ?>
                </select>
            </label>

            <label>
                <?= _('Seitentitel') ?>
                <input style="width: 90%;" type="text" name="detail_name" id="detail_name">
            </label>

            <label>
                <input type="checkbox" name="draft_status" id="draft_status" checked>
                <?= _('Entwurfsmodus (nur sichtbar für root)') ?>
            </label>

            <label>
                <input type="checkbox" name="page_disabled_nobody" id="page_disabled_nobody">
                <?= _('Seite nicht anzeigen für nicht angemeldete Benutzer') ?>
            </label>

            <label>
                <?= _('Position der Seite') ?>
                <input type="number" name="page_position" id="page_position">
            </label>

            <label>
                <?= _('Seiteninhalt') ?>
                <textarea style="width: 90%;height: 15em;" class="add_toolbar size-l wysiwyg" name="content" id="content"></textarea><br>
            </label>
        <? endif ?>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Abschicken')) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('siteinfo/show/'.$currentrubric)) ?>
    </footer>
</form>

<? if(!isset($edit_rubric)): ?>
    <?= $this->render_partial('siteinfo/help') ?>
<? endif ?>
