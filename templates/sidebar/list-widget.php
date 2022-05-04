<ul class="<?= implode(' ', $css_classes) ?>">
<? foreach ($elements as $index => $element): ?>
    <? if ($element instanceof LinkElement): ?>
        <? $icon = $element->icon ?? null ?>
        <? if ($icon && $element->isDisabled()): ?>
            <? $icon = $icon->copyWithRole('inactive') ?>
        <? endif ?>
    <? endif ?>
    <li id="<?= htmlReady($index) ?>"
        <?= isset($icon) ? 'style="' . $icon->asCSS() .'"' : '' ?>
        <?= !empty($element->active) ? 'class="active"' : '' ?>>
        <?= $element->render() ?>
    </li>
<? endforeach; ?>
</ul>
