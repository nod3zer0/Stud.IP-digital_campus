<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <ul class="<?= implode(' ', $css_classes) ?>" aria-label="<?= htmlReady($title) ?>">
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
</form>
