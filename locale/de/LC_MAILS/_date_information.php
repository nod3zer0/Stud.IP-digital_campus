*Zeiten:* <?= date('d.m.Y H:i', $date->begin) ?> - <?= date('d.m.Y H:i', $date->end) ?>

*Titel:* <?= $date->title ?>

<?= $date->description ?? '' ?>

--

<? if ($date->category) : ?>
*Kategorie:* <?= $date->getCategoryAsString() ?>
<? endif ?>

*Zugriff:* <?= $date->getAccessAsString() ?>

<? if ($date->repetition_type) : ?>
*Wiederholung:* <?= $date->getRepetitionAsString() ?>
<? endif ?>

<? if (Config::get()->CALENDAR_GROUP_ENABLE && count($date->calendars) > 1) : ?>
*Teilnehmende:*
<? foreach($date->getParticipantsAsStringArray($receiver->user_id) as $participant_string) : ?>
- <?= $participant_string ?>
<? endforeach ?>
<? endif ?>

<? if ($receiver_date_assignment) : ?>
**Ihre Teilnahme:** <?= $receiver_date_assignment->getParticipationAsString() ?>
<? endif ?>
