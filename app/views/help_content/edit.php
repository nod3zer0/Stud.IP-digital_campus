<? use Studip\Button, Studip\LinkButton; ?>

<form id="edit_help_content_form" class="default"
      action="<?= $controller->url_for('help_content/store' . ($help_content_id ? '/' . $help_content_id : ''), $parameters) ?>"
      method="POST">
    <?= CSRFProtection::tokenTag(); ?>
    <fieldset>
        <? if ($help_content_route) : ?>
            <legend><?= sprintf(_('Seite %s'), $help_content_route) ?></legend>
            <input type="hidden" name="help_content_route" value="<?= $help_content_route ?>">
        <? else : ?>
            <legend><?= _('Neuer Hilfe-Text') ?></legend>
            <label for="help_content_route">
                <?= _('Seite') ?>:
                <input type="text" size="60" maxlength="255" name="help_content_route"
                       value=""
                       placeholder="<?= _('Bitte geben Sie eine Route für den Hilfe-Text an') ?>">
            </label>
        <? endif ?>

        <label for="help_content_language">
            <span class="required"><?= _('Sprache des Textes') ?>:</span>
            <select name="help_content_language">
                <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language) : ?>
                    <option value="<?= mb_substr($key, 0, 2) ?>"<?= (isset($help_content) && $help_content->language === mb_substr($key, 0, 2)) ? ' selected' : '' ?>>
                        <?= $language['name'] ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <label for="help_content_content">
            <?= _('Hilfe-Text') ?>:
            <textarea cols="60" rows="5" name="help_content_content"
                      placeholder="<?= _('Bitte geben Sie den Text ein') ?>"><?= htmlReady($help_content->content ?? '') ?></textarea>
        </label>
        <label for="help_content_comment">
            <?= _('Bemerkung') ?>:
            <textarea name="help_content_comment"><?= htmlReady($help_content->comment ?? '') ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button="1">
        <?= Button::createAccept(_('Speichern'), 'save_help_content', ['data-dialog' => '']) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('help_content/admin_overview'), []) ?>
    </footer>
</form>
