<?= strftime('%A, %x', $block->start) ?>

<?= sprintf(
    _('%s bis %s Uhr'),
    date('H:i', $block->start),
    date('H:i', $block->end)
) ?>

(<?= formatLinks($block->room) ?>)

<? if ($block->show_participants): ?>
    - <?= _('öffentlich sichtbar') ?>
    <?= tooltipIcon(_('Die Namen der buchenden Person sind sichtbar')) ?>
<? endif; ?>

<? if (count($block->responsibilities) > 0): ?>
<br>
<ul class="narrow list-csv">
<? foreach ($block->responsibilities as $responsibility): ?>
    <li>
        <a href="<?= URLHelper::getLink($responsibility->getURL(), [], true) ?>">
            <?= htmlReady($responsibility->getName()) ?>
        </a>
    </li>
<? endforeach; ?>
</ul>
<? endif; ?>

<? if ($block->note): ?>
<br>
<small>
    <?= formatLinks($block->note); ?>
</small>
<? endif; ?>
