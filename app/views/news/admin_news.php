<? use Studip\Button, Studip\LinkButton; ?>
<? if (!empty($flash['question_text'])) : ?>
<?= QuestionBox::create(
        htmlReady($flash['question_text']),
        $controller->url_for('news/admin_news/' . $area_type, array_merge(
            $flash['question_param'],
            [
                'news_filter_term'  => htmlReady($news_searchterm),
                'news_filter_start' => $news_startdate,
                'news_filter_end'   => $news_enddate,
                'news_filter'       => 'set'
            ]
        )),
        $controller->url_for('news/admin_news/' . $area_type, [
            'news_filter_term'  => htmlReady($news_searchterm),
            'news_filter_start' => $news_startdate,
            'news_filter_end'   => $news_enddate,
            'news_filter'       => 'set'
        ])
    );
?>
<? endif ?>

<form action="<?= $controller->link_for('news/admin_news/' . $area_type) ?>" id="admin_news_form" class="default" method="post">
    <input type="hidden" name="news_filter" value="set">
    <input type="hidden" name="news_filter_term" value="<?= htmlReady($news_searchterm) ?>">
    <input type="hidden" name="news_filter_start" value="<?= $news_startdate ?>">
    <input type="hidden" name="news_filter_end" value="<?= $news_enddate ?>">
    <?= CSRFProtection::tokenTag(); ?>

    <fieldset>
        <legend><?= _('Meine Ankündigungen') ?></legend>

        <label>
            <?= _('Suchbegriff') ?>
            <input type="text" name="news_searchterm" aria-label="<?= _('Suchbegriff') ?>"
                   value="<?= htmlReady($news_searchterm) ?>">
        </label>

        <label class="col-1">
            <?= _('Anzeige von') ?>
            <input class="has-date-picker" type="text" name="news_startdate"
                   aria-label="<?= _('Ankündigungen anzeigen, die ab diesem Datum sichtbar sind') ?>"
                   value="<?= $news_startdate ? date('d.m.Y', $news_startdate) : '' ?>">
        </label>

        <label class="col-1">
            <?= _('Anzeige bis') ?>
            <input class="has-date-picker" type="text" name="news_enddate"
                   aria-label="<?= _('Ankündigungen anzeigen, die vor diesem Datum sichtbar sind') ?>"
                   value="<?= $news_enddate ? date('d.m.Y', $news_enddate) : '' ?>">
        </label>
    </fieldset>
    <footer>
        <?= Button::create(_('Filter anwenden'), 'apply_news_filter', ['aria-label' => _('Liste mit Suchbegriff und/oder Zeitraum filtern')]) ?>
    <? if ($filter_text) : ?>
        <?= Button::create(_('Filter zurücksetzen'), 'reset_filter') ?>
    <? endif ?>
    </footer>
    <br>

<? if ($filter_text) : ?>
    <?= MessageBox::info(htmlReady($filter_text) )?>
<? endif ?>

<? if ($news_items && count($news_items)) : ?>
    <? foreach ($area_structure as $type => $area_data) : ?>
        <? $last_title = 'none' ?>
        <? if (isset($news_items[$type]) && is_array($news_items[$type]) && count($news_items[$type]) > 0) : ?>
            <table class="default">
            <? if (!$area_type) : ?>
                <caption>
                    <?= htmlReady($area_data['title']) ?>
                </caption>
            <? endif ?>
                <colgroup>
                    <col width="20">
                    <col>
                    <col width="25%">
                    <col width="10%">
                    <col width="10%">
                    <col width="80">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th><?= _('Überschrift') ?></th>
                        <th><?= _('Autor') ?></th>
                        <th><?= _('Einstelldatum') ?></th>
                        <th><?= _('Ablaufdatum') ?></th>
                        <th class="actions"><?= _('Aktion') ?></th>
                    </tr>
                </thead>
                <tbody>
                <? foreach ($news_items[$type] as $news) : ?>
                    <? $title = $news['title'] ?>
                    <? if ($title !== $last_title) : ?>
                        <? if ($title) : ?>
                            <tr>
                                <th>
                                    <input type="checkbox"
                                           data-proxyfor=".news_<?= $news['range_id'] ?>"
                                           aria-labelledby="<?= _('Alle auswählen') ?>">
                                </th>
                                <th colspan="5"><?= mila(htmlReady($news['title'] ?? '')) . ' ' . htmlReady($news['semester'] ?? '') ?></th>
                            </tr>
                        <? endif ?>
                        <? $last_title = $title ?>
                    <? endif ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="news_<?= $news['range_id'] ?>" name="mark_news[]"
                                   value="<?= $news['object']->news_id . '_' . $news['range_id'] ?>"
                                   aria-label="<?= _('Diese Ankündigung zum Entfernen vormerken') ?>" <?= tooltip(_("Diese Ankündigung zum Entfernen vormerken"), false) ?>>
                        </td>
                        <td><?= htmlReady($news['object']->topic) ?></td>
                        <?
                            $parts = explode('<admin_msg>', $news['object']->body);
                            $body = $parts[0];
                            $admin_msg = $parts[1] ?? ';'
                        ?>
                        <td><?= htmlReady($news['object']->author) ?></td>
                        <td><?= strftime("%d.%m.%y", $news['object']->date) ?></td>
                        <td><?= strftime("%d.%m.%y", $news['object']->date + $news['object']->expire) ?></td>
                        <td class="actions">
                            <?
                            $menu = ActionMenu::get()->setContext($news['object']->topic);
                            $menu->addLink(
                                $controller->url_for('news/edit_news/' . $news['object']->news_id),
                                _('Ankündigung bearbeiten'),
                                Icon::create('edit'),
                                ['data-dialog' => '', 'target' => '_blank']
                            );
                            $menu->addLink(
                                $controller->url_for('news/edit_news/new/template/' . $news['object']->news_id),
                                _('Kopieren, um neue Ankündigung zu erstellen'),
                                Icon::create('clipboard'),
                                ['data-dialog' => '1', 'target' => '_blank']
                            );
                            if ($news['object']->havePermission('unassign', $news['range_id'])) {
                                $menu->addButton(
                                    'news_remove_' . $news['object']->news_id . '_' . $news['range_id'],
                                    _('Ankündigung aus diesem Bereich entfernen'),
                                    Icon::create('remove')
                                );
                            } else {
                                $menu->addButton(
                                    'news_remove_' . $news['object']->news_id . '_' . $news['range_id'],
                                    _('Ankündigung löschen'),
                                    Icon::create('trash')
                                );
                            }
                            echo $menu->render();
                            ?>
                        </td>
                    </tr>
                <? endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">
                            <?= Button::create(_('Alle markierten Ankündigungen entfernen'), 'remove_marked_news') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        <? endif ?>
    <? endforeach ?>
<? else : ?>
    <?= MessageBox::info(_('Keine Ankündigungen vorhanden.')) ?>
<? endif ?>
</form>
