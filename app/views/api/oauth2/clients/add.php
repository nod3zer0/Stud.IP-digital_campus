<form class="default" action="<?= $controller->url_for('api/oauth2/clients/store') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Basisdaten des OAuth2-Clients') ?>
        </legend>
        <label>
            <span class="required">
                <?= _('Name') ?>
            </span>
            <input required type="text" name="name">
        </label>

        <label>
            <span class="required">
                <?= _('Redirect-URIs') ?>
            </span>
            <textarea required name="redirect" placeholder="<?= _('schema://<redirect-uri-1>\nschema://<redirect-uri-2>') ?>" maxlength="1000"></textarea>
        </label>

        <label>
            <span>
                <?= _('Beschreibung') ?>
            </span>
            <textarea name="description" maxlength="1000"></textarea>
        </label>
    </fieldset>

    <fieldset class="oauth2-clients--confidentiality">
        <legend class="required">
            <?= _('Kann der OAuth2-Client kryptographische Geheimnisse bewahren?') ?>
        </legend>

        <div>
            <input type="radio" name="confidentiality" value="public" id="oauth2-clients-confidentiality--public" required>
            <label for="oauth2-clients-confidentiality--public">
                <?= _('Nein. Es handelt sich zum Beispiel um eine <span lang="en">Mobile App</span> oder <span lang="en">Single Page App</span>.') ?>
            </label>
        </div>

        <div>
            <input type="radio" name="confidentiality" value="confidential" id="oauth2-clients-confidentiality--confidential">
            <label for="oauth2-clients-confidentiality--confidential">
                <?= _('Ja, dieser OAuth2-Client kann ein kryptographisches Geheimnis bewahren.') ?>
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Meta-Informationen') ?>
        </legend>

        <label>
            <span class="required">
                <?= _('Von wem wird der OAuth2-Client entwickelt?') ?>
            </span>
            <input required type="text" name="owner" maxlength="100">
        </label>

        <label>
            <span class="required">
                <?= _('Homepage der Entwickelnden des OAuth2-Clients') ?>
            </span>
            <input required type="url" name="homepage" maxlength="200">
        </label>

        <label>
            <span>
                <?= _('Notizen (nur für Root-Accounts sichtbar)') ?>
            </span>
            <textarea name="admin_notes"></textarea>
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen'), 'create_client', [
            'title' => _('Neuen OAuth2-Client erstellen'),
        ]) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/oauth2'), [
            'title' => _('Zurück zur Übersicht'),
        ]) ?>
    </footer>
</form>
