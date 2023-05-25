<section class="resources-grid">
    <section class="contentbox">
        <header><h1><?= _('Anfrage') ?></h1></header>
        <section>
            <?= htmlready($request->getTypeString(), 1, 1) ?>
            <? if ($request->getType() == 'course'): ?>
                <?
                $dates = $request->getDateString(true);
                ?>
                <?= tooltipHtmlIcon(implode('<br>', $dates)) ?>
            <? endif ?>
        </section>
    </section>
    <section class="contentbox">
        <header><h1><?= _('Bearbeitungsstatus') ?></h1></header>
        <section><?= htmlReady($request->getStatusText()) ?></section>
    </section>
</section>
