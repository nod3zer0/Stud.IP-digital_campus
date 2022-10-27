<?php
/**
 * @var int $count_all
 * @var int $count_used
 * @var int $sum
 * @var int $last_change
 */
?>
<dl class="smiley-statistics">
    <dt><?= _('Vorhanden') ?></dt>
    <dd><?= $count_all ?></dd>
    
    <dt><?= _('Davon benutzt') ?></dt>
    <dd><?= $count_used ?></dd>
    
    <dt><?= _('Smiley-Vorkommen') ?></dt>
    <dd><?= $sum ?></dd>

    <dt><?= _('Letzte Änderung') ?></dt>
    <dd><?= (!is_null($last_change) ? date('d.m.Y H:i:s', $last_change) : '')?></dd>
</dl>
