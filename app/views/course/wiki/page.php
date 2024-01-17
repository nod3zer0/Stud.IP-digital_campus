<?php
/**
 * @var WikiPage $page
 * @var string $edit_perms
 * @var Context $range
 * @var Course_WikiController $controller
 */

echo $contentbar;
?>

<? if ($page->isEditable()) : ?>
<form action="<?= $controller->delete($page->id) ?>" method="post" id="delete_page">
    <?= CSRFProtection::tokenTag() ?>
</form>
<? endif ?>

<? if ($page->isNew()) : ?>
    <section>
        <? if ($edit_perms !== 'all' && !$GLOBALS['perm']->have_studip_perm($edit_perms, $range->id)) : ?>
            <div class="wiki-empty-background"></div>
        <? else : ?>
            <a href="<?= $controller->new_page() ?>"
               data-dialog
               class="wiki-empty-background"
               title="<?= _('Dieses Wiki ist noch leer. Erstellen Sie die erste Wiki-Seite.') ?>"></a>
        <? endif ?>
        <div class="flex">
            <? if ($edit_perms !== 'all' && !$GLOBALS['perm']->have_studip_perm($edit_perms, $range->id)) : ?>
                <div class="wiki-teaser">
            <? else : ?>
                <a href="<?= $controller->new_page() ?>"
                   data-dialog
                   class="wiki-teaser">
            <? endif ?>
            <?= _('Mach die Welt ein Stückchen schlauer.') ?>
            <? if ($edit_perms !== 'all' && !$GLOBALS['perm']->have_studip_perm($edit_perms, $range->id)) : ?>
                </div>
            <? else : ?>
                </a>
            <? endif ?>
        </div>
    </section>
<? else : ?>
    <article class="studip wiki">
        <section>
            <div class="wiki_page_content wiki_page_content_<?= htmlReady($page->id) ?>"
                 data-page_id="<?= htmlReady($page->id) ?>">
                <?= wikiReady($page->content, true, $range->id, $page->id) ?>
            </div>
        </section>
        <? if ($page->isEditable()) : ?>
            <footer id="wikifooter">
                <div class="button-group">
                    <?= \Studip\LinkButton::create(_('Bearbeiten'), $controller->editURL($page)) ?>
                </div>
            </footer>
        <? endif ?>
    </article>
<? endif ?>
