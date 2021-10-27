<fieldset>
    <legend>
        <?= _('Zuordnungsdaten') ?>
    </legend>
    <? if ($contact_range->range_type !== 'Modul') : ?>
        <label>
            <?= _('Ansprechpartnertyp') ?>
            <select style="display: inline-block; max-width: 40em;" name="contact_type"<?= MvvPerm::get('MvvContactRange')->disable('contact_type') ?>>
                <option value=""<?= empty($contact_range->type) ? ' selected' : '' ?>><?= _('keine Auswahl') ?></option>
            <? foreach ($GLOBALS['MVV_CONTACTS']['TYPE']['values'] as $key => $entry) : ?>
                <option value="<?= $key ?>"<?= $key == $contact_range->type ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
            <? endforeach ?>
            </select>
        </label>
    <? endif ?>
    <label>
        <?= _('Kategorie') ?>
        <select style="display: inline-block; max-width: 40em;" name="contact_category"<?= MvvPerm::get('MvvContactRange')->disable('contact_category') ?>>
        <? foreach (MvvContactRange::getCategoriesByRangetype($contact_range->range_type) as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $contact_range->category ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach ?>
        </select>
    </label>
</fieldset>