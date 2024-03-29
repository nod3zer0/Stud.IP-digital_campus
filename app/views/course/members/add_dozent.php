<h1><?= sprintf(_('%s hinzufügen'), htmlReady($decoratedStatusGroups['dozent'])) ?></h1>

<form action="<?= $controller->url_for('course/members/set') ?>" method="post">
<?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= $studipticket ?>">
    <table class="default">
        <thead>
            <tr>
                <th colspan="2"><?= sprintf(_('%s suchen'), htmlReady($decoratedStatusGroups['dozent'])) ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?=
                        QuickSearch::get('new_dozent', $search)
                        ->withButton(
                            [
                                'reset_button_name' => 'reset_dozent',
                                'search_button_name' => 'search_dozent'
                            ]
                        )->render();
                    ?>
                    <input type="hidden" name="seminar_id" value="<?= $course_id ?>">
                </td>
                <td>
                    <?= Studip\Button::createAccept(
                        _('Eintragen'),
                        'add_dozent',
                        ['title' => sprintf(_("als %s eintragen"), $decoratedStatusGroups['dozent'])]
                    ) ?>
                    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/members/index')) ?>
                </td>
            </tr>
        </tbody>
    </table>
</form>
