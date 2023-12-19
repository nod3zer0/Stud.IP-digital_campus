<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 * @var ExternPageCourses $page
 */
?>

<span class="content-title">
    <?= _('Konfiguration für die Ausgabe von Veranstaltungen') ?>
</span>
<form method="post" action="<?= $controller->store('Courses', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>
    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label class="col-3">
            <?= _('Gruppierung') ?>
            <select name="groupby">
                <? foreach ($page->getGroupingOptions() as $id => $name) : ?>
                    <option value="<?= $id ?>"<?= $page->groupby == $id ? ' selected' : '' ?>><?= htmlReady($name) ?></option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Startsemester') ?>
            <?= tooltipIcon(_('Geben Sie das erste anzuzeigende Semester an. '
                . 'Die Angaben "Vorheriges", "Aktuelles" und "Nächstes" beziehen sich immer auf das laufende Semester '
                . 'und werden automatisch angepasst.')) ?>
            <select name="startsem">
                <? foreach ($page->getSemesterOptions() as $semester) : ?>
                    <option
                        value="<?= htmlReady($semester['id']) ?>"
                        <?= $page->startsem === $semester['id'] ? ' selected' : '' ?>>
                        <?= htmlReady($semester['name']) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Anzahl der anzuzeigenden Semester') ?>
            <?= tooltipIcon(_('Geben Sie an, wieviele Semester (ab o.a. Startsemester) angezeigt werden sollen.')) ?>
            <input type="number" min="1" name="semcount" value="<?= htmlReady($page->semcount ?: '1') ?>">
        </label>
        <label>
            <?= _('Umschalten des aktuellen Semesters') ?>
            <?= tooltipIcon(_('Geben Sie an, wieviele Wochen vor Semesterende automatisch auf das nächste Semester '
                . 'umgeschaltet werden soll.')) ?>
            <select name="semswitch">
                <option value="0"><?= _('Am Semesterende') ?></option>
                <option value="-1">
                    <? printf(ngettext('%s Woche vor Semesterende (Systemkonfiguration)',
                        '%s Wochen vor Semesterende (Systemkonfiguration)',
                        Config::get()->SEMESTER_TIME_SWITCH),
                        Config::get()->SEMESTER_TIME_SWITCH) ?>
                </option>
                <? foreach (range(1, 12) as $weeks) : ?>
                    <option value="<? $weeks ?>">
                        <? printf(ngettext('%s Woche vor Semesterende',
                            '%s Wochen vor Semesterende', $weeks),
                            $weeks) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <input type="checkbox" name="participating" value="1"
                <?= $page->participating ? 'checked' : '' ?>>
            <?= _('Veranstaltungen beteiligter Institute anzeigen.') ?>
        </label>
        <?= $this->render_partial('institute/extern/extern_config/_sem_types_selector') ?>
        <?= $this->render_partial('institute/extern/extern_config/_institutes_selector') ?>
    </fieldset>
    <?= $this->render_partial('institute/extern/extern_config/_study_areas_selector') ?>
    <?= $this->render_partial('institute/extern/extern_config/_template') ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createAccept(_('Speichern und zurück'), 'store_cancel') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'), $controller->indexURL()
        ) ?>
    </footer>
</form>
