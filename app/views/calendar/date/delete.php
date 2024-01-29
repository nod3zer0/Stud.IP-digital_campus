<form class="default" method="post" data-dialog="reload-on-close"
      action="<?= $controller->link_for('calendar/date/delete/' . $date->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($date_has_repetitions) : ?>
        <input type="hidden" name="selected_date" value="<?= $selected_date->format('Y-m-d') ?>">
        <fieldset>
            <legend><?= _('Es handelt sich um einen Termin in einer Terminserie. Was möchten Sie tun?') ?></legend>
            <label>
                <input type="radio" name="repetition_handling" value="create_exception"
                    <?= $repetition_handling === 'create_exception' ? 'checked' : '' ?>>
                <?= sprintf(
                    _('Am %s soll aus dem Einzeltermin eine Ausnahme der Terminserie werden.'),
                    $selected_date->format('d.m.Y')
                ) ?>
            </label>
            <label>
                <input type="radio" name="repetition_handling" value="delete_all"
                    <?= $repetition_handling === 'delete_all' ? 'checked' : '' ?>>
                <?= _('Die gesamte Terminserie soll gelöscht werden.') ?>
            </label>
        </fieldset>
    <? else : ?>
        <?= MessageBox::warning(_('Soll der folgende Termin wirklich gelöscht werden?')) ?>
    <? endif ?>
    <fieldset>
        <legend><?= _('Informationen') ?></legend>
        <dl>
            <dt><?= _('Zeitbereich') ?></dt>
            <dd>
                <?= htmlReady(date('d.m.Y H:i', $date->begin)) ?>
                -
                <?= htmlReady(date('d.m.Y H:i', $date->end)) ?>
            </dd>
            <dt><?= _('Titel') ?></dt>
            <dd><?= htmlReady($date->title) ?></dd>
            <? if ($date->description) : ?>
                <dt><?= _('Beschreibung') ?></dt>
                <dd><?= htmlReady($date->description) ?></dd>
            <? endif ?>
            <dt><?= _('Zugriff') ?></dt>
            <dd><?= htmlReady($date->getAccessAsString()) ?></dd>
            <? if ($date->repetition_type) : ?>
                <dt><?= _('Wiederholung') ?></dt>
                <dd><?= htmlReady($date->getRepetitionAsString()) ?></dd>
            <? endif ?>
        </dl>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Löschen'), 'delete') ?>
    </div>
</form>
