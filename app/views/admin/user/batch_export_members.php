<form class="default" action="<?= $controller->link_for('admin/user/do_batch_export') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Die Daten der Teilnehmenden folgender Veranstaltungen werden exportiert') ?></legend>
        <table class="default selected-courses">
            <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Anzahl Lehrende') ?></th>
                <th><?= _('Anzahl Tutor/-innen') ?></th>
                <th><?= _('Anzahl Studierende') ?></th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($courses as $course) : ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getLink('dispatch.php/course/overview', ['cid' => $course->id])?>"
                           title="<?= sprintf(_('Zur Veranstaltung %s'), htmlReady($course->getFullname())) ?>"
                           target="_blank">
                            <?= htmlReady($course->getFullname('number-name-semester')) ?>
                        </a>
                        <input type="hidden" name="courses[]" value="<?= htmlReady($course->id) ?>">
                    </td>
                    <td><?= count($course->getMembersWithStatus('dozent')) ?></td>
                    <td><?= count($course->getMembersWithStatus('tutor')) ?></td>
                    <td><?= count($course->getMembersWithStatus('autor')) ?></td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
    </fieldset>
    <? if ($return) : ?>
        <input type="hidden" name="return" value="<?= htmlReady($return) ?>">
    <? endif ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Export als Excel-Datei'), 'xlsx') ?>
        <?= Studip\Button::createAccept(_('Export als CSV-Datei'), 'csv') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>

