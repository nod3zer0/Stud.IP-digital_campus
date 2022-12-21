<?php
/**
 * @var bool $html
 * @var bool $important
 * @var string $text
 */
?>
<span class="tooltip tooltip-icon <? if ($important) echo 'tooltip-important'; ?>" data-tooltip <? if (!$html) printf('title="%s"', htmlReady($text)) ?> tabindex="0">
<? if ($html): ?>
    <span class="tooltip-content"><?= $text ?></span>
<? endif; ?>
</span>
