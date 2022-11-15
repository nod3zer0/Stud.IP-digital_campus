<? if ($block->lock_time): ?>
    <?= tooltipIcon(sprintf(
        _('Dieser Block wird %u Stunden vor Beginn für Buchungen gesperrt.'),
        $block->lock_time
    )) ?>
<? endif; ?>
