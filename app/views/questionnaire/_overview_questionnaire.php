<tr id="questionnaire_<?= $questionnaire->id ?>">
    <? $countedAnswers = $questionnaire->countAnswers() ?>
    <td>
        <input type="checkbox" name="q[]" value="<?= htmlReady($questionnaire->id) ?>">
    </td>
    <td>
        <a href="<?= $controller->link_for(($questionnaire->isRunning() && $countedAnswers ? 'questionnaire/evaluate/' : 'questionnaire/edit/') . $questionnaire->id) ?>" data-dialog="size=big">
            <?= htmlReady($questionnaire['title']) ?>
        </a>
    </td>
    <td>
    <? if ($questionnaire['startdate']): ?>
        <?= date('d.m.Y H:i', $questionnaire['startdate']) ?>
    <? else: ?>
        <?= _('händisch') ?>
    <? endif; ?>
    </td>
    <td>
    <? if ($questionnaire['stopdate']): ?>
        <?= date('d.m.Y H:i', $questionnaire['stopdate']) ?>
    <? else: ?>
        <?= _('händisch') ?>
    <? endif; ?>
    </td>
    <td class="context">
    <? if (count($questionnaire->assignments) > 0) : ?>
        <ul class="clean">
        <? foreach ($questionnaire->assignments as $assignment) : ?>
            <li>
            <? if ($assignment['range_id'] === 'start') : ?>
                <?= _('Stud.IP Startseite')?>
            <? elseif ($assignment['range_id'] === 'public') : ?>
                <?= _('Öffentlich per Link')?>
            <? endif ?>

            <? if ($assignment['range_type'] === 'user') : ?>
                <?= _('Profilseite')?>
            <? elseif ($assignment['range_type'] === 'course') : ?>
                <? $course = Course::find($assignment['range_id']) ?>
                <?= htmlReady((Config::get()->IMPORTANT_SEMNUMBER ? $course->veranstaltungsnummer." " : "") . $course['name'] . ' ('.$course->semester_text.')') ?>
            <? elseif ($assignment['range_type'] === 'statusgruppe') : ?>
                <? $statusgruppe = Statusgruppen::find($assignment['range_id']) ?>
                <? if ($statusgruppe) : ?>
                    <?= $statusgruppe->course ? htmlReady($statusgruppe->course->name).":" : "" ?>
                    <?= $statusgruppe->institute ? htmlReady($statusgruppe->institute->name).":" : "" ?>
                    <?= htmlReady($statusgruppe->name) ?>
                <? endif ?>
            <? elseif ($assignment['range_type'] === 'institute') : ?>
                <?= htmlReady(Institute::find($assignment['range_id'])->name) ?>
            <? elseif ($assignment['range_type'] === 'plugin') : ?>
                <?= htmlReady(Institute::find($assignment['range_id'])->name) ?>
            <? else : ?>
                <?
                foreach (PluginManager::getInstance()->getPlugins("QuestionnaireAssignmentPlugin") as $plugin) {
                    $name = $plugin->getQuestionnaireAssignmentName($assignment);
                    if ($name) {
                        echo htmlReady($name);
                    }
                }
                ?>
            <? endif ?>
            </li>
        <? endforeach ?>
        </ul>
    <? else : ?>
        <?= _('Nirgendwo') ?>
    <? endif ?>
    </td>
    <td>
        <?= htmlReady($countedAnswers) ?>
    </td>
    <td class="actions">
    <? if ($questionnaire->isRunning() && $countedAnswers) : ?>
        <?= Icon::create('edit', 'inactive')->asImg(20, ['title' => _('Der Fragebogen wurde gestartet und kann nicht mehr bearbeitet werden.')]) ?>
    <? else : ?>
        <a href="<?= $controller->link_for('questionnaire/edit/' . $questionnaire->id) ?>"
           data-dialog="size=big"
           title="<?= _('Fragebogen bearbeiten') ?>">
            <?= Icon::create('edit', 'clickable')->asImg(20) ?>
        </a>
    <? endif ?>
        <a href="<?= $controller->link_for('questionnaire/context/' . $questionnaire->id) ?>"
           data-dialog="reload-on-close"
           title="<?= _('Zuweisungen bearbeiten') ?>">
            <?= Icon::create('group2', 'clickable')->asImg(20) ?>
        </a>

        <?
        $menu = ActionMenu::get()->setContext($questionnaire['title']);
        if ($questionnaire->isRunning()) {
            $menu->addLink(
                $controller->url_for('questionnaire/answer/' . $questionnaire->id),
                _('Ausfüllen'),
                Icon::create('evaluation', 'clickable'),
                ['data-dialog' => 1]
            );
            $menu->addLink(
                $controller->url_for('questionnaire/stop/' . $questionnaire->id, in_array($range_type, ['course', 'institute']) ? ['redirect' => 'questionnaire/courseoverview'] : []),
                _('Fragebogen beenden'),
                Icon::create('pause', 'clickable')
            );
        } else {
            $menu->addLink(
                $controller->url_for('questionnaire/edit/'  .$questionnaire->id),
                _('Fragebogen bearbeiten'),
                Icon::create('edit'),
                ['data-dialog' => 'size=big']
            );
            $menu->addLink(
                $controller->url_for('questionnaire/start/'  .$questionnaire->id, in_array($range_type, ['course', 'institute']) ? ['redirect' => 'questionnaire/courseoverview'] : []),
                _('Fragebogen starten'),
                Icon::create('play', 'clickable')
            );
        }
        $menu->addLink(
            $controller->url_for('questionnaire/evaluate/'  .$questionnaire->id),
            _('Auswertung'),
            Icon::create('stat', 'clickable'),
            ['data-dialog' => '']
        );
        $menu->addLink(
            $controller->url_for('questionnaire/copy/'  .$questionnaire->id),
            _('Kopieren'),
            Icon::create('clipboard', 'clickable'),
            ['data-dialog' => '']
        );
        $menu->addLink(
            $controller->url_for('questionnaire/export_file/'  .$questionnaire->id),
            _('Vorlage herunterladen'),
            Icon::create('export', 'clickable')
        );
        if ($questionnaire->countAnswers() > 0) {
            $menu->addButton(
                'reset_answers',
                _('Antworten löschen'),
                Icon::create('refresh', 'clickable'),
                [
                    'data-confirm' => _('Sollen die Antworten wirklich gelöscht werden?'),
                    'formaction' => $controller->url_for('questionnaire/reset/' . $questionnaire->id)
                ]
            );
        }
        $menu->addLink(
            $controller->url_for('questionnaire/export/'  .$questionnaire->id),
            _('Export als CSV'),
            Icon::create('file-excel', 'clickable')
        );
        $menu->addLink(
            $controller->url_for('questionnaire/delete/'  .$questionnaire->id),
            _('Fragebogen löschen'),
            Icon::create('trash', 'clickable'),
            ['data-confirm' => _('Wirklich löschen?')]
        );
        echo $menu->render();
        ?>
    </td>
</tr>
