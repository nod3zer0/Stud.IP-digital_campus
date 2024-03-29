<?= $this->render_partial('search/breadcrumb') ?>
<table class="default nohover">
    <caption>
        <?= _('Studiengang') ?>: <?= htmlReady($studiengang->getDisplayName()) ?>
        <? if (Config::get()->ENABLE_STUDYCOURSE_INFO_PAGE) : ?>
            <a href="<?= $controller->link_for('search/studiengaenge/info', $studiengang->id)?>" data-dialog>
                <?= Icon::create('infopage2')->asImg(['title' => _('Informationen zum Studiengang')]) ?>
            </a>
        <? endif; ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Fächer') ?></th>
        <? foreach ($studiengangTeilBezeichnungen as $teil_bezeichnung): ?>
            <th style="text-align: center;"><?= htmlReady($teil_bezeichnung->getDisplayName()) ?></th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <? foreach ($data as $fach_id => $fach): ?>
        <tr>
            <td>
                <?= htmlReady($fachNamen[$fach_id]) ?>
            </td>
        <? foreach ($studiengangTeilBezeichnungen as $teil_bezeichnung): ?>
            <td style="text-align: center;">
            <? if (isset($fach[$teil_bezeichnung->id])) : ?>
                <a href="<?= $controller->link_for($verlauf_url, $fach[$teil_bezeichnung->id], $teil_bezeichnung->id, $studiengang_id ?? null) ?>">
                    <?= Icon::create('info-circle-full')->asImg(['title' => _('Studienverlaufsplan anzeigen')]) ?>
                </a>
            <? endif; ?>
            </td>
        <? endforeach; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
