<?php
/**
 * @var Admin_PluginController $controller
 */
?>
<form action="<?= $controller->url_for('admin/plugin/install') ?>"
      method="post" enctype="multipart/form-data" class="drag-and-drop">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <?= _('Plugin auswählen oder via Drag and Drop installieren') ?>
    <input type="file" name="upload_file">
</form>
