<li class="chapter<?= $item->isActive() ? ' active' : '' ?>">
    <div>
        <? if (!$item->isActive()) : ?>
            <a class="navigate" href="<?= htmlReady($item->getURL()) ?>">
        <? endif ?>
            <? if ($item->getIcon()) : ?>
                <?= $item->getIcon()->copyWithRole(Icon::ROLE_INFO)->asImg(24) ?>
            <? endif ?>
            <?= htmlReady($item->getTitle()) ?>
        <? if (!$item->isActive()) : ?>
            </a>
        <? endif ?>
    </div>
    <? if ($item->hasChildren()) : ?>
        <ul class="toc">
            <? foreach ($item->getChildren() as $child) : ?>
                <?= $this->render_partial('toc/_toc-item-list', ['item' => $child]) ?>
            <? endforeach ?>
        </ul>
    <? endif ?>
</li>
