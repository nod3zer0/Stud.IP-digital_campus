<?php
/**
 * @var Oer_MarketController $controller
 * @var OERMaterial $material
 */
?>
<article class="contentbox">
    <a href="<?= $controller->link_for('oer/market/details', $material) ?>" target="_blank">
        <header>
            <h1>
                <?= $material->getIcon()->asImg(['class' => 'text-bottom']) ?>
                <div class="title">
                    <? if (strlen($material->name) > 50) : ?>
                        <?= htmlReady(substr($material->name, 0, 50)) . ' ...' ?>
                    <? else : ?>
                        <?= htmlReady($material->name) ?>
                    <? endif ?>
                </div>
            </h1>
        </header>
        <div class="image" style="background-image: url(<?= htmlReady($material->getLogoURL()) ?>);<?= !$material->front_image_content_type ? ' background-size: 60% auto;' : '' ?>"></div>
    </a>
</article>
