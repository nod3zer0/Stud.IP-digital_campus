<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Leinwand') . ': ' ?></p>
    <? if ($files[0]): ?>
        <p>
            <img src="<?= htmlReady($files[0]->getDownloadURL()); ?>">
            <span style="font-style:italic"><?= htmlReady($files[0]->name); ?></span>
        </p>
    <? else: ?>
        <p>
        <span style="font-style:italic"><?= _('ohne Hintergrundbild'); ?></span>
        </p>
    <? endif; ?>
</div>