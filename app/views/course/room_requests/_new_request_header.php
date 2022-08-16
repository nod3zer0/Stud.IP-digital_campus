<?= MessageBox::info(
    _('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.'),
    [_('<strong>Achtung:</strong> Um später einen passenden Raum für Ihre Veranstaltung zu bekommen, geben Sie bitte immer die gewünschten Eigenschaften mit an!')]
)?>

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
        <section></section>
    </section>
</section>
