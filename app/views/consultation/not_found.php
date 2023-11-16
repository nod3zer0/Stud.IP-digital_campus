<?php
/**
 * @var string $type
 */
?>
<?= MessageBox::error(
    $type === 'person'
        ? _('Die Person, für die die Sprechstunden angezeigt werden sollen, ist nicht mehr vorhanden')
        : _('Das Objekt, für das die Sprechstunden angezeigt werden sollen, ist nicht mehr vorhanden')
)->hideClose() ?>
