    <footer data-dialog-button>
        <? if ($step >= 1) : ?>
            <?= \Studip\LinkButton::create(
                _('Zurück'),
                $controller->link_for('course/room_requests/new_request/' . $request_id),
                ['data-dialog' => 'size=big']
            ) ?>


        <? endif ?>

        <? if ($step == 1 && $_SESSION[$request_id]['search_by'] == 'roomname') : ?>
            <?= \Studip\Button::create(_('Weiter'), 'request_second_step') ?>
        <? elseif (($step == 1 && $_SESSION[$request_id]['search_by'] == 'category') || ($step == 2 && $_SESSION[$request_id]['search_by'] == 'roomname')) : ?>
            <?= \Studip\Button::create(_('Speichern'), 'save_request') ?>
        <? endif ?>

        <?= \Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->link_for('course/room_requests/index/' . $course_id),
            [
                'title' => _('Abbrechen')
            ]
        ) ?>
    </footer>
</form>
Step: <?= $step ?>
