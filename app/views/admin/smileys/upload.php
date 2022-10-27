<?php
/**
 * @var Admin_SmileysController $controller
 * @var string $view
 */

use Studip\Button, Studip\LinkButton;
?>
<form action="<?= $controller->url_for('admin/smileys/upload', $view) ?>"
      method="post" enctype="multipart/form-data">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default">
        <thead class="hide-in-dialog">
            <tr>
                <th colspan="2"><b><?= _('Neues Smiley hochladen') ?></b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label for="replace"><?= _('existierende Datei überschreiben') ?></label>
                </td>
                <td>
                    <input type="checkbox" id="replace" name="replace" value="1">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="file"><?= _('Bilddatei auswählen') ?></label>
                </td>
                <td>
                    <input type="file" id="file" name="smiley_file" required>
                </td>
            </tr>
        </tbody>
        <tfoot data-dialog-button>
            <tr>
                <td colspan="2" align="center">
                    <?= Button::createAccept(_('Hochladen'), 'upload') ?>
                    <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/smileys?view=' . $view))?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
