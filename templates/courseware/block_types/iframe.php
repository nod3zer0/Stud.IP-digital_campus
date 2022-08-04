<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Externer Inhalt') . ': ' ?></p>
    <p>
        <?= _('Titel') . ' => ' . htmlReady($payload['title']) ?>, 
        <?= _('URL') . '=>' ?> <a href="<?= htmlReady($payload['url']) ?>"><?= htmlReady($payload['url']) ?></a>
    </p>
</div>
