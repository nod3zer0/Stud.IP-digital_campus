<?php
/**
 * @var QuestionnaireQuestion $vote
 * @var QuestionnaireAnswer[] $answers
 * @var $filtered string
 */
$options = range($vote->questiondata['minimum'], $vote->questiondata['maximum']);
?>

<div class="description_container">
    <div class="icon_container">
        <?= Icon::create('question-rangescale', Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <div class="description">
        <?= formatReady($vote->questiondata['description']) ?>
    </div>
</div>

<table class="default nohover">
    <colgroup>
        <col>
        <? foreach ($options as $option) : ?>
            <col style="width: 70px">
        <? endforeach ?>
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Aussage') ?></th>
            <? for ($i = $vote->questiondata['minimum'] ?? 1; $i <= $vote->questiondata['maximum']; $i++) : ?>
                <th class="rangescale_center"><?= htmlReady($i) ?></th>
            <? endfor ?>
        </tr>
    </thead>
    <tbody>
        <? $countAnswers = $vote->questionnaire->countAnswers() ?>
        <? foreach ($vote->questiondata['statements'] as $key => $statement) : ?>
        <tr>
            <td>
                <strong><?= htmlReady($statement) ?></strong>
            </td>

            <?
            $average = 0;
            if (count($answers) > 0) {
                foreach ($answers as $answer) {
                    $average += $answer['answerdata']['answers'][$key];
                }
                $average /= count($answers);
                $average = round($average, 2);
            }
            ?>

            <? foreach($options as $option_index => $option) : ?>
                <?
                $hits = 0;
                $names = [];
                foreach ($answers as $answer) {
                    if ($answer['answerdata']['answers'][$key] == $option) {
                        $hits++;
                        if ($answer['user_id'] && $answer['user_id'][0] !== 'q' && $answer['user_id'][0] !== 'n') {
                            $names[] = $answer->user->getFullName('full');
                        }
                    }
                }
                ?>
                <td style="white-space: nowrap;"<?= count($names) > 0 ? 'title="'.htmlReady(implode(', ', $names)).'"' : ''?>>
                    <? if ($option_index === 0 && count($answers) > 0) : ?>
                        <div class="average" style="margin-left: <?= (count($options) * 80) * $average / $vote->questiondata['maximum'] - $vote->questiondata['minimum'] * 80 + 34 ?>px;">
                            Ø<?= htmlReady(str_replace('.', ',', (string) round($average, 2))) ?>
                        </div>
                    <? endif ?>
                    <?
                    $bubble_width = 70;
                    $font_size = 2.5;
                    if ($countAnswers === 1) {
                        $bubble_width /= 3;
                        $font_size /= 3;
                    } elseif ($countAnswers === 2) {
                        $bubble_width /= 2;
                        $font_size /= 2;
                    } elseif ($countAnswers === 3) {
                        $bubble_width /= 1.5;
                        $font_size /= 1.5;
                    }
                    ?>
                    <? if (count($answers) > 0) : ?>
                        <div class="centerline"></div>
                    <? endif ?>
                    <? if ($countAnswers) : ?>
                        <? $bubble_width = $hits > 0 ? ($bubble_width - 14) * $hits / $countAnswers + 14 : 0 ?>
                        <? $font_size = $hits > 0 ? ($font_size - 0.5) * $hits / $countAnswers + 0.5 : 0 ?>
                        <? if ($filtered !== null && $filtered == ($key.'_'.$option)) : ?>
                            <a href=""
                               class="questionnaire-evaluation-circle-container"
                               onclick="STUDIP.Questionnaire.removeFilter('<?= htmlReady($vote['questionnaire_id']) ?>'); return false;"
                               title="<?= _('Zeige wieder alle Ergebnisse ohne Filterung an.') ?>">
                                <div class="questionnaire-evaluation-circle">
                                    <div class="value" style="font-size: <?= $font_size ?>em; max-width: <?= $bubble_width ?>px; max-height: <?= $bubble_width ?>px;">
                                        <?= htmlReady($hits) ?>
                                    </div>
                                </div>
                                <?= Icon::create('filter2', Icon::ROLE_CLICKABLE)->asImg(16, ['class' => 'text-bottom']) ?>
                                <?= round(100 * $hits / $countAnswers) ?>%
                            </a>
                        <? else : ?>
                            <a href=""
                               class="questionnaire-evaluation-circle-container"
                               onclick="STUDIP.Questionnaire.addFilter('<?= htmlReady($vote['questionnaire_id']) ?>', '<?= htmlReady($vote->getId()) ?>', '<?= $key.'_'.$option ?>'); return false;"
                               title="<?= _('Zeige nur Ergebnisse von Personen an, die diese Option gewählt haben.') ?>">
                                <div class="questionnaire-evaluation-circle">
                                    <div class="value" style="font-size: <?= $font_size ?>em; max-width: <?= $bubble_width ?>px; max-height: <?= $bubble_width ?>px;">
                                        <?= htmlReady($hits) ?>
                                    </div>
                                </div>
                                <?= round(100 * $hits / $countAnswers) ?>%
                            </a>
                        <? endif ?>
                    <? else : ?>
                        0%
                    <? endif ?>
                </td>
            <? endforeach ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
