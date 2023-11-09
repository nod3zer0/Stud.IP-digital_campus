<?php
/**
 * @var array $initial_widgets
 * @var StudIPPlugin[] $widgets
 * @var bool $restricted
 * @var string $permission
 */
?>
<div class="edit-widgetcontainer">
    <div class="start-widgetcontainer">
        <? foreach ([0, 1] as $column): ?>
            <ul class="portal-widget-list">
                <? if (isset($initial_widgets[$column])): ?>
                    <? foreach ($initial_widgets[$column] as $widget_id) : ?>
                        <? foreach ($widgets as $widget) : ?>
                            <? if ($widget->getPluginId() == $widget_id): ?>
                                <li class="studip-widget-wrapper" id="<?= $widget_id ?>">
                                    <div class="ui-widget-content studip-widget">
                                        <div class="ui-widget_head widget-header">
                                            <?= htmlReady($widget->getPluginName()) ?>
                                        </div>
                                    </div>
                                </li>
                            <? endif ?>
                        <? endforeach ?>
                    <? endforeach; ?>
                <? endif ?>
            </ul>
        <? endforeach ?>
    </div>

    <h2><?= _('Nicht standardmäßig aktivierte Widgets') ?></h2>
    <div class="available-widgets">
        <ul class="portal-widget-list" style="clear: both;">
        <? foreach ($widgets as $widget) : ?>
            <? if (!in_array($widget->getPluginId(), array_merge(...$initial_widgets))): ?>
                <li class="studip-widget-wrapper" id="<?= $widget->getPluginId() ?>">
                    <div class="ui-widget-content studip-widget">
                        <div class="ui-widget_head widget-header">
                            <?= htmlReady($widget->getPluginName()) ?>
                        </div>
                    </div>
                </li>
            <? endif ?>
        <? endforeach; ?>
        </ul>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        STUDIP.startpage.init_edit('<?= $permission ?>');
    })
}(jQuery));
</script>
