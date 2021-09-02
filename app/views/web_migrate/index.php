<? if (count($migrations) === 0): ?>
    <?= MessageBox::info(_('Ihr System befindet sich auf dem aktuellen Stand.'))->hideClose() ?>
<? else: ?>
<form method="post" action="<?= $controller->link_for('migrate') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if (isset($target)): ?>
        <input type="hidden" name="target" value="<?= htmlReady($target) ?>">
    <? endif ?>
    <input type="hidden" name="branch" value="<?= htmlReady($branch) ?>">

    <table class="default" id="migration-list">
        <caption>
            <?= _('Die hier aufgeführten Anpassungen werden beim Klick auf "Starten" ausgeführt:') ?>
        </caption>
        <colgroup>
            <col style="width: 24px">
            <col style="width: 120px">
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th></th>
                <th><?= _('Nr.') ?></th>
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($migrations as $number => $migration): ?>
            <? $version = $migrator->migrationBranchAndVersion($number) ?>
            <tr>
                <td>
                    <? if ($version[0] === $branch): ?>
                        <input type="radio" name="target" value="<?= $version[1] + $offset ?>">
                    <? endif ?>
                </td>
                <td>
                    <?= htmlReady($number) ?>
                </td>
                <td>
                    <?= htmlReady(get_class($migration)) ?>
                </td>
                <td>
                <? if ($migration->description()): ?>
                    <?= htmlReady($migration->description()) ?>
                <? else: ?>
                    <em><?= _('keine Beschreibung vorhanden') ?></em>
                <? endif ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">
                <? if ($lock->isLocked($lock_data)):
                    $user = User::find($lock_data['user_id']);
                ?>
                    <?= MessageBox::info(sprintf(
                        _('Die Migration wurde %s von %s bereits angestossen und läuft noch.'),
                        reltime($lock_data['timestamp']),
                        htmlReady($user ? $user->getFullName() : _('unbekannt'))
                    ), [
                        sprintf(
                            _('Sollte während der Migration ein Fehler aufgetreten sein, so können Sie '
                            . 'diese Sperre durch den unten stehenden Link oder das Löschen der Datei '
                            . '<em>%s</em> auflösen.'),
                            $lock->getFilename()
                        )
                    ]) ?>
                    <?= Studip\LinkButton::create(_('Sperre aufheben'), $controller->url_for('release', $target)) ?>
                <? else: ?>
                    <?= Studip\Button::createAccept(_('Starten'), 'start')?>
                <? endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<? endif ?>
