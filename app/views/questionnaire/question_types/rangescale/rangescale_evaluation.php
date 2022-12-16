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
        <?= Icon::create('rangescale', Icon::ROLE_INFO)->asImg(20) ?>
    </div>
    <div class="description">
        <?= formatReady($vote->questiondata['description']) ?>
    </div>
</div>

<table class="default nohover">
    <thead>
        <tr>
            <th><?= _('Aussage') ?></th>
            <? for ($i = $vote->questiondata['minimum'] ?? 1; $i <= $vote->questiondata['maximum']; $i++) : ?>
                <th><?= htmlReady($i) ?></th>
            <? endfor ?>
        </tr>
    </thead>
    <tbody>
        <? $countAnswers = $vote->questionnaire->countAnswers() ?>
        <? foreach ($vote->questiondata['statements'] as $key => $statement) : ?>
        <tr>
            <td>
                <strong><?= formatReady($statement) ?></strong>
            </td>

            <? foreach($options as $option) : ?>
                <?
                $hits = 0;
                $names = [];
                foreach ($answers as $answer) {
                    if ($answer['answerdata']['answers'][$key] == $option) {
                        $hits++;
                        if ($answer['user_id'] && $answer['user_id'][0] !== 'n') {
                            $names[] = $answer->user->getFullName('full');
                        }
                    }
                }
                $color = 'hsl(0 0% '.round(70 + (30 * (1 - ($hits / $countAnswers ?? 1)) )).'%)';
                ?>
                <td style="background-color: <?= $color ?>;" style="white-space: nowrap;"<?= count($names) > 0 ? 'title="'.htmlReady(implode(', ', $names)).'"' : ''?>>
                    <? if ($filtered !== null && $filtered == ($key.'_'.$option)) : ?>
                        <a href=""
                           onclick="STUDIP.Questionnaire.removeFilter('<?= htmlReady($vote['questionnaire_id']) ?>'); return false;"
                           title="<?= _('Zeige wieder alle Ergebnisse ohne Filterung an.') ?>">
                            <?= Icon::create('filter2', Icon::ROLE_CLICKABLE)->asImg(16, ['class' => 'text-bottom']) ?>
                            <?= round(100 * $hits / $countAnswers) ?>%
                        </a>
                    <? else : ?>
                        <a href=""
                           onclick="STUDIP.Questionnaire.addFilter('<?= htmlReady($vote['questionnaire_id']) ?>', '<?= htmlReady($vote->getId()) ?>', '<?= $key.'_'.$option ?>'); return false;"
                           title="<?= _('Zeige nur Ergebnisse von Personen an, die diese Option gewählt haben.') ?>">
                            <?= round(100 * $hits / $countAnswers) ?>%
                        </a>
                    <? endif ?>
                </td>
            <? endforeach ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
