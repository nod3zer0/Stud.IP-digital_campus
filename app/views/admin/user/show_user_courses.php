<?php
/**
 * @var Admin_UserController $controller
 * @var User $user
 */
?>
<? if (!empty($sem_courses)) : ?>
    <form method="post" action="<?= $controller->delete_course_assignment($user) ?>" class="default collapsable"
          data-dialog="size=auto">
        <?= CSRFProtection::tokenTag() ?>
        <? foreach ($sem_courses as $sem_name => $courses) : ?>
            <fieldset>
                <legend>
                    <?= htmlReady($sem_name) ?>
                </legend>
                <table class="default ">
                    <colgroup>
                        <col style="width: 20px">
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" name="all" value="1"
                                       data-proxyfor="tbody#courses-<?= md5($sem_name) ?> td :checkbox">
                            </th>
                            <th><?= _('Veranstaltungsname') ?></th>
                            <th class="actions"><?= _('Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody id="courses-<?= md5($sem_name) ?>">
                        <? foreach ($courses as $course) : ?>
                            <tr>
                                <td><input type="checkbox" name="courses[]" value="<?= htmlReady($course->id) ?>"></td>
                                <td><?= htmlReady($course->getFullname()) ?></td>
                                <td class="actions">
                                    <?= Icon::create('trash')->asInput([
                                        'formaction'   => $controller->delete_course_assignment($user, ['course_id' => $course->id]),
                                        'data-confirm' => sprintf(
                                            _('Wollen Sie %s wirklich austragen?'),
                                            htmlReady($user->getFullName())
                                        ),
                                        'data-dialog'  => 'size=auto'
                                    ]) ?>
                                </td>
                            </tr>
                        <? endforeach ?>
                    </tbody>
                </table>
            </fieldset>
        <? endforeach ?>
        <footer data-dialog-button>
            <?= \Studip\Button::create(
                _('Austragen'),
                'delete_assignments',
                [
                    'data-confirm' => sprintf(
                        _('Wollen Sie %s wirklich austragen?'),
                        htmlReady($user->getFullName())
                    )
                ]
            ) ?>
        </footer>
    </form>
<? else : ?>
    <?= MessageBox::info(_('Es wurden keine Veranstaltungen gefunden.')) ?>
<? endif ?>
