<ul class="content-items">
    <? foreach ($tiles as $key => $navigation): ?>
        <? if ($navigation->isVisible() && $key !== 'overview'): ?>
            <li class="content-item content-item-<?= htmlReady($key) ?>">
                <a href="<?= URLHelper::getLink($navigation->getURL()) ?>" class="content-item-link">
                    <div class="content-item-img-wrapper">
                        <? if ($navigation->getImage()): ?>
                            <?= $navigation->getImage()->asImg(64, $navigation->getLinkAttributes()) ?>
                        <? endif ?>
                    </div>
                    <div class="content-item-text">
                        <p class="content-item-title">
                            <?= htmlReady($navigation->getTitle()) ?>
                        </p>
                        <p class="content-item-description">
                            <?= htmlReady(mila($navigation->getDescription(), 70)) ?>
                        </p>
                    </div>
                </a>
            </li>
        <? endif ?>
    <? endforeach ?>
</ul>
