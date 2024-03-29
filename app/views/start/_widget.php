<?php
/**
 * @var StartController $controller
 * @var StudIPPlugin $widget
 * @var string $admin_title
 * @var string $content_for_layout
 * @var Navigation[]|null $icons
 * @var string|null $admin_url
 */
?>
<div class="ui-widget_head widget-header" id="widget-<?= $widget->getPluginId() ?>">
    <span class="header-options">
        <? if (isset($icons)): ?>
            <? foreach ($icons as $nav): ?>
                <? if ($nav->isVisible(true)): ?>
                    <? $attr = $nav->getLinkAttributes() ?>
                    <a href="<?= URLHelper::getLink($nav->getURL()) ?>"
                        <? foreach ($attr as $key => $value): ?>
                            <? if ($key !== 'src'): ?>
                                <?= $key ?>="<?= htmlReady($value) ?>"
                            <? endif ?>
                        <? endforeach ?>>
                        <?= $nav->getImage() ?>
                    </a>
                <? endif ?>
            <?endforeach ?>
        <? endif ?>

        <? if (isset($admin_url)): ?>
            <a href="<?= URLHelper::getLink($admin_url) ?>">
                <?= Icon::create('admin', Icon::ROLE_CLICKABLE, ['title' => $admin_title]) ?>
            </a>
        <? endif ?>

        <a href="<?= $controller->url_for('start/delete/' . $widget->getPluginId()) ?>">
            <?= Icon::create('decline', Icon::ROLE_CLICKABLE, ['title' => _('Entfernen')]) ?>
        </a>
    </span>
    <span id="widgetName<?= $widget->getPluginId() ?>" class="widget-title">
        <?= htmlReady($title ?? $widget->getPluginName()) ?>
    </span>
</div>
<div id="wid<?=$widget->getPluginId()?>">
    <?= $content_for_layout ?>
</div>
