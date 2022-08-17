<footer data-dialog-button>
    <? if ($step >= 1) : ?>
        <?= \Studip\LinkButton::create(
            _('Zurück'),
            $controller->link_for('course/room_requests/new_request/' . $request_id),
            [
                'step' => $step,
                'data-dialog' => 'size=big'
            ]

        ) ?>
        <?= \Studip\LinkButton::create(
            _('Raum auswählen und weiter'),
            $controller->link_for('course/room_requests/new_request/' . $request_id),
            [
                'step' => $step +1,
                'data-dialog' => 'size=auto'
            ]

        ) ?>
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
