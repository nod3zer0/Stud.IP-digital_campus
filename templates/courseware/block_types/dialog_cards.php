<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Lernkarten') . ': ' ?></p>
    <? foreach ($payload['cards'] as $card): ?>
        <p style="font-weight: bold;"><?= _('Karte') . ' ' . htmlReady($card['index']) .': ' ?></p>
        <p>
            <? if ($card['front_file_ref']): ?>
            <img src="<?= htmlReady($card['front_file_ref']->getDownloadURL()); ?>">
            <? endif; ?>
            <?= htmlReady($card['front_text']); ?>
        </p>
        <p>
            <? if ($card['front_file_ref']): ?>
                <img src="<?= htmlReady($card['back_file_ref']->getDownloadURL()); ?>">
            <? endif; ?>
            <?= htmlReady($card['back_text']); ?>
        </p>
    <? endforeach; ?>
</div>