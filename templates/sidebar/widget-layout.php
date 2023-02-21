<?php
/**
 * @var string $title
 * @var string $base_class
 * @var array $additional_attributes
 * @var string|null $extra
 * @var string $content
 */

$css_classes = $layout_css_classes ?? [];
$css_classes[] = "{$base_class}-widget";

if ($title && isset($extra)) {
    $css_classes[] = 'sidebar-widget-has-extra';
}

$additional_attributes['class'] = implode(' ', $css_classes);
?>
<div <?= arrayToHtmlAttributes($additional_attributes) ?>>
<? if ($title): ?>
    <div class="<?= $base_class ?>-widget-header">
        <?= htmlReady($title) ?>
    </div>
<? endif; ?>
    <div class="<?= $base_class ?>-widget-content">
        <?= $content_for_layout ?>
    </div>
<? if ($title && isset($extra)): ?>
    <div class="<?= $base_class ?>-widget-extra"><?= $extra ?></div>
<? endif; ?>
</div>
