<?php
/**
 * @var Admin_LoginStyleController $controller
 * @var LoginFaq $entry
 */
?>
<form action="<?= $controller->store_faq($entry) ?>"
      method="post"
      enctype="multipart/form-data"
      class="default">
    <?= CSRFProtection::tokenTag() ?>

    <label class="studiprequired">
        <?= _('Titel') ?>
        <span title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true" class="asterisk">*</span>
        <input type="text" name="title" value="<?= htmlReady($entry->title) ?>" required>
    </label>

    <label>
        <span class="studiprequired">
            <?= _('Text') ?>
            <span title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true" class="asterisk">*</span>
        </span>
        <textarea name="description"
                  class="add_toolbar wysiwyg" data-editor="toolbar=minimal"><?= htmlReady($entry->description)?></textarea>
    </label>


    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')) ?>
    </div>

</form>
