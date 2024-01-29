<form class="default new-calendar-date-form" method="post" action="<?= $form_post_link ?>"
      data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>

    <? if ($return_path = Request::get('return_path')) : ?>
        <input type="hidden" name="return_path" value="<?= htmlReady($return_path) ?>">
    <? endif ?>

    <? if ($user_id) : ?>
        <input type="hidden" name="user_id" value="<?= htmlReady($user_id) ?>">
    <? endif ?>
    <? if ($group_id) : ?>
        <input type="hidden" name="group_id" value="<?= htmlReady($group_id) ?>">
    <? endif ?>

    <article aria-live="assertive"
             class="validation_notes studip">
        <header>
            <h1>
                <?= Icon::create('info-circle', Icon::ROLE_INFO)->asImg(['class' => 'text-bottom validation_notes_icon']) ?>
                <?= _('Hinweise zum Ausfüllen des Formulars') ?>
            </h1>
        </header>
        <div class="required_note">
            <div aria-hidden="true">
                <?= _('Pflichtfelder sind mit Sternchen gekennzeichnet.') ?>
            </div>
            <div class="sr-only">
                <?= _('Dieses Formular enthält Pflichtfelder.') ?>
            </div>
        </div>
        <? if ($form_errors) : ?>
            <div>
                <?= _('Folgende Angaben müssen korrigiert werden, um das Formular abschicken zu können:') ?>
                <ul>
                    <? foreach ($form_errors as $field => $error) : ?>
                        <li><?= htmlReady($field) ?>: <?= htmlReady($error) ?></li>
                    <? endforeach ?>
                </ul>
            </div>
        <? endif ?>
    </article>

    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label class="studiprequired">
            <?= _('Titel') ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
            <input type="text" name="title" required="required"
                   value="<?= htmlReady($date->title) ?>">
        </label>
        <div class="hgroup">
            <label class="studiprequired">
                <?= _('Beginn') ?>
                <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
                <input type="text" name="begin" class="begin-input" data-datetime-picker
                       required="required" value="<?= date('d.m.Y H:i', $date->begin) ?>">
            </label>
            <label class="studiprequired">
                <?= _('Ende') ?>
                <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
                <input type="text" name="end" class="end-input" data-datetime-picker
                       required="required" value="<?= date('d.m.Y H:i', $date->end) ?>">
            </label>
        </div>
        <label>
            <input type="checkbox" name="all_day" value="1" <?= $all_day_event ? 'checked' : '' ?>
                   data-deactivates=".new-calendar-date-form input[name='end']">
            <?= _('Ganztägiger Termin') ?>
        </label>
        <label>
            <?= _('Zugriff') ?>
            <div class="flex-row">
                <select name="access">
                    <option value="PUBLIC" <?= $date->access === 'PUBLIC' ? 'selected' : '' ?>>
                        <?= _('Öffentlich zugänglich') ?>
                    </option>
                    <option value="PRIVATE" <?= $date->access === 'PRIVATE' ? 'selected' : '' ?>>
                        <?= _('Privat') ?>
                    </option>
                    <option value="CONFIDENTIAL" <?= $date->access === 'CONFIDENTIAL' ? 'selected' : '' ?>>
                        <?= _('Vertraulich') ?>
                    </option>
                </select>
                <?= tooltipIcon(
                    _('Öffentliche Termine sind systemweit sichtbar. Private Termine sind für Personen, denen der Kalender freigegeben wurde, sichtbar. Vertrauliche Termine sind hingegen nur für einen selbst sichtbar.')
                ) ?>
            </div>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description"><?= htmlReady($date->description) ?></textarea>
        </label>
        <label class="studiprequired">
            <?= _('Kategorie') ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
            <select class="" name="category" required>
                <? foreach ($category_options as $key => $option) : ?>
                    <option value="<?= htmlReady($key) ?>" <?= $key === intval($date->category) ? 'selected' : '' ?>>
                        <?= htmlReady($option) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Eigene Kategorie') ?>
            <input type="text" name="user_category" value="<?= htmlReady($date->user_category) ?>">
        </label>
        <label>
            <?= _('Ort') ?>
            <input type="text" name="location" value="<?= htmlReady($date->location) ?>">
        </label>
    </fieldset>
    <fieldset class="simplevue">
        <legend><?= _('Wiederholung') ?></legend>
        <?= $date->getRepetitionInputHtml('repetition') ?>
    </fieldset>
    <fieldset class="simplevue">
        <legend><?= _('Ausnahmen') ?></legend>
        <date-list-input name="exceptions" :selected_dates="<?= htmlReady(json_encode($exceptions)) ?>"></date-list-input>
    </fieldset>
    <? if (Config::get()->CALENDAR_GROUP_ENABLE && $user_quick_search_type) : ?>
        <fieldset class="simplevue">
            <legend><?= _('Teilnehmende Personen') ?></legend>
            <editable-list
                name="assigned_calendar_ids"
                quicksearch="<?= htmlReady($user_quick_search_type) ?>"
                :items="<?= htmlReady(json_encode($calendar_assignment_items)) ?>"
            ></editable-list>
        </fieldset>
    <? elseif ($calendar_assignment_items) : ?>
       <? foreach ($calendar_assignment_items as $item) : ?>
            <input type="hidden" name="assigned_calendar_ids[]" value="<?= htmlReady($item['value']) ?>">
       <? endforeach ?>
    <? elseif ($owner_id): ?>
        <input type="hidden" name="assigned_calendar_ids[]" value="<?= htmlReady($owner_id) ?>">
    <? endif ?>

    <footer data-dialog-button>
        <? if ($date->isNew()) : ?>
            <?= \Studip\Button::create(_('Anlegen'), 'save') ?>
        <? else : ?>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
        <? endif ?>
        <? if (!$date->isNew()) : ?>
            <?= \Studip\LinkButton::create(
                _('Löschen'),
                $controller->url_for('calendar/date/delete/' . $date->id),
                ['data-dialog' => 'reload-on-close']
            ) ?>
        <? endif ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('calendar/calendar')) ?>
    </footer>
</form>
