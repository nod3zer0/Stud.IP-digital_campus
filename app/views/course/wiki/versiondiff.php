<?php
/**
 * @var WikiPage|WikiVersion $version
 * @var Course_WikiController $controller
 */
?>
<h3>
    <a href="<?= is_a($version, WikiPage::class) ? $controller->page($version) : $controller->version($version) ?>">
        <? $chdate = is_a($version, WikiPage::class) ? $version->chdate : $version->mkdate ?>
        <?= sprintf(
            _('Version %1$s, geändert von %2$s am %3$s.'),
            htmlReady($version->versionnumber),
            htmlReady($version->user ? $version->user->getFullName() : _('unbekannt')),
            $chdate > 0 ? date('d.m.Y H:i:s', $chdate) : _('unbekannt')) ?>
    </a>
</h3>
<div class="wiki_diffs">
    <?= $diff ?>
</div>
