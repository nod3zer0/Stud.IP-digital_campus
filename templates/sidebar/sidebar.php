<!-- Start sidebar -->
<aside id="sidebar" aria-label="<?= _('Seitenleiste') ?>">
    <div class="sidebar-image <? if ($avatar) echo 'sidebar-image-with-context'; ?>">
    <? if ($avatar) : ?>
        <div class="sidebar-context">
        <? if ($avatar->is_customized()) : ?>
            <a href="<?= htmlReady($avatar->getURL(Avatar::NORMAL)) ?>"
               data-lightbox="sidebar-avatar"
               data-title="<?= htmlReady(PageLayout::getTitle()) ?>">
        <? endif ?>
                <?= $avatar->getImageTag(Avatar::MEDIUM) ?>
        <? if ($avatar->is_customized()) : ?>
            </a>
        <? endif ?>
        </div>
    <? endif ?>
        <div class="sidebar-title">
            <?= htmlReady($title) ?>
        </div>
    </div>

    <? foreach ($widgets as $index => $widget): ?>
        <?= $widget->render(['base_class' => 'sidebar']) ?>
    <? endforeach; ?>
</aside>
<!-- End sidebar -->
