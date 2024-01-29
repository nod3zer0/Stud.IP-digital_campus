*Time:* <?= date('d.m.Y H:i', $date->begin) ?> - <?= date('d.m.Y H:i', $date->end) ?>

*Title:* <?= $date->title ?>

<?= $date->description ?? '' ?>

--

<? if ($date->category) : ?>
*Category:* <?= $date->category ?>
<? endif ?>

*Access:* <?= $date->getAccessAsString() ?>

<? if ($date->repetition_type) : ?>
*Repetition:* <?= $date->getRepetitionAsString() ?>
<? endif ?>

<? if (Config::get()->CALENDAR_GROUP_ENABLE && count($date->calendars) > 1) : ?>
*Participants:*
<? foreach($date->getParticipantsAsStringArray($receiver->user_id) as $participant_string) : ?>
- <?= $participant_string ?>
<? endforeach ?>
<? endif ?>
