<form class="default" action="<?= $controller->link_for('admin/tree/do_batch_assign') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Studienbereichszuordnungen der ausgewählten Veranstaltungen bearbeiten') ?></legend>
        <div data-studip-tree>
            <studip-tree start-id="StudipStudyArea_root" :with-info="false" :open-levels="1"
                         :assignable="true"></studip-tree>
        </div>
    </fieldset>
    <fieldset>
        <legend><?= _('Diese Veranstaltungen werden zugewiesen') ?></legend>
        <table class="default selected-courses">
            <colgroup>
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Name') ?></th>
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
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    </fieldset>
    <? if ($return) : ?>
        <input type="hidden" name="return" value="<?= htmlReady($return) ?>">
    <? endif ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
