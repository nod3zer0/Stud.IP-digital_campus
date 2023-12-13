<?php
/**
 * @var TOCItem $toc
 * @var Icon $icon
 * @var Flexi_Template $breadcrumbs
 * @var string $info
 * @var ActionMenu $actionMenu
 * @var Flexi_Template $ttpl
 */
?>
<section>
    <header class="contentbar">
        <nav class="contentbar-nav"></nav>
        <div class="contentbar-wrapper-left">
            <nav class="contentbar-breadcrumb">
            <? if (!$toc->isActive()) : ?>
                <a href="<?= $toc->getUrl() ?>" title="<?= htmlReady($toc->getTitle()) ?>" class="contentbar-icon">
            <? endif ?>
                    <?= $icon->asImg(24, ['class' => 'text-bottom']) ?>
            <? if (!$toc->isActive()) : ?>
                </a>
            <? endif ?>
                <?= $breadcrumbs->render() ?>
            </nav>
        </div>
        <div class="contentbar-wrapper-right">
            <? if (trim($info)): ?>
                <div class="contentbar-info"><?= $info ?></div>
            <? endif; ?>
            <? if ($toc->hasChildren()) : ?>
                <div class="contentbar-button-wrapper contentbar-toc-wrapper">
                    <input type="checkbox" id="cb-toc">
                    <label for="cb-toc" class="contentbar-button contentbar-button-menu check-box enter-accessible" title="<?= _('Inhaltsverzeichnis') ?>" tabindex="0">
                    </label>
                    <?= $ttpl->render() ?>
                </div>
            <? endif ?>

            <? if ($actionMenu) : ?>
                <div class="contentbar-button-wrapper contentbar-action-menu-wrapper">
                    <?= $actionMenu->render() ?>
                </div>
            <? endif ?>
        </div>
    </header>
</section>
