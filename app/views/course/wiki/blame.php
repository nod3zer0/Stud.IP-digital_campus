<?php
/**
 * @var WikiPage $page
 * @var array $diffarray
 * @var array $line_versions
 */
?>
<h1><?= htmlReady($page->name) . ' - ' . _('Text mit Autor/-innenzuordnung') ?></h1>

<div class="blame_diff">
    <?
    $last_author = 'None';
    $collect = "";
    $version = $line_versions[0];
    foreach ($diffarray as $number => $line) {
        if (!$line || $last_author !== $line->who) {
            if (trim($collect) !== '') : ?>
                <div class="wiki_line">
                    <div class="author">
                        <a href="<?= URLhelper::getLink('dispatch.php/profile', ['username' => get_username($last_author)]) ?>" title="<?= htmlReady(get_fullname($last_author)) ?>">
                            <?= Avatar::getAvatar($last_author)->getImageTag(Avatar::SMALL) ?>
                            <div class="author_name"><?= htmlReady(get_fullname($last_author)) ?></div>
                        </a>
                    </div>
                    <a class="difflink"
                       href="<?= $controller->versiondiff(!$version || is_a($version, WikiPage::class) ? $version : $version->page, is_a($version, WikiVersion::class) ? $version->id : null) ?>"
                       data-dialog
                       title="<?= _('Änderungen anzeigen') ?>">
                        <?= Icon::create('log')->asImg(20, ['class' => 'text-bottom']) ?>
                    </a>
                    <div class="content">
                        <?= wikiReady($collect) ?>
                    </div>
                </div>
            <? endif;
            $collect = "";
        }
        if ($line) {
            $last_author = $line->who;
            $collect .= $line->text;
            $version = $line_versions[$number] ?? null;
        }
    }
    ?>
</div>
