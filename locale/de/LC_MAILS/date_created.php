<?= $date->editor->getFullName() ?> hat einen Termin im Kalender eingetragen.

<?= $this->render_partial(__DIR__ . '/_date_information', [
    'date' => $date,
    'receiver' => $receiver,
]) ?>

--

Direkt zum Termin: <?= URLHelper::getURL('dispatch.php/calendar/date/index/' . $date->id) ?>
