<h5 style="font-size: 14px;"><?= sprintf(_('Block-Typ: %s'), htmlReady($title)) ?></h5>
<h6 style="font-size: 12px;"><?= _('Block-Daten') ?>:</h6>
<? foreach ($payload as $key => $value): ?>
    <? $value = is_bool($value) ? ($value ? 'true' : 'false') : $value; ?>
    <? if (!empty($value)): ?>
        <h6><?= htmlReady(str_replace('_', ' ', strtocamelcase($key, true)) . ' => ' . $value) ?></h6>
    <? endif; ?>
<? endforeach; ?>
<? if($files): ?>
    <h6 style="font-size: 12px;"><?= _('Block-Dateien') ?>:</h6>
    <? foreach ($files as $file): ?>
        <? if ($file === null) { continue; } ?>
        <p>
            <a href="<?= htmlReady($file->getDownloadURL()); ?>"><?= htmlReady($file->name); ?></a>
        </p>
    <? endforeach; ?>
<? endif; ?>