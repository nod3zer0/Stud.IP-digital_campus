<form class="default" method="post" action="<?= $controller->link_for('course/members/circular_mail') ?>"
      data-dialog="size=default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('An wen möchten Sie eine Rundmail schreiben?') ?></legend>
        <p aria-hidden="true"><?= _('An wen möchten Sie eine Rundmail schreiben?') ?></p>
        <? if (in_array('dozent', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="dozent"
                    <? if (!$dozent_count): echo 'disabled'; ?>
                    <? elseif (in_array('dozent', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= htmlready($dozent_name) ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $dozent_count),
                        $dozent_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
        <? if (in_array('tutor', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="tutor"
                    <? if (!$tutor_count): echo 'disabled'; ?>
                    <? elseif (in_array('tutor', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= htmlReady($tutor_name) ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $tutor_count),
                        $tutor_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
        <? if (in_array('autor', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="autor"
                    <? if (!$autor_count): echo 'disabled'; ?>
                    <? elseif (in_array('autor', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= htmlReady($autor_name) ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $autor_count),
                        $autor_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
        <? if (in_array('user', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="user"
                    <? if (!$user_count): echo 'disabled'; ?>
                    <? elseif (in_array('user', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= htmlReady($user_name) ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $user_count),
                        $user_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
        <? if (in_array('accepted', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="accepted"
                    <? if (!$accepted_count): echo 'disabled'; ?>
                    <? elseif (in_array('accepted', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= _('Alle vorläufig akzeptierten Teilnehmende der Veranstaltung') ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $accepted_count),
                        $accepted_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
        <? if (in_array('awaiting', $all_available_groups)) : ?>
            <label>
                <input type="checkbox" name="selected_groups[]" value="awaiting"
                    <? if (!$awaiting_count): echo 'disabled'; ?>
                    <? elseif (in_array('awaiting', $default_selected_groups)): echo 'checked'; ?>
                    <? endif; ?>>
                <?= _('Alle Personen auf der Warteliste der Veranstaltung') ?>
                <em>
                    (<?= sprintf(
                        ngettext('%u Person', '%u Personen', $awaiting_count),
                        $awaiting_count
                    ) ?>)
                </em>
            </label>
        <? endif ?>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Rundmail schreiben'), 'write') ?>
    </div>
</form>
