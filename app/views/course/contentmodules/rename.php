<form class="default"
      action="<?= $controller->link_for('course/contentmodules/rename/' . $module->getPluginId()) ?>"
      method="post">
    <fieldset>

        <label>
            <?= _('Neuer Name des Werkzeugs') ?>
            <input type="text"
                   name="displayname"
                   value="<?= $tool && $tool['metadata'] ? htmlReady($tool['metadata']['displayname']) : ''?>"
                   placeholder="<?= htmlReady($metadata['displayname']) ?>">
        </label>

        <div>
            <?= htmlReady(sprintf(_('Ursprünglicher Werkzeugname ist "%s".'), $metadata['displayname'])) ?>
        </div>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern'))?>
        <? if ($tool && $tool['metadata'] && $tool['metadata']['displayname']) : ?>
            <?= \Studip\Button::create(_('Namen löschen'), 'delete') ?>
        <? endif ?>
    </div>
</form>
