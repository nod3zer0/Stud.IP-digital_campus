<div class="<?= $base_class ?>-widget <?= is_array($layout_css_classes) ? htmlReady(implode(' ', $layout_css_classes)) : '' ?>"
    <?= !empty($id) ? sprintf('id="%s"', htmlReady($id)) : '' ?>>
<? if ($title): ?>
    <div class="<?= $base_class ?>-widget-header">
    <? if (isset($extra)): ?>
        <div class="<?= $base_class ?>-widget-extra"><?= $extra ?></div>
    <? endif; ?>
        <?= htmlReady($title) ?>
    </div>
<? endif; ?>
    <div class="<?= $base_class ?>-widget-content">
        <?= $content_for_layout ?>
    </div>
</div>
