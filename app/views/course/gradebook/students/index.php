<?php
/**
 * @var Course_Gradebook_StudentsController $controller
 * @var float $total
 * @var string[] $categories
 * @var float[] $subtotals
 * @var array<string, Grading\Definition> $groupedDefinitions
 */
?>
<article class="gradebook-student">
    <header>
        <h1><?= _("Gesamt") ?></h1>
        <? if ($total > 0) : ?>
            <?= $this->render_partial("course/gradebook/_progress", ['value' => $controller->formatAsPercent($total)])?>
        <? endif ?>
        <? if ($passed) : ?>
            <div><?= _('bestanden')?></div>
        <? endif ?>
    </header>

    <? foreach ($categories as $category) { ?>
        <section class="gradebook-student-category">
            <header>
                <h2><?= $controller->formatCategory($category) ?></h2>
            </header>
            <? if ($subtotals[$category] > 0) : ?>
                <?= $this->render_partial("course/gradebook/_progress", ['value' => $controller->formatAsPercent($subtotals[$category])])?>
            <? endif ?>
            <? if ($subpassed[$category]) : ?>
                <div><?= _('bestanden')?></div>
            <? endif ?>
            <table class="default">
                <colgroup>
                    <col width="200px" />
                    <col width="150px" />
                    <col width="100px" />
                    <col />
                </colgroup>

                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th><?= _("Tool") ?></th>
                        <th><?= _("Gewichtung") ?></th>
                        <th><?= _("Feedback") ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?
                    foreach ($groupedDefinitions[$category] as $definition) {
                        $instance = $groupedInstances[$definition->id] ?? null;
                        $grade = $controller->formatAsPercent($instance ? $instance->rawgrade : 0);
                        $feedback = $instance ? $instance->feedback : '';
                        $passed = $instance ? $instance->passed : 0;
                    ?>
                        <tr>
                            <td>
                                <span class="gradebook-definition-name"><?= htmlReady($definition->name) ?></span>
                                <? if ($grade > 0) : ?>
                                    <?= $this->render_partial("course/gradebook/_progress", ['value' => (int) $grade])?>
                                <? endif ?>
                                <div><?= $passed ? _('bestanden') : '' ?></div>
                            </td>
                            <td>
                                <?= htmlReady($definition->tool) ?>
                            </td>
                            <td>
                                <?= $controller->formatAsPercent($controller->getNormalizedWeight($definition)) ?>%
                            </td>
                            <td>
                                <?= htmlReady($feedback) ?>
                            </td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </section>
    <? } ?>
</article>
