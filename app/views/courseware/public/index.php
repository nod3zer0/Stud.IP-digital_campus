<? if (!$expired && !$invalid): ?>
<div
    id="courseware-public-app"
    link-id="<?= htmlReady($link_id) ?>"
    link-pass="<?= htmlReady($link_pass) ?>"
    entry-type="public"
    entry-element-id="<?= htmlReady($entry_element_id) ?>"
>
</div>
<? endif; ?>
<? if ($expired): ?>
    <?= MessageBox::warning(_('Der Link zu dieser Seite ist abgelaufen.'))->hideClose() ?>
<? endif; ?>
<? if ($invalid): ?>
    <?= MessageBox::error(_('Es wurde kein gültiger Link aufgerufen.'))->hideClose() ?>
<? endif; ?>
