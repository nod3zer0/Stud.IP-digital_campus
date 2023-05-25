    <br>
    <footer data-dialog-button>
        <? if ($step !== 3) : ?>
            <?= \Studip\LinkButton::create(
                _('Zurück auf Anfang'),
                $controller->url_for('course/room_requests/new_request/' . $request_id),
                ['data-dialog' => 'size=big']
            ) ?>
        <? else: ?>
                <?= \Studip\LinkButton::create(
                    _('Angaben bearbeiten'),
                    $controller->url_for('course/room_requests/request_find_available_properties/' . $request_id . '/1'),
                    ['data-dialog' => 'size=big']
                ) ?>

        <? endif ?>

        <? if ($step === 1 || $step === 2) : ?>
                <? if ($_SESSION[$request_id]['search_by'] !== 'category') : ?>
                    <? \Studip\Button::create(_('Raum auswählen'), 'select_room') ?>
            <? endif ?>
        <? endif ?>

        <? if (($step === 1 && $_SESSION[$request_id]['room_category_id'] !== '0')
            || $step === 2) : ?>
            <?= \Studip\Button::create(_('Weiter'), 'show_summary') ?>
        <? endif ?>

        <? if ($step === 3) : ?>
            <?= \Studip\Button::create(_('Raumanfrage speichern'), 'save_request') ?>
        <? endif ?>

        <?= \Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('course/room_requests/index/' . $course_id),
            [
                'title' => _('Abbrechen')
            ]
        ) ?>
    </footer>
</form>
