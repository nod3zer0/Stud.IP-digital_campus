<? use Studip\Button, Studip\LinkButton; ?>
<?= $delete_question ?>

<form action="<?= $controller->url_for('tour/admin_overview') ?>" id="admin_tour_form" method="post" class="default">
    <input type="hidden" name="tour_filter" value="set">
    <input type="hidden" name="tour_filter_term" value="<?= htmlReady($tour_searchterm) ?>">
    <?= CSRFProtection::tokenTag(); ?>
<? if ($filter_text) : ?>
    <table class="default">
        <tr>
            <td><?= htmlReady($filter_text) ?></td>
            <td>
                <div class="tour_reset_filter">
                    <?= Button::create(_('Auswahl aufheben'), 'reset_filter') ?>
                </div>
            </td>
        </tr>
    </table>
<? endif ?>

    <table class="default sortable-table">
        <caption>
            <div class="tour_list_title"><?= _('Touren') ?></div>
        </caption>
        <thead>
            <tr>
                <th><?= _('Aktiv') ?></th>
                <th data-sort="text"><?= _('Überschrift') ?></th>
                <th data-sort="htmldata"><?= _('Stud.IP-Version') ?></th>
                <th data-sort="text"><?= _('Sprache') ?></th>
                <th data-sort="text"><?= _('Typ') ?></th>
                <th data-sort="text"><?= _('Zugang') ?></th>
                <th data-sort="text"><?= _('Startseite') ?></th>
                <th data-sort="htmldata"><?= _('Anzahl der Schritte') ?></th>
                <th data-sort="htmldata"><?= _('Letzte Änderung') ?></th>
                <th data-sort="htmldata"><?= _('Geändert von') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
    <? if (count($tours)) : ?>
        <tbody>
        <? foreach ($tours as $tour_id => $tour) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="tour_status_<?= htmlReady($tour_id) ?>" value="1"
                           aria-label="<?= _('Status der Tour (aktiv oder inaktiv)') ?>" <?= tooltip(_("Status der Tour (aktiv oder inaktiv)"), false) ?><?= ($tour->settings->active) ? ' checked' : '' ?>>
                </td>
                <td>
                    <a href="<?= $controller->link_for('tour/admin_details/' . htmlReady($tour_id)) ?>">
                        <?= htmlReady($tour->name) ?>
                        <?= tooltipIcon($tour->description) ?>
                    </a>
                </td>
                <td><?= htmlReady($tour->studip_version) ?></td>
                <td><?= htmlReady($tour->language) ?></td>
                <td><?= htmlReady($tour->type) ?></td>
                <td><?= htmlReady($tour->settings->access) ?></td>
                <td>
                <? if (count($tour->steps)): ?>
                    <?= htmlReady($tour->steps[0]->route) ?>
                <? endif; ?>
                </td>
                <td><?= count($tour->steps) ?></td>
                <td><?= $tour->chdate ? date('d.m.Y H:i', $tour->chdate) : '' ?></td>
                <td>
                    <? if ($tour->author) : ?>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $tour->author->username]) ?>" class="link-intern" title="<?= _('Zum Profil') ?>">
                            <?= htmlReady($tour->author->getFullName()) ?>
                        </a>
                    <? elseif ($tour->author_email) : ?>
                        <a href="mailto:<?= htmlReady($tour->author_email) ?>">
                            <?= htmlReady($tour->author_email) ?>
                        </a>
                    <? else : ?>
                        <?= _('unbekannt') ?>
                    <? endif ?>
                </td>
                <td class="actions">
                    <?= ActionMenu::get()->setContext(
                        $tour->name
                    )->addLink(
                        $controller->url_for('tour/admin_details/' . $tour_id),
                        _('Tour bearbeiten'),
                        Icon::create('edit')
                    )->addLink(
                        $controller->url_for('tour/export/' . $tour_id),
                        _('Tour exportieren'),
                        Icon::create('export'),
                        ['disabled' => count($tour->steps) === 0]
                    )->addButton(
                        'tour_remove_' . $tour_id,
                        _('Tour löschen'),
                        Icon::create('trash')
                    ) ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="11">
                    <?= Button::createAccept(_('Speichern'), 'save_tour_settings') ?>
                </td>
            </tr>
        </tfoot>
    <? else : ?>
        <tbody>
            <tr>
                <td colspan="11" style="text-align: center">
                    <?= _('Keine Touren vorhanden.') ?>
                </td>
            </tr>
        </tbody>
    <? endif ?>
    </table>
</form>
