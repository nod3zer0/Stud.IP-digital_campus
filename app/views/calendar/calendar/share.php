<form class="default" method="post"
      action="<?= $controller->link_for('calendar/calendar/share') ?>"
      data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset class="simplevue">
        <calendar-permissions-table name="calendar"
                                    :selected_users="<?= htmlReady($selected_users_json ?? '{}') ?>"
                                    searchtype="<?= htmlReady((string) $searchtype) ?>"></calendar-permissions-table>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Aktualisieren'), 'share') ?>
    </div>
</form>
