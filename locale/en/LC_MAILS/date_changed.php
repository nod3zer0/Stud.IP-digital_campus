<?= $date->editor->getFullName() ?> has modified a date in the calendar.

<?= $this->render_partial(__DIR__ . '/_date_information', [
    'date' => $date,
    'receiver' => $receiver,
]) ?>

--

Go to date: <?= URLHelper::getURL('dispatch.php/calendar/date/index/' . $date->id) ?>
