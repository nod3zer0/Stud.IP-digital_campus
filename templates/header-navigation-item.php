<?php
$attributes = $nav->getLinkAttributes();

$image_attributes = $nav->getImage()->getAttributes();
if (!empty($image_attributes['title'])) {
    $attributes['title'] = $image_attributes['title'];
}

// Add badge number to link attributes
if ($nav->getBadgeNumber()) {
    $attributes['data-badge'] = (int)$nav->getBadgeNumber();
}

// Convert link attributes array to proper attribute string
$attr_str = arrayToHtmlAttributes($attributes);
?>

<li id="nav_<?= $path ?>"<? if ($nav->isActive()) : ?> class="active"<? endif ?>>
    <a href="<?= URLHelper::getLink($nav->getURL(), $link_params) ?>" <?= $attr_str ?>>
        <?= $nav->getImage()->asImg(['class' => 'headericon original', 'title' => null]) ?>
        <div class="navtitle"><?= htmlReady($nav->getTitle()) ?></div>
    </a>
</li>
