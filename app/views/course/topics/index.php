<? if (count($topics) > 0) : ?>
<table class="default withdetails">
    <colgroup>
        <col width="50%">
        <col>
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Thema') ?></th>
            <th><?= _('Termine') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($topics as $key => $topic) : ?>
        <tr class="<?= Request::get("open") === $topic->getId() ? "open" : "" ?>">
            <td>
                <a href="#" name="<?=$topic->getId()?>" onClick="jQuery(this).closest('tr').toggleClass('open'); return false;">
                <? if ($topic->paper_related): ?>
                    <?= Icon::create('glossary')->asImg(array_merge(
                        ['class' => 'text-top'],
                        tooltip2(_('Thema behandelt eine Hausarbeit oder ein Referat'))
                    )) ?>
                <? endif; ?>
                    <?= htmlReady($topic['title']) ?>
                </a>
            </td>
            <td>
                <ul class="clean">
                    <? foreach ($topic->dates as $date) : ?>
                        <li>
                            <a href="<?= URLHelper::getLink("dispatch.php/course/dates/details/".$date->getId()) ?>" data-dialog="size=auto">
                                <?= Icon::create('date', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                                <?= htmlReady($date->getFullName()) ?>
                            </a>
                        </li>
                    <? endforeach ?>
                </ul>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="2">
                <div class="detailscontainer">
                    <table class="default nohover">
                        <tbody>
                        <tr>
                            <td><strong><?= _('Beschreibung') ?></strong></td>
                            <td><?= formatReady($topic['description']) ?></td>
                        </tr>
                        <tr>
                            <td><strong><?= _('Materialien') ?></strong></td>
                            <td>
                                <? $material = false ?>
                                <ul class="clean">
                                    <? $folder = $topic->folders->first() ?>
                                    <? if ($documents_activated && $folder) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink(
                                                'dispatch.php/course/files/index/' . $folder->id
                                                ) ?>">
                                                <?= $folder->getTypedFolder()->getIcon('clickable')->asImg(['class' => "text-bottom"]) ?>
                                                <?= _('Dateiordner') ?>
                                            </a>
                                        </li>
                                        <? $material = true ?>
                                    <? endif ?>

                                    <? if ($forum_activated && ($link_to_thread = $topic->forum_thread_url)) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink($link_to_thread) ?>">
                                                <?= Icon::create('forum', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                                                <?= _('Thema im Forum') ?>
                                            </a>
                                        </li>
                                        <? $material = true ?>
                                    <? endif ?>
                                </ul>
                                <? if (!$material) : ?>
                                    <?= _('Keine Materialien zu dem Thema vorhanden') ?>
                                <? endif ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="text-align: center;">
                        <? if ($GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) : ?>
                            <?= Studip\LinkButton::createEdit(
                                _('Bearbeiten'),
                                $controller->editURL($topic),
                                ['data-dialog' => '']
                            ) ?>

                            <form action="<?= $controller->delete($topic) ?>" method="post" style="display: inline">
                                <?= Studip\Button::create(
                                    _('Löschen'),
                                    'delete',
                                    ['data-confirm' => _('Das Thema wirklich löschen?')]
                                ) ?>
                            </form>

                            <? if (!$cancelled_dates_locked && $topic->dates->count()) : ?>
                                <?= \Studip\LinkButton::create(_('Alle Termine ausfallen lassen'), URLHelper::getURL("dispatch.php/course/cancel_dates", ['issue_id' => $topic->getId()]), ['data-dialog' => '']) ?>
                            <? endif ?>

                            <span class="button-group">
                            <? if ($key > 0) : ?>
                                <form action="<?= $controller->move_up($topic) ?>" method="post" style="display: inline;">
                                    <?= Studip\Button::createMoveUp(_('nach oben verschieben')) ?>
                                </form>
                            <? endif ?>
                            <? if ($key < count($topics) - 1) : ?>
                                <form action="<?=$controller->move_down($topic)?>" method="post" style="display: inline;">
                                    <?= Studip\Button::createMoveDown(_('nach unten verschieben')) ?>
                                </form>
                            <? endif ?>
                            </span>
                        <? endif ?>
                    </div>
                </div>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<? else : ?>
    <? PageLayout::postInfo(_('Keine Themen vorhanden.')) ?>
<? endif ?>
