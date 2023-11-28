<?php
/**
 * @var array<array{
 *     type: string,
 *     label: string,
 *     link?: string,
 *     name?: string,
 *     object?: MultiPersonSearch,
 *     icon: Icon,
 *     attributes: array
 * }> $actions
 */
?>
<? foreach ($actions as $action): ?>
    <? if ($action['disabled']): ?>
        <label class="undecorated action-menu-item-disabled" aria-disabled="true" <?= arrayToHtmlAttributes($action['attributes'] + ['title' => $action['label']]) ?>>
        <? if ($action['icon']): ?>
            <?= $action['icon']->asImg(['class' => 'action-menu-item-icon']) ?>
        <? else: ?>
            <?= htmlReady($action['label']) ?>
        <? endif ?>
        </label>
    <? elseif ($action['type'] === 'link'): ?>
        <a href="<?= htmlReady($action['link']) ?>" <?= arrayToHtmlAttributes($action['attributes'] + ['title' => $action['label']]) ?>>
            <? if ($action['icon']): ?>
                <?= $action['icon']->asImg(['class' => 'action-menu-item-icon']) ?>
            <? else: ?>
                <?= htmlReady($action['label']) ?>
            <? endif ?>
        </a>
    <? elseif ($action['type'] === 'button'): ?>
        <? if ($action['icon']): ?>
            <?= $action['icon']->asInput($action['attributes'] + [
                'class' => 'action-menu-item-icon',
                'name'  => $action['name'],
                'title' => $action['label'],
            ]) ?>
        <? else: ?>
            <button name="<?= htmlReady($action['name']) ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                <?= htmlReady($action['label']) ?>
            </button>
        <? endif ?>
    <? elseif ($action['type'] === 'multi-person-search'): ?>
        <?= $action['object']->render(false) ?>
    <? elseif ($action['type'] === 'separator'): ?>
        <span class="quiet">|</span>
    <? endif ?>
<? endforeach ?>
