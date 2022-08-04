<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Dateiordner') . ': ' ?></p>
    <? foreach ($files as $file): ?>
        <? if ($file === null) { continue; } ?>
        <p>
            <a href="<?= htmlReady($file->getDownloadURL()); ?>"><?= htmlReady($file->name); ?></a>
        </p>
    <? endforeach; ?>
</div>