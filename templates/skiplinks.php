<?
# Lifter010: TODO
?>
<? if ($navigation instanceof Navigation && iterator_count($navigation) > 0) : ?>
    <ul id="skiplink_list">
    <? foreach ($navigation as $index => $nav) : ?>
        <li>
        <? if (mb_substr($url = $nav->getURL(), 0, 1) == '#') : ?>
            <button class="skiplink" role="link" onclick="STUDIP.SkipLinks.setActiveTarget('<?= htmlReady($url) ?>');"
                    data-in-fullscreen="<?= $fullscreen[$index] ?>">
                <?= htmlReady($nav->getTitle()) ?>
            </button>
        <? else : ?>
            <a href="<?= URLHelper::getLink($url, [], !is_internal_url($url)) ?>" data-in-fullscreen="<?= $fullscreen[$index] ?>">
                <?= htmlReady($nav->getTitle()) ?>
            </a>
        <? endif ?>
        </li>
    <? endforeach ?>
    </ul>
<? endif ?>
