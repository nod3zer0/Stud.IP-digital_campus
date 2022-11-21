<form class="default" action="<?= $controller->link_for('lti/link_content_item') ?>" method="POST">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Veranstaltung auswählen') ?>
        </legend>

        <label>
            <?= _('Veranstaltung') ?>

            <select name="course_id" class="nested-select">
                <? foreach ($courses as $course): ?>
                    <option value="<?= $course->id ?>">
                        <?= htmlReady($course->getFullname('number-name-semester')) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <label>
            <?= _('Art der Anzeige') ?>

            <select name="target">
                <? foreach (explode(',', $document_targets) as $target): ?>
                    <option value="<?= htmlReady($target) ?>" <?= $target === 'window' ? 'selected' : '' ?>>
                        <?= htmlReady($target_labels[$target] ?? $target) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Verknüpfen'), 'link') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'abort') ?>
    </footer>
</form>
