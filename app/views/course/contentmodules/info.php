<? if ($plugin->getDescriptionMode() === 'replace_all') : ?>
    <?= formatReady($plugin->getPluginDescription()) ?>
<? else : ?>
    <div class="contentmodule_info">
        <div class="main_part">
            <div class="header">
                <div class="image">
                    <?
                    if ($metadata['icon']) {
                        $icon = $metadata['icon'] instanceof Icon
                            ? $metadata['icon']->asImagePath()
                            : Icon::create($plugin->getPluginURL().'/'.$metadata['icon'])->asImagePath();
                    } else {
                        $icon = null;
                    }
                    if ($icon && !is_a($icon, 'Icon')) {
                        $icon = Icon::create($icon);
                    }
                    ?>
                    <? if ($icon) : ?>
                        <?= $icon->asImg(100) ?>
                    <? endif ?>
                </div>
                <div class="text">
                    <h1><?= htmlReady($metadata['displayname'] ?? $plugin->getPluginName()) ?></h1>
                    <strong>
                        <?= htmlReady($metadata['summary']) ?>
                    </strong>
                </div>
            </div>
            <div class="content-modules-controls-vue-app" is="ContentModulesControl" module_id="<?= htmlReady($plugin->getPluginId()) ?>"></div>
            <? $keywords = preg_split( "/;/", $metadata['keywords'], -1, PREG_SPLIT_NO_EMPTY) ?>
            <? if (count($keywords)) : ?>
            <ul class="keywords">
                <? foreach ($keywords as $keyword) : ?>
                <li>
                    <?= htmlReady($keyword) ?>
                </li>
                <? endforeach ?>
            </ul>
            <? endif ?>
            <div class="description">
                <?= formatReady($plugin->getPluginDescription()) ?>
            </div>
        </div>
        <? if (isset($metadata['screenshots']) && count($metadata['screenshots']['pictures'])) : ?>
        <ul class="screenshots clean">
            <? foreach ($metadata['screenshots']['pictures'] as $pictures) : ?>
            <li>
                <a href="<?= $plugin->getPluginURL().$metadata['screenshots']['path'].'/'.$pictures['source'] ?>"
                   data-lightbox="<?= htmlReady($metadata['displayname'] ?? $plugin->getPluginName()) ?>"
                   data-title="<?= htmlReady($pictures['title']) ?>">
                    <img src="<?= $plugin->getPluginURL().$metadata['screenshots']['path'].'/'.$pictures['source'] ?>" alt="">
                    <?= htmlReady($pictures['title']) ?>
                </a>
            </li>
            <? endforeach ?>
        </ul>
        <? endif ?>
    </div>
<? endif ?>
