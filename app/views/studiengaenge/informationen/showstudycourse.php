<td colspan="3">
    <table class="default">
        <colgroup>
            <col style="width: 70%">
            <col style="width: 29%">
            <col style="width: 1%">
        </colgroup>
        <? foreach ($studycourses as $key => $course) : ?>
            <? if (($count = UserStudyCourse::countBySql('fach_id = :fach_id AND abschluss_id = :abschluss_id',
                    [':fach_id' => $course->fach_id, ':abschluss_id' => $degree->abschluss_id])) > 0) : ?>
                <tr>
                    <td><?= htmlReady($course->name) ?></td>
                    <td>
                        <?= $count ?>
                    </td>
                    <td class="actions">
                        <?= $action = ActionMenu::get()->setContext($course->name)
                            ->addLink($controller->messagehelperURL(
                                ['fach_id' => $course->fach_id, 'abschluss_id' => $degree->abschluss_id]),
                                _('Nachricht an Studierende schreiben'),
                                Icon::create(
                                    'mail',
                                    Icon::ROLE_CLICKABLE,
                                    ['title' => htmlReady(sprintf(
                                        _('Nachricht an alle Studierende mit dem Studiengang %s mit dem Abschluss %s'),
                                        $course->name, $degree->name))
                                    ]),
                                ['data-dialog' => ''])->render()
                        ?>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>
    </table>
</td>
