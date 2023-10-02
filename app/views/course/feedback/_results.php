<? $entries_count = count($feedback->entries); ?>
<section class="feedback-results">
    <h2><?= _('Ergebnisse') ?></h2>
    <? if ($entries_count == 0 ) {
        print(_('Bisher wurde kein Feedback gegeben.'));
    } ?>
    <? if ($feedback->mode == FeedbackElement::MODE_NO_RATING) {
        printf(_('Insgesamt wurde %s mal Feedback gegeben.'), $entries_count);
    } ?>
    <? if ($entries_count >= 1 && $feedback->mode != FeedbackElement::MODE_NO_RATING) : ?>
        <?
            $rating_scale = $feedback->mode == FeedbackElement::MODE_5STAR_RATING ? 5 : 10;
        ?>
        <div class="ratings">
            <table class="default sortable-table feedback" data-sortlist="[[1, 1]]">
            <colgroup>
                <col>
                <col width="15%">
                <col width="15%">
            </colgroup>
                <thead>
                    <tr>
                        <th data-sort="text"><?=_('Prozent')?></th>
                        <th data-sort="htmldata"><?=_('Bewertung')?></th>
                        <th data-sort="text"><?=_('Anzahl')?></th>
                    </tr>
                </thead>
                <tbody>
                <? for ($i = 1; $i < $rating_scale+1; $i++) : ?>
                    <tr>
                        <td data-sort-value="<?= $feedback->getPercentageOfRating($i) ?>">
                        <div class="percentage">
                            <div class="percentage-bar" style="width: <?= $feedback->getPercentageOfRating($i) . '%' ?>;">
                            <?= $feedback->getPercentageOfRating($i) . '%' ?>
                            </div>
                        </div>
                        </td>
                        <td data-sort-value="<?= $i ?>">
                        <?= $i ?>
                        <?=Icon::create('star') ?>
                        </td>
                        <td>
                            <?= $feedback->getCountOfRating($i) ?>
                        </td>
                    </tr>
                <? endfor; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <?= _('Durchschnitt: ') ?>
                            <?= $feedback->getMeanOfRating(); ?>
                        </td>
                        <td>
                            <strong><?= $entries_count ?></strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <? endif; ?>
</section>
