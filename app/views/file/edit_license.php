<form action="<?= $controller->link_for('file/edit_license', $origin_folder_id ?? null) ?>"
      method="post" class="default" data-dialog="reload-on-close">
<input type="hidden" name="re_location" value="<?= htmlReady($re_location) ?>">
<? foreach ($file_refs as $file_ref) : ?>
    <input type="hidden" name="file_refs[]" value="<?= htmlReady($file_ref->id) ?>">
<? endforeach ?>


    <? if ($show_description_field): ?>
    <fieldset>
        <legend><?= _('Zusatzangaben') ?></legend>
        <label>
            <b><?= _('Beschreibung') ?></b>
            <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"></textarea>
        </label>
    </fieldset>

    <? endif ?>

    <? if (count($file_refs) === 1) : ?>
        <fieldset>
            <legend><?= _('Barrierefreiheit') ?></legend>
            <label>
                <input type="checkbox" name="is_accessible" value="1">
                <?= _('Diese Datei ist barrierefrei.') ?>
            </label>
            <?= formatReady((string)Config::get()->ACCESSIBILITY_INFO_TEXT ?: '') ?>
        </fieldset>
    <? endif ?>

        <?= $this->render_partial('file/_terms_of_use_select.php', [
            'content_terms_of_use_entries' => $licenses,
            'selected_terms_of_use_id'     => $file_ref->content_terms_of_use_id
        ]) ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for((in_array($folder->range_type, ['course', 'institute']) ? $folder->range_type . '/' : '') . 'files/index/' . $folder->id)
        ) ?>
    </footer>
</form>
