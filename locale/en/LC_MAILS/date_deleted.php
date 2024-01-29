<?= $date->editor->getFullName() ?> has deleted a date in the calendar.

<?= $this->render_partial(__DIR__ . '/_date_information', [
    'date' => $date,
    'receiver' => $receiver,
]) ?>
