<table class="default sortable-table gradebook-lecturer-overview" data-sortlist="[[0, 0]]">

    <caption><?= _('Erbrachte Leistungen') ?></caption>

    <colgroup>
        <col class="gradebook-column-name">
        <col class="gradebook-column-total">
    </colgroup>

    <? foreach ($categories as $i => $category) { ?>
        <colgroup class="gradebook-column-category" span="<?= count($groupedDefinitions[$category]) ?>"/>
    <? } ?>

    <thead>
        <tr class="tablesorter-ignoreRow">
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <? foreach ($categories as $category) { ?>
                <th colspan="<?= count($groupedDefinitions[$category]) ?>"><?= $controller->formatCategory($category) ?></th>
            <? } ?>
        </tr>

        <tr class="sortable">
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="text"><?= _('Gesamtsumme') ?></th>

            <? foreach ($categories as $category) { ?>
                <? foreach ($groupedDefinitions[$category] as $definition) { ?>
                    <th data-sort="text" class="gradebook-lecturer-overview-definition">
                        <?= htmlReady($definition->name) ?>
                        <span class="gradebook-definition-weight">(<?= $controller->formatAsPercent($controller->getNormalizedWeight($definition)) ?> %)</span>
                    </th>
                <? } ?>
            <? } ?>
        </tr>

    </thead>

    <tbody>
        <? if (count($students)) { ?>
            <? foreach ($students as $student) { ?>
                <tr>
                    <td class="gradebook-student-name" data-sort-value="<?= $studentName = htmlReady($student->nachname . ', ' . $student->vorname) ?>">
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $student->username]) ?>">
                            <?= $studentName ?>
                        </a>
                    </td>
                    <? $totalSum = $totalSums[$student->user_id] ?? 0 ?>
                    <? $totalPassed = $totalPassed[$student->user_id] ?? 0 ?>
                    <td data-sort-value="<?= $totalSum + $totalPassed?>">
                        <?= $totalSum > 0 ? $controller->formatAsPercent($totalSum) . ' %' : ''?>
                        <?= $totalPassed ? _('bestanden') : ''?>
                    </td>

                    <? foreach ($categories as $category) { ?>
                        <? foreach ($groupedDefinitions[$category] as $definition) { ?>
                            <? $instance = $controller->getInstanceForUser($definition, $student) ?>
                            <? $rawgrade = $instance ? $instance->rawgrade : 0 ?>
                            <? $passed = $instance ? $instance->passed : 0 ?>
                            <td data-sort-value="<?= $rawgrade + $passed ?>">
                                <?= $rawgrade > 0 ? $controller->formatAsPercent($rawgrade) . ' %' : ''?>
                                <?= $passed ? _('bestanden') : ''?>
                            </td>
                        <? } ?>
                    <? } ?>
                </tr>
            <? } ?>
        <? } ?>
    </tbody>
</table>

<? if (!count($categories)) { ?>
    <?= \MessageBox::info(_('Es wurden noch keine Leistungen definiert.')) ?>
<? } ?>

<? if (!count($students)) { ?>
    <?= \MessageBox::info(_('Es gibt noch keine Teilnehmer.')) ?>
<? } ?>
