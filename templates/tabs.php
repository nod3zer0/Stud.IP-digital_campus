<?
# Lifter010: TODO
foreach (Navigation::getItem("/")->getSubNavigation() as $path => $nav) {
    if ($nav->isActive()) {
        $path1 = $path;
    }
}
?>
<div class="tabs_wrapper">
    <? SkipLinks::addIndex(_('Zweite Navigationsebene'), 'navigation-level-2', 10, false); ?>
    <ul id="tabs">
        <? if (!empty($navigation)): ?>
        <? foreach ($navigation as $path => $nav) : ?>
            <? if ($nav->isVisible()) : ?>
                <li id="nav_<?= $path1 ?>_<?= $path ?>"<?= $nav->isActive() ? ' class="current"' : '' ?>>
                    <? if ($nav->isEnabled()): ?>
                        <?
                        $attr = array_merge(['class' => ''], $nav->getLinkAttributes());
                        if ($nav->hasBadgeNumber()) {
                            $attr['class'] = trim("{$attr['class']} badge");
                            $attr['data-badge-number'] = (int) $nav->getBadgeNumber();
                        }
                        ?>
                        <a href="<?= URLHelper::getLink($nav->getURL()) ?>" <?= arrayToHtmlAttributes($attr) ?>>
                            <? if ($image = $nav->getImage()) : ?>
                                <?= $image->asImg(['class' => "tab-icon", 'title' => $nav->getTitle() ? $nav->getTitle() : $nav->getDescription()]) ?>
                            <? endif ?>
                            <span title="<?= $nav->getDescription() ? htmlReady($nav->getDescription()) : htmlReady($nav->getTitle())?>" class="tab-title"><?= htmlReady($nav->getTitle()) ?></span>
                        </a>
                    <? else: ?>
                        <span class="quiet tab-title">
                            <? if ($image = $nav->getImage()) : ?>
                                <?= $image->asImg(['class' => "tab-icon", 'title' => $nav->getTitle()]) ?>
                            <? endif ?>
                            <?= htmlReady($nav->getTitle()) ?>
                        </span>
                    <? endif ?>
                </li>
            <? endif ?>
        <? endforeach ?>
       <? endif; ?>
    </ul>
    <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor')) : ?>
        <?= Helpbar::get()->render() ?>
    <? endif; ?>
    <? if (User::findCurrent()) : ?>
    <div id="non-responsive-toggle-fullscreen">
        <button class="styleless" id="fullscreen-on"
                title="<?= _('Kompakte Navigation aktivieren') ?>">
            <?= Icon::create('screen-compact')->asImg(24) ?>
        </button>
    </div>
    <? endif ?>
</div>
