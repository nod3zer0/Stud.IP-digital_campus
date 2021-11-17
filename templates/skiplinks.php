<?
# Lifter010: TODO
?>
<? if ($navigation instanceof Navigation && iterator_count($navigation) > 0) : ?>
    <ul role="navigation" id="skiplink_list">
    <? foreach ($navigation as $nav) : ?>
        <li>
        <? if (mb_substr($url = $nav->getURL(), 0, 1) == '#') : ?>
            <button class="skiplink" onclick="STUDIP.SkipLinks.setActiveTarget('<?= $url ?>');"><?= htmlReady($nav->getTitle()) ?></button>
        <? else : ?>
            <? if (is_internal_url($url)) : ?>
                <a href="<?= URLHelper::getLink($url) ?>"><?= htmlReady($nav->getTitle()) ?></a>
            <? else : ?>
                <a href="<?= htmlReady($url) ?>"><?= htmlReady($nav->getTitle()) ?></a>
            <? endif ?>
        <? endif ?>
        </li>
    <? endforeach ?>
    </ul>
<? endif ?>
