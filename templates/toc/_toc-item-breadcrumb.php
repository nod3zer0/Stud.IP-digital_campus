<? if (!$item->isRoot()) : ?>
    <?= $this->render_partial('toc/_toc-item-breadcrumb', ['item' => $item->getParent()]) ?>
<? endif ?>
<li>
    <? if (!$item->isActive()) : ?>
        <a class="navigate" href="<?= htmlReady($item->getURL()) ?>">
    <? endif ?>
        <?= htmlReady($item->getTitle()) ?>
    <? if (!$item->isActive()) : ?>
        </a>
    <? endif ?>
</li>
