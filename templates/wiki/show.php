<?= $contentbar ?? '' ?>
<article class="studip wiki" id="main_content" role="main">
    <section>
        <? if ($wikipage->keyword == 'WikiWikiWeb' && $wikipage->isNew()): ?>
            <div class="wiki-empty-background"></div>
            <div class="flex">
                <div class="wiki-teaser">
                    <?= _('Mach die Welt ein Stückchen schlauer.') ?>
                </div>
            </div>
        <? else : ?>
            <?= $content ?>
        <? endif ?>
    </section>
    <? if ($wikipage->isEditableBy($GLOBALS['user'])): ?>
        <footer id="wikifooter">
            <div class="button-group">

            <?= Studip\LinkButton::create(
                $wikipage->isNew() ? _('Neue Seite anlegen') : ('Bearbeiten'),
                URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'view' => 'edit'])
            ) ?>
            </div>
        </footer>
    <? endif ?>
</article>
