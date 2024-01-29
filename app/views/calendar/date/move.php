<?= MessageBox::info(_('Es handelt sich um einen Termin in einer Terminserie. Was möchten Sie tun?')) ?>
<form class="default" method="post" data-dialog="reload-on-close"
      action="<?= $controller->link_for('calendar/date/move/' . $date->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="begin" value="<?= htmlReady($begin->format(\DateTime::RFC3339)) ?>">
    <input type="hidden" name="end" value="<?= htmlReady($end->format(\DateTime::RFC3339)) ?>">
    <label>
        <input type="radio" name="repetition_handling" value="create_single_date">
        <?= _('Der Termin soll aus der Terminserie herausgelöst werden.') ?>
    </label>
    <label>
        <input type="radio" name="repetition_handling" value="change_times">
        <?= _('Start- und Enduhrzeit der gesamten Terminserie soll geändert werden.') ?>
    </label>
    <label>
        <input type="radio" name="repetition_handling" value="change_all">
        <?= _('Die gesamte Terminserie soll verschoben werden und erst am gewählten Datum beginnen.') ?>
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Verschieben'), 'move') ?>
    </div>
</form>
