<? // class "action-menu" will be set from API ?>
<nav <?= arrayToHtmlAttributes($attributes) ?> aria-role="presentation">
    <button class="action-menu-icon" aria-role="button" aria-expanded="false" title="<?= htmlReady($action_menu_title) ?>">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div class="action-menu-content">
        <div class="action-menu-title" aria-hidden="true">
            <?= _('Aktionen') ?>
        </div>
        <ul class="action-menu-list" aria-label="<?= _('Aktionen') ?>">
        <? foreach ($actions as $action): ?>
            <li class="action-menu-item <? if (isset($action['attributes']['disabled'])) echo 'action-menu-item-disabled'; ?>">
            <? if ($action['type'] === 'link'): ?>
                <a href="<?= htmlReady($action['link']) ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                    <? if ($action['icon']): ?>
                        <?= $action['icon'] ?>
                    <? else: ?>
                        <span class="action-menu-no-icon"></span>
                    <? endif ?>
                    <?= htmlReady($action['label']) ?>
                </a>
            <? elseif ($action['type'] === 'button'): ?>
                <? if ($action['icon']): ?>
                    <label class="undecorated">
                        <?= $action['icon']->asInput($action['attributes'] + ['name' => $action['name'], 'title' => $action['label']]) ?>
                        <?= htmlReady($action['label']) ?>
                    </label>
                <? else: ?>
                    <span class="action-menu-no-icon"></span>
                    <button name="<?= htmlReady($action['name']) ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                        <?= htmlReady($action['label']) ?>
                    </button>
                <? endif ?>
            <? elseif ($action['type'] === 'multi-person-search'): ?>
                <?= $action['object']->render() ?>
            <? endif ?>
            </li>
        <? endforeach ?>
        </ul>
    </div>
</nav>
