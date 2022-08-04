<div style="font-size: 12px;">
    <p style="font-weight: bold;"><?= _('Diagramm') . ': ' ?></p>
    <p><?= htmlReady($payload['label']) ?></p>
    <? foreach ($payload['content'] as $val): ?>
        <p><?= htmlReady($val['label']) . ' => ' . htmlReady($val['value']); ?></p>
    <? endforeach; ?>
</div>