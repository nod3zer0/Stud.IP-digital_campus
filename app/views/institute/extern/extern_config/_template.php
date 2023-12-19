<?php
/**
 * @var ExternPage $page
 */
?>

<fieldset>
    <legend>
        <?= _('Template') ?>
    </legend>
    <label>
        <textarea name="template" placeholder="<?= _('Geben Sie hier das Template ein.') ?>"
                  cols="80" rows="20"><?= htmlReady($page->template) ?></textarea>
    </label>
</fieldset>
