<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Verweissensitive Grafik') . ': ' ?></p>
    <p style="font-style: italic; font-size: 10px;"><?= _('Hinweis: Positionen der Verweise können nicht dargestellt werden.')?></p>
    <p>
        <? if ($files[0]): ?>
            <img src="<?= htmlReady($files[0]->getDownloadURL()); ?>">
            <span style="font-style:italic"><?= htmlReady($files[0]->name); ?></span>
        <? endif; ?>
    </p>
    <p><?= _('Verweise'). ': ' ?></p>
    <ul>
        <? foreach($payload['shapes'] as $shape): ?>
            <li>
                <?= htmlReady($shape['title']); ?>,<?= htmlReady($shape['data']['text']); ?><? if ($shape['link_type'] === 'external'): ?>,
                    <a href="<?= htmlReady($shape['target_external']); ?>"><?= htmlReady($shape['target_external']); ?></a>
                <? endif; ?>
            </li>
        <? endforeach; ?>
    </ul>
</div>