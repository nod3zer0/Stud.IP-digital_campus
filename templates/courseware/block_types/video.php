<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Video') . ': ' ?></p>
    <p>
        <?= htmlReady($payload['title']); ?>,
        <? if ($files[0]): ?>
            <a href="<?= htmlReady($files[0]->getDownloadURL()) ?>"><?= htmlReady($files[0]->name); ?></a>
        <? endif; ?>
    </p>
</div>