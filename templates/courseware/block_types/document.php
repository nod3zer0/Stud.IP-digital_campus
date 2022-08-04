<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Dokument') . ': ' ?></p>
    <p>
        <?= _('Titel') . ' => ' . htmlReady($payload['title']) ?>,
        <? if ($files[0]): ?>
            <?= _('Datei') . '=>' ?> <a href="<?= htmlReady($files[0]->getDownloadURL()) ?>"><?= htmlReady($files[0]->name) ?></a>
        <? endif; ?>
    </p>
</div>