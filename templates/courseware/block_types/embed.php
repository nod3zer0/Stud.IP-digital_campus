<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Externer Inhalt') . ': ' ?></p>
    <p>
        <?= htmlReady(_('Titel') . ' => ' . $payload['title']) ?>, 
        <?= htmlReady(_('Quelle') . ' => ' . $payload['source']) ?>,
        <?= _('URL') . '=>' ?> <a href="<?= htmlReady($payload['url']) ?>"><?= htmlReady($payload['url']) ?></a>
    </p>
</div>
