<?php
/**
 * @var string $element_id
 * @var array $entry
 */
?>
<div id="schedule_icons_<?= $element_id ?>" class="schedule_icons">
    <? if (!empty($entry['icons'])) foreach ($entry['icons'] as $icon) : ?>
        <? if (!empty($icon['url'])) : ?>
        <a href="<?= $icon['url'] ?>" <?= $icon['onClick'] ? 'onClick="STUDIP.Calendar.clickEngine('. $icon['onClick'].', this, event); return false;"' : '' ?>>
            <?= Assets::img($icon['image'], ['title' => $icon['title'], 'alt' => $icon['title']]) ?>
        </a>
        <? else : ?>
        <?= Assets::img($icon['image'], ['title' => $icon['title']]) ?>
        <? endif; ?>
    <? endforeach ?>
</div>
