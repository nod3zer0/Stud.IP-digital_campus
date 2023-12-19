<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <? if(isset($edit_rubric)): ?>
                <?= _('Rubrik bearbeiten') ?>
            <? else : ?>
                <?= _('Seite bearbeiten') ?>
            <? endif ?>
        </legend>

        <? if(isset($edit_rubric)): ?>
            <input type="hidden" name="rubric_id" value="<?= htmlReady($rubric_id) ?>">
            <label>
                <?= _('Titel der Rubrik')?>
                <input type="text" name="rubric_name" id="rubric_name" value="<?= htmlReady($rubric_name) ?>">
            </label>
            <label>
                <?= _('Position der Rubrik') ?>
                <input type="number" name="rubric_position" id="rubric_position" value="<?= (int)$rubric_position ?>">
            </label>
        <? else: ?>
            <label>
                <?= _('Rubrik-Zuordnung')?>
                <select name="rubric_id">
                <? foreach ($rubrics as $option): ?>
                    <option value="<?= htmlReady($option['rubric_id']) ?>" <? if ($currentrubric == $option['rubric_id']) echo 'selected'; ?>>
                        <?= htmlReady(language_filter($option['name'])) ?>
                    </option>
                <? endforeach; ?>
                </select>
            </label>

            <label>
                <?= _('Seitentitel')?>
                <input type="text" name="detail_name" id="detail_name" value="<?= htmlReady($detail_name) ?>">
            </label>

            <label>
                <input type="checkbox" name="draft_status" id="draft_status" <?= $draft_status ? 'checked' : ''?>>
                <?= _('Entwurfsmodus (nur sichtbar für root)') ?>
            </label>

            <label>
                <input type="checkbox" name="page_disabled_nobody" id="page_disabled_nobody" <?= $page_disabled_nobody ? 'checked' : ''?>>
                <?= _('Seite nicht anzeigen für nicht angemeldete Benutzer') ?>
            </label>

            <label>
                <?= _('Position der Seite') ?>
                <input type="number" name="page_position" id="page_position" value="<?= (int)$page_position ?>">
            </label>

            <label>
                <?= _('Seiteninhalt')?>
                <textarea style="height: 15em;" name="content" id="content" class="size-l wysiwyg"><?= wysiwygReady($content) ?></textarea>
            </label>

            <input type="hidden" name="detail_id" value="<?= $currentdetail?>">
        <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Abschicken')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('siteinfo/show/'.$currentrubric.'/'.$currentdetail)) ?>
    </footer>
</form>

<? if(!isset($edit_rubric)): ?>
    <?= $this->render_partial('siteinfo/help.php') ?>
<? endif; ?>
