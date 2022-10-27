<?php
/**
 * @var Admin_SmileysController $controller
 * @var Smiley $smiley
 * @var string $view
 */
use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/smileys/edit', $smiley->id, $view) ?>"
      method="post" enctype="multipart/form-data">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default">
        <thead class="hide-in-dialog">
            <tr>
                <th colspan="2"><b><?= _('Smiley bearbeiten') ?></b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= _('Smiley:')?></td>
                <td align="center"><?= $smiley->getImageTag() ?></td>
            </tr>
            <tr>
                <td>
                    <label for="name"><?= _('Name')?></label>
                </td>
                <td>
                    <input type="text" name="name" id="name" required pattern="[A-Za-z0-9-_]+"
                           value="<?= Request::option('name', $smiley->name) ?>">
                    <br>
                    <small><?= _('Erlaubte Zeichen:') ?> a-z 0-9 &ndash; _</small>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="short"><?= _('Kürzel')?></label>
                </td>
                <td>
                    <input type="text" name="short" id="short" 
                           value="<?= Request::option('short', $smiley->short) ?>">
                </td>
            </tr>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td><?= date('d.m.Y H:i:s', $smiley->mkdate) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert') ?></td>
                <td><?= date('d.m.Y H:i:s', $smiley->chdate) ?></td>
            </tr>
        </tbody>
        <tfoot data-dialog-button>
            <tr>
                <td colspan="2">
                    <?= Button::createAccept(_('Speichern'), 'edit') ?>
                    <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/smileys?view=' . $view))?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
