<?php
// in den Controller
$room_request_filter = function ($date) {
    return $date->room_request
        && !$date->room_request->isNew()
        && $date->room_request->closed < 2;
};
?>

<section class="contentbox timesrooms">
    <header>
        <h1>
            <?= _('Unregelmäßige Termine / Blocktermine') ?>
        </h1>
    <? if(!$locked) : ?>
        <nav>
            <? $actionMenu = ActionMenu::get()->setContext(_('Unregelmäßige Termine/Blocktermine')) ?>
            <? $actionMenu->addLink(
                $controller->url_for('course/timesrooms/createSingleDate/' . $course->id, $linkAttributes),
                _('Einzeltermin hinzufügen'),
                Icon::create('date', Icon::ROLE_CLICKABLE, ['title' => _('Einzeltermin hinzufügen')]),
                ['data-dialog' => 'size=600']
            ) ?>

            <? $actionMenu->addLink(
                $controller->url_for('course/block_appointments/index/' . $course->id, $linkAttributes),
                _('Blocktermin hinzufügen'),
                Icon::create('date-block', Icon::ROLE_CLICKABLE, ['title' => _('Blocktermin hinzufügen')]),
                ['data-dialog' => 'size=600']
            ) ?>
            <?= $actionMenu->render() ?>
        </nav>
    <? endif ?>
    </header>
<? if (!empty($single_dates)): ?>
    <form class="collapsable" action="<?= $controller->url_for('course/timesrooms/stack', $linkAttributes) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?> method="post">
        <?= CSRFProtection::tokenTag() ?>
    <? foreach ($single_dates as $semester_id => $termine) : ?>
        <article id="singledate-<?= $semester_id ?>" class="<?= count($single_dates) === 1 ? 'open' :  ContentBoxHelper::classes('singledate-' . $semester_id) ?>">
            <header>
                <h1>
                <? if (!$locked): ?>
                    <input type="checkbox" class="date-proxy"
                           data-proxyfor="#singledate-<?= $semester_id ?> .ids-irregular">
                <? endif ?>
                    <a href="<?= ContentBoxHelper::href('singledate-' . $semester_id) ?>">
                        <? if ($semester_id !== 'none'): ?>
                            <?= htmlReady(Semester::find($semester_id)->name) ?>
                        <? else: ?>
                            <?= _('Ohne Semester') ?>
                        <? endif ?>
                    </a>
                </h1>
                <section>
                    <span>
                        <?= sprintf(ngettext('%u Termin', '%u Termine', count($termine)), count($termine)) ?>
                    </span>
                <? if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS): ?>
                    <span>| <strong><?= _('Einzel-Raumanfrage') ?></strong>:
                    <? if ($single_date_room_request_c > 0): ?>
                        <?= sprintf(_('%u noch offen'), $single_date_room_request_c) ?>
                    <? else: ?>
                        <?= _('keine offen') ?>
                    <? endif ?>
                    </span>
                <? endif ?>
                </section>
            </header>
            <section>
                <table class="default">
                    <colgroup>
                    <? if (!$locked) :?>
                        <col style="width: 30px">
                    <? endif ?>
                        <col style="width: 30%">
                        <col>
                        <col>
                        <col style="width: 20%">
                        <col style="width: 50px">
                    </colgroup>

                    <tbody>
                    <? foreach ($termine as $termin): ?>
                        <?= $this->render_partial(
                            'course/timesrooms/_cycleRow.php',
                            [
                                'termin'    => $termin,
                                'class_ids' => 'ids-irregular',
                            ]
                        ) ?>
                    <? endforeach ?>
                    </tbody>
                </table>
            </section>
        </article>
    <? endforeach ?>
    <? if(!$locked) : ?>
        <table class="default nohover">
            <colgroup>
                <col style="width: 30px">
                <col style="width: 30%">
                <col>
                <col>
                <col style="width: 20%">
                <col style="width: 50px">
            </colgroup>

            <tfoot>
                <tr>
                    <td colspan="2">
                        <label class="horizontal">
                            <input type="checkbox" data-proxyfor=".date-proxy"
                                   data-activates=".actionForAllIrregular">
                            <?= _('Alle auswählen') ?>
                        </label>
                    </td>
                    <td colspan="5" class="actions">
                        <select name="method" class="datesBulkActions actionForAllIrregular">
                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                        </select>
                        <?= Studip\Button::create(
                            _('Ausführen'),
                            'run',
                            [
                                'class' => 'actionForAllIrregular',
                                'data-dialog' => 'size=big',
                            ]
                        ) ?>
                    </td>
                </tr>
            </tfoot>
            <? endif ?>
        </table>
    </form>
<? else: ?>
    <section>
        <p class="text-center">
            <strong>
                <?= _('Keine unregelmäßigen Termine vorhanden') ?>
            </strong>
        </p>
    </section>
<? endif ?>
</section>
