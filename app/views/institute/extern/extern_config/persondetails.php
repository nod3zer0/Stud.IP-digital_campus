<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 * @var ExternPagePersonDetails $page
 */
?>

<span class="content-title">
    <?= _('Konfiguration für die Detailansicht von Mitarbeitenden (Personal)') ?>
</span>

<form method="post" action="<?= $controller->store('PersonDetails', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>
    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label>
            <input type="checkbox" name="defaultaddr" value="1"
                   <?= $page->defaultaddr ? 'checked' : '' ?>>
            <?= _('Standard-Adresse') ?>
            <?= tooltipIcon(_('Wenn Sie diese Option wählen, wird die Standard-Adresse ausgegeben, '
                . 'die jede(r) Mitarbeiter(in) bei seinen universitären Daten auswählen kann. '
                . 'Wählen Sie diese Option nicht, wenn immer die Adresse der Einrichtung ausgegeben werden soll.')) ?>
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Persönliche Lehrveranstaltungen') ?>
        </legend>
        <label>
            <?= _('Startsemester') ?>
            <?= tooltipIcon(_('Geben Sie das erste anzuzeigende Semester an. '
                . 'Die Angaben "vorheriges", "aktuelles" und "nächstes" beziehen sich immer auf das laufende Semester '
                . 'und werden automatisch angepasst.')) ?>
            <select name="startsem">
                <option value="previous"<?= $page->startsem === 'previous' ? ' selected' : '' ?>>
                    <?= _('vorheriges') ?>
                </option>
                <option value="current"<?= $page->startsem === 'current' ? ' selected' : '' ?>>
                    <?= _('aktuelles') ?>
                </option>
                <option value="last"<?= $page->startsem === 'last' ? ' selected' : '' ?>>
                    <?= _('letztes') ?>
                </option>
                <? foreach (array_reverse(Semester::getAll()) as $semester) : ?>
                    <option value="<?= $semester->id ?>"<?= $page->startsem === $semester->id ? ' selected' : '' ?>>
                        <?= htmlReady($semester->name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Anzahl der anzuzeigenden Semester') ?>
            <?= tooltipIcon(_('Geben Sie an, wieviele Semester (ab o.a. Startsemester) angezeigt werden sollen.')) ?>
            <input type="number" name="semcount" value="<?= (int) $page->semcount ?>">
        </label>
        <label>
            <?= _('Umschalten des aktuellen Semesters') ?>
            <?= tooltipIcon(_('Geben Sie an, wieviele Wochen vor Semesterende automatisch auf das nächste Semester '
                . 'umgeschaltet werden soll.')) ?>
            <select name="semswitch">
                <option value="0"<?= $page->semswitch === 0 ? ' selected' : '' ?>><?= _('Am Semesterende') ?></option>
                <option value="-1"<?= $page->semswitch === -1 ? ' selected' : '' ?>>
                    <? printf(ngettext('%s Woche vor Semesterende (Systemkonfiguration)',
                            '%s Wochen vor Semesterende (Systemkonfiguration)',
                            Config::get()->SEMESTER_TIME_SWITCH),
                        Config::get()->SEMESTER_TIME_SWITCH) ?>
                </option>
                <? foreach (range(1, 12) as $weeks) : ?>
                    <option value="<?= $weeks ?>"<?= $weeks === $page->semswitch ? ' selected' : '' ?>>
                        <? printf(ngettext('%s Woche vor Semesterende',
                                '%s Wochen vor Semesterende', $weeks),
                            $weeks) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Veranstaltungskategorien') ?>
            <?= tooltipIcon(_('Wählen Sie aus, welche Veranstaltungskategorien angezeigt werden sollen.')) ?>
            <select class="nested-select" name="semclass[]" multiple>
                <? foreach ($GLOBALS['SEM_CLASS'] as $key => $sem_class) : ?>
                    <? if ($sem_class['show_browse']) : ?>
                        <option value="<?= $key ?>"<?= in_array($key, $page->semclass) ? ' selected' : '' ?>>
                            <?= htmlReady($sem_class['name']) ?>
                        </option>
                    <? endif ?>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <?= $this->render_partial('institute/extern/extern_config/_template') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createAccept(_('Speichern und zurück'), 'store_cancel') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'), $controller->indexURL()
        ) ?>
    </footer>

</form>
