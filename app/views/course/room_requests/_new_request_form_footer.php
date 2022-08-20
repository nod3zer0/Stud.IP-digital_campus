<footer data-dialog-button>
    <? if ($step >= 1) : ?>
        <?= \Studip\LinkButton::create(
            _('Zurück'),
            $controller->link_for('course/room_requests/new_request/' . $request_id),
            [
                'data-dialog' => 'size=big'
            ]

        ) ?>

        <?= \Studip\Button::create(_('Raum auswählen und weiter'), 'request_second_step') ?>

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
