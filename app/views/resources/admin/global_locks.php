<? if ($locks): ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Beginn') ?></th>
                <th><?= _('Ende') ?></th>
                <th><?= _('Typ der Sperrung') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($locks as $lock): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', $lock->begin) ?></td>
                    <td><?= date('d.m.Y H:i', $lock->end) ?></td>
                    <td><?= $lock->getTypeString() ?></td>
                    <td class="actions">
                        <?= ActionMenu::get()->setContext(
                            sprintf(
                                _('Sperre vom %1$s, %2$s Uhr bis %3$s, %4$s Uhr'),
                                strftime('%x', $lock->begin),
                                date('H:i', $lock->begin),
                                strftime('%x', $lock->end),
                                date('H:i', $lock->end)
                            )
                        )->addLink(
                                $controller->url_for('resources/global_locks/edit/' . $lock->id),
                                _('Sperrung bearbeiten'),
                                Icon::create('edit'),
                                [
                                    'data-dialog' => 'size=auto'
                                ])
                            ->addLink(
                                $controller->url_for('resources/global_locks/delete/' . $lock->id),
                                _('Sperrung löschen'),
                                Icon::create('trash'),
                                [
                                    'data-dialog' => 'size=auto'
                                ]
                            )
                            ->render();
                        ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <?= MessageBox::info(
        _('Es sind keine gegenwärtigen und zukünftigen Sperren der Raumverwaltung hinterlegt.')
    ) ?>
<? endif ?>
