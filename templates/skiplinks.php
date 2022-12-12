<?
# Lifter010: TODO
?>
<? if ($navigation instanceof Navigation && iterator_count($navigation) > 0) : ?>
    <ul role="navigation" id="skiplink_list">
    <? foreach ($navigation as $index => $nav) : ?>
        <li>
        <? if (mb_substr($url = $nav->getURL(), 0, 1) == '#') : ?>
            <button class="skiplink" role="link" onclick="STUDIP.SkipLinks.setActiveTarget('<?= htmlReady($url) ?>');"
                    data-in-fullscreen="<?= $fullscreen[$index] ?>">
                <?= htmlReady($nav->getTitle()) ?>
            </button>
        <? else : ?>
            <? if (is_internal_url($url)) : ?>
                <a href="<?= URLHelper::getLink($url) ?>" data-in-fullscreen="<?= $fullscreen[$index] ?>">
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? else : ?>
                <a href="<?= htmlReady($url) ?>" data-in-fullscreen="<?= $fullscreen[$index] ?>">
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? endif ?>
        <? endif ?>
        </li>
    <? endforeach ?>
    </ul>
<? endif ?>
