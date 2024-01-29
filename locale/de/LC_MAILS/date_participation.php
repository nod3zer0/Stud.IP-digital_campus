<? if ($date_assignment->participation === 'ACCEPTED') : ?>
<?= $date_assignment->user->getFullName() ?> hat Ihren Termin angenommen.
<? elseif ($date_assignment->participation === 'DECLINED') : ?>
<?= $date_assignment->user->getFullName() ?> hat Ihren Termin abgelehnt.
<? endif ?>

<?= $this->render_partial(__DIR__ . '/_date_information', [
    'date' => $date_assignment->calendar_date,
    'receiver' => $date_assignment->calendar_date->author,
]) ?>

--

Direkt zum Termin: <?= URLHelper::getURL('dispatch.php/calendar/date/index/' . $date_assignment->calendar_date->id) ?>
