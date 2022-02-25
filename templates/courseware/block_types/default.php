<h5><?= sprintf(_('Block-Typ: %s'), htmlReady($title)) ?></h5>
<h6><?= _('Block-Daten') ?>:</h6>
<? foreach ($payload as $key => $value): ?>
    <? $value = is_bool($value) ? ($value ? 'true' : 'false') : $value; ?>
    <? if (!empty($value)): ?>
        <h6><?= htmlReady(str_replace('_', ' ', strtocamelcase($key, true)) . ' => ' . $value) ?></h6>
    <? endif; ?>
<? endforeach; ?>
