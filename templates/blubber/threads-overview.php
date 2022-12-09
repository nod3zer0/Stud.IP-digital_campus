<div class="sidebar-widget blubber_threads_widget"
     data-threads_data="<?= htmlReady(json_encode($json)) ?>">
    <div class="sidebar-widget-header">
        <div class="actions">
            <? if ($with_composer) : ?>
                <a href="<?= URLHelper::getLink("dispatch.php/blubber/compose") ?>" data-dialog="width=600;height=300">
                    <?= Icon::create("add", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
            <? endif ?>
        </div>
        <?= count($json) > 1 ? _("Konversationen") : _("Konversation") ?>
    </div>
    <div class="sidebar-widget-content">
        <div id="blubber-threads-widget"></div>
    </div>
</div>
