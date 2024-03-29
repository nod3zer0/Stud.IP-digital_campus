<form action="<?= URLHelper::getLink($url) ?>"
      <?= $onsubmit ? 'onsubmit="'.htmlReady($onsubmit).'"' : '' ?>
      method="<?= $method ?>">
    <?= \SelectWidget::arrayToHiddenInput($params) ?>
    <?= (strtolower($method) == 'post') ?  CSRFProtection::tokenTag() : ''; ?>
    <select class="sidebar-selectlist <?= $class ?> <? if ($__is_nested): ?>nested-select<? endif; ?>"
            <? !empty($size) ? printf('size="%u"', $size) : '' ?>
            <?= !empty($attributes) ? arrayToHtmlAttributes($attributes) : '' ?>
            name="<?= sprintf('%s%s', htmlReady($name), $multiple ? '[]' : '') ?>"
            <? if ($multiple) echo 'multiple'; ?>
            aria-label="<?= htmlReady($title) ?>"
            <?= $dropdownAutoWidth ? 'data-dropdown-auto-width="1"' : '' ?>>

    <? foreach ($elements as $element): ?>
        <? if ($element instanceof SelectGroupElement && count($element->getElements()) > 0): ?>
            <optgroup label="<?= htmlReady($element->getLabel() ) ?>">
            <? foreach ($element->getElements() as $option): ?>
                <option value="<?= htmlReady($option->getId()) ?>" <? if ($option->isActive()) echo 'selected'; ?>
                    class="<? if ($element->getIndentLevel()): ?>nested-item nested-item-level-<?= $element->getIndentLevel() + 1 ?><? endif; ?>  <? if ($element->isHeader()): ?>nested-item-header<? endif; ?>"
                    title="<?= htmlReady($option->getTooltip() !== null ? $option->getTooltip() : $option->getLabel()) ?>">

                    <?= htmlReady($option->getLabel()) ?>
                </option>
            <? endforeach; ?>
            </optgroup>
        <? elseif (!($element instanceof SelectGroupElement)): ?>
            <option value="<?= htmlReady($element->getId()) ?>" <? if ($element->isActive()) echo 'selected'; ?>
                class="<? if ($element->getIndentLevel()): ?>nested-item nested-item-level-<?= $element->getIndentLevel() + 1 ?><? endif; ?> <? if ($element->isHeader()): ?>nested-item-header<? endif; ?>"
                title="<?= htmlReady($element->getTooltip() !== null ? $element->getTooltip() : $element->getLabel()) ?>">

                <?= htmlReady($element->getLabel()) ?>
            </option>
        <? endif; ?>
    <? endforeach; ?>
    </select>

    <? if(!$multiple) : ?>
        <noscript>
            <?= Studip\Button::create(_('Zuweisen')) ?>
        </noscript>
    <? elseif ($multiple) : ?>
        <?= Studip\Button::create(_('Zuweisen')) ?>
    <? endif; ?>
</form>
