<?php
/**
 * @var QuestionnaireQuestion $vote
 * @var QuestionnaireAnswer[] $answers
 * @var $filtered string
 */
$options = $vote->questiondata['options'];
$numTaskAnswers = count($vote->answers);

$results = array_fill(0, $numTaskAnswers, 0);
$results_users = array_fill(0, $numTaskAnswers, []);

if ($numTaskAnswers > 0) {
    foreach ($answers as $answer) {
        if ($vote->questiondata['multiplechoice']) {
            if (is_array($answer['answerdata']['answers']) || $answer['answerdata']['answers'] instanceof Traversable) {
                foreach ($answer['answerdata']['answers'] as $a) {
                    $results[(int)$a]++;
                    $results_users[(int)$a][] = $answer['user_id'];
                }
            }
        } else {
            if (isset($answer['answerdata']['answers'])) {
                if (!isset($results[(int) $answer['answerdata']['answers']])) {
                    $results[(int) $answer['answerdata']['answers']] = 0;
                }
                $results[(int) $answer['answerdata']['answers']]++;

                if (!isset($results_users[(int) $answer['answerdata']['answers']])) {
                    $results_users[(int) $answer['answerdata']['answers']] = [];
                }
                $results[(int) $answer['answerdata']['answers']]++;
                $results_users[(int) $answer['answerdata']['answers']][] = $answer['user_id'];
            }
        }
    }
}

$ordered_results = $results;
arsort($ordered_results);
$ordered_answer_options = [];
$ordered_users = [];
foreach ($ordered_results as $index => $value) {
    if ($value > 0) {
        $ordered_answer_options[] = strip_tags(formatReady($options[$index]));
    } else {
        unset($ordered_results[$index]);
    }
}
rsort($ordered_results);
?>

<div class="description_container">
    <div class="icon_container">
        <?= Icon::create('vote', Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <div class="description">
        <?= formatReady($vote->questiondata['description']) ?>
    </div>
</div>

<? if (count($vote->answers) > 0 && $numTaskAnswers > 0) : ?>
    <div style="max-height: none; opacity: 1;"
         id="questionnaire_<?= $vote->getId() ?>_chart"
         class="ct-chart"></div>

    <script>
         STUDIP.Questionnaire.initVoteEvaluation(
             '#questionnaire_<?= $vote->getId() ?>_chart',
             <?= json_encode(
                 [
                     "labels" => $ordered_answer_options,
                     "series" => [$ordered_results],
                 ]
             ) ?>,
             <?= json_encode(Request::isAjax()) ?>,
             <?= json_encode($vote->questiondata['type'] === 'multiple') ?>
         );
    </script>
<? endif ?>

<table class="default nohover">
    <tbody>
        <? $countAnswers = $vote->questionnaire->countAnswers() ?>
        <? foreach ($options as $key => $answer) : ?>
        <tr>
            <? $percentage = ($countAnswers && isset($results[$key])) ? round((int) $results[$key] / $countAnswers * 100) : 0 ?>

            <td style="text-align: right; background-size: <?= $percentage ?>% 100%; background-position: right center; background-image: url('<?= Assets::image_path("vote_lightgrey.png") ?>'); background-repeat: no-repeat;" width="50%">
                <strong><?= formatReady($answer) ?></strong>
            </td>

            <td style="white-space: nowrap;">
                <? if ($filtered !== null && $filtered == $key) : ?>
                    <a href=""
                       title="<?= _('Zeige wieder alle Ergebnisse ohne Filterung an.') ?>"
                       onclick="STUDIP.Questionnaire.removeFilter('<?= htmlReady($vote['questionnaire_id']) ?>'); return false;">
                        <?= Icon::create('filter2', Icon::ROLE_CLICKABLE)->asImg(16, ['class' => 'text-bottom']) ?>
                        (<?= $percentage ?>% | <?= (int) $results[$key] ?>/<?= $countAnswers ?>)
                    </a>
                <? else : ?>
                    <a href=""
                       onclick="STUDIP.Questionnaire.addFilter('<?= htmlReady($vote['questionnaire_id']) ?>', '<?= htmlReady($vote->getId()) ?>', '<?= $key ?>'); return false;"
                       title="<?= _('Zeige nur Ergebnisse von Personen an, die diese Option gewählt haben.') ?>">
                        (<?= $percentage ?>% | <?= (int) ($results[$key] ?? 0) ?>/<?= $countAnswers ?>)
                    </a>
                <? endif ?>
            </td>

            <td width="50%">
                <? if (empty($vote->questionnaire['anonymous']) && !empty($results[$key])) : ?>

                    <? $users = SimpleCollection::createFromArray(
                        User::findMany($results_users[$key])); ?>

                    <? foreach ($results_users[$key] as $index => $user_id) : ?>

                        <? $user = $users->findOneBy('user_id', $user_id); ?>

                        <? if ($user) : ?>
                            <a href="<?= URLHelper::getLink(
                                     'dispatch.php/profile',
                                     ['username' => $user->username]
                                     ) ?>">
                                <?= Avatar::getAvatar($user_id, $user->username)->getImageTag(
                                    Avatar::SMALL,
                                    ['title' => $user->getFullname('no_title')]
                                ) ?>
                                <? if (count($results_users[$key]) < 4) : ?>
                                    <?= htmlReady($user->getFullname('no_title')) ?>
                                <? endif ?>
                            </a>
                        <? endif ?>
                    <? endforeach ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
