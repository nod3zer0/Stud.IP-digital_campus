<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 * @var ExternPageTimetable $page
 */
?>

<span class="content-title">
    <?= _('Konfiguration für die Ausgabe Veranstaltungsterminen') ?>
</span>
<form method="post" action="<?= $controller->store('Timetable', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>
    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label>
            <?= _('Startdatum') ?>
            <input type="text" name="date" class="size-s" data-date-picker="" value="<?= htmlReady($page->date) ?>">
        </label>
        <label>
            <select name="date_offset">
                <? foreach ($page->getDateOffsetOptions() as $offset_key => $offset_name) : ?>
                    <option value="<?= $offset_key ?>"<?= $offset_key === $page->date_offset ? ' selected' : '' ?>>
                        <?= htmlReady($offset_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Anzahl der Zeitbereiche') ?>
            <?= tooltipIcon(_('Geben Sie an, wie viele der folgenden Zeitbereiche angezeigt werden sollen.')) ?>
            <input type="number" min="1" name="range_count" value="<?= htmlReady($page->range_count ?: '1') ?>">
        </label>
        <label>
            <?= _('Anzuzeigender Zeitbereich') ?>
            <select name="time_range">
                <? foreach ($page->getTimeRangeOptions() as $range_key => $range_name) : ?>
                    <option value="<?= $range_key ?>"<?= $range_key === $page->time_range ? ' selected' : '' ?>>
                        <?= htmlReady($range_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Anzuzeigende Termintypen') ?>
            <select name="event_types[]" class="nested-select" multiple>
                <? foreach (Config::get()->TERMIN_TYP as $type_id => $event_typ) : ?>
                    <option value="<?= $type_id ?>"<?= in_array($type_id, (array) $page->event_types) ? ' selected' : '' ?>>
                        <?= htmlReady($event_typ['name']) ?>
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
