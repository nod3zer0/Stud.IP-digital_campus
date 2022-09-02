<form class="default" action="<?= $controller->action_link('store_ansprechpartner', $contact_range->contact_range_id, $origin) ?>" method="post" data-dialog="size=auto">
    <fieldset>
        <legend>
            <?= _('Personendaten') ?>
        </legend>
        <? if ($contact_range->contact->contact_status === 'extern') : ?>
            <? $perm_extern = MvvPerm::get('MvvExternContact') ?>
            <label>
                <?= _('Name') ?>
                <?= MvvI18N::input('contact_name', $extern_contact->name, ['maxlength' => '255'])->checkPermission($extern_contact) ?>
            </label>
            <label>
                <?= _('Vorname (optional)') ?>
                <input name="contact_vorname" type="text" value="<?= htmlReady($extern_contact->vorname) ?>"<?= $perm_extern->disable('vorname'); ?>>
            </label>
            <label>
                <?= _('Homepage') ?>
                <?= MvvI18N::input('contact_homepage', $extern_contact->homepage, ['maxlength' => '255'])->checkPermission($extern_contact) ?>
            </label>
            <label>
                <?= _('Email') ?>
                <input name="contact_mail" type="text" value="<?= htmlReady($extern_contact->mail) ?>"<?= $perm_extern->disable('mail'); ?>>
            </label>
            <label>
                <?= _('Telefon') ?>
                <input name="contact_tel" type="text" value="<?= htmlReady($extern_contact->tel) ?>"<?= $perm_extern->disable('tel'); ?>>
            </label>
        <? endif ?>
        <label>
            <?= _('Alternative Kontaktmail (optional)') ?>
            <input name="contact_altmail" type="text" value="<?= htmlReady($contact_range->contact->alt_mail) ?>"<?= MvvPerm::get('MvvContact')->disable('alt_mail'); ?>>
        </label>
    </fieldset>
    <?= $this->render_partial('shared/contacts/contact_range_fields') ?>
    <?= CSRFProtection::tokenTag() ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_contacta') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>
</form>
