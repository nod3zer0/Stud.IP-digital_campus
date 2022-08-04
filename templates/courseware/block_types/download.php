<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Download') . ': ' ?></p>
    <? if ($files[0]): ?>
        <p>
            <a href="<?= htmlReady($files[0]->getDownloadURL()); ?>"><?= htmlReady($files[0]->name); ?></a>
        </p>
    <? else: ?>
        <p>
        <span style="font-style:italic"><?= _('keine Datei ausgewählt'); ?></span>
        </p>
    <? endif; ?>
</div>