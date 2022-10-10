<?php
/**
 * @var StudipStudyArea $node
 * @var string $open
 * @var int $layer
 * @var array $dont_open
 * @var string $compulsory
 */
?>
<li>
<? if ($node->id !== 'root' && $node->required_children): ?>
    <input id='<?= htmlReady($node->id) ?>' type='checkbox' <?= $open && !in_array($layer, $dont_open) ? 'checked' : ''?>>
<? endif; ?>
    <label for='<?= htmlReady($node->id) ?>'></label>

<? if ($node->id !== 'root'): ?>
    <a href="<?= URLHelper::getLink('show_bereich.php?level=sbb&id=' . $node->id) ?>">
        <?= htmlReady($node->name) ?>
    </a>
<? else: ?>
    <?= htmlReady($node->name) ?>
<? endif; ?>

<? if ($node->required_children): ?>
    <ul>
    <? foreach ($node->required_children as $child): ?>
        <?= $this->render_partial('study_area/tree.php', ['node' => $child, 'open' => $open, 'layer' => ((int)$layer + 1)]) ?>
    <? endforeach; ?>
    </ul>
<? endif; ?>
</li>
