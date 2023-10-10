<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <ul class="<?= implode(' ', $css_classes) ?>" aria-label="<?= htmlReady($title) ?>">
    <? foreach ($elements as $index => $element): ?>
        <? $icon = $element->icon ?? null ?>
        <? if ($icon && $element instanceof LinkElement && $element->isDisabled()): ?>
            <? $icon = $icon->copyWithRole(Icon::ROLE_INACTIVE) ?>
        <? endif ?>
        <li id="<?= htmlReady($index) ?>"
            <?= isset($icon) ? 'style="' . $icon->asCSS() .'"' : '' ?>
            <?= !empty($element->active) ? 'class="active"' : '' ?>>
            <?= $element->render() ?>
        </li>
    <? endforeach; ?>
    </ul>
</form>
