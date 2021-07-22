<div class="contentbar">
    <div class="contentbar_title">
        <? if (!$toc->isActive()) : ?>
        <a href="<?= $toc->getUrl() ?>" title="<?= htmlReady($toc->getTitle()) ?>">
        <? endif ?>
            <?= $icon->asImg(24, ['class' => 'text-bottom']) ?>
        <? if (!$toc->isActive()) : ?>
            </a>
        <? endif ?>
        <ul class="breadcrumb"><?= $breadcrumbs->render() ?></ul>
    </div>

    <div class="contentbar_info">
        <div class="textblock"><?= $info ?></div>

        <div class="contentbar-icons">
            <? if ($toc->hasChildren()) : ?>
                <input type="checkbox" id="cb-toc">
                <label for="cb-toc" class="check-box" title="<?= _('Inhaltsverzeichnis') ?>" >
                    <?= Icon::create('table-of-contents')->asImg(24) ?>
                </label>
                <?= $ttpl->render() ?>
            <? endif ?>

            <a class="consuming_mode_trigger"
               href="#"
               title="<?= _("Konsummodus ein-/ausschalten") ?>">
            </a>

            <? if ($actionMenu) : ?>
                <?= $actionMenu->render() ?>
            <? endif ?>
        </div>
    </div>

</div>
