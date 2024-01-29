<? if ($date_assignment->participation === 'ACCEPTED') : ?>
<?= $date_assignment->user->getFullName() ?> has accepted your date.
<? elseif ($date_assignment->participation === 'DECLINED') : ?>
<?= $date_assignment->user->getFullName() ?> has declined your date.
<? endif ?>

<?= $this->render_partial(__DIR__ . '/_date_information', [
    'date' => $date_assignment->calendar_date,
    'receiver' => $date_assignment->calendar_date->author,
]) ?>

--

Go to date: <?= URLHelper::getURL('dispatch.php/calendar/date/index/' . $date_assignment->calendar_date->id) ?>
