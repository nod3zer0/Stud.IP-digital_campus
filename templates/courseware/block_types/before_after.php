<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Bildvergleich') . ': ' ?></p>
    <? foreach ($files as $file): ?>
        <? if ($file === null) { continue; } ?>
        <p>
            <img src="<?= htmlReady($file->getDownloadURL()); ?>">
            <span style="font-style:italic"><?= htmlReady($file->name); ?></span>
        </p>
    <? endforeach; ?>
</div>
