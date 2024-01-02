<?php
/**
 * @var LoginBackground[] $pictures
 * @var Admin_LoginStyleController $controller
 */
?>
<form method="post">
    <?= CSRFProtection::tokenTag(); ?>

    <? if (count($pictures) > 0) : ?>
    <table class="default">
        <caption>
            <?= _('Hintergrundbilder für den Startbildschirm') ?>
        </caption>
        <colgroup>
            <col>
            <col style="width: 400px">
            <col style="width: 100px">
            <col style="width: 25px">
        </colgroup>
        <thead>
        <tr>
            <th><?= _('Info') ?></th>
            <th><?= _('Vorschau') ?></th>
            <th><?= _('Aktiviert für') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <? foreach ($pictures as $pic) :
            $dim = $pic->getDimensions();
            ?>
            <tr>
                <td>
                    <?= htmlReady($pic->filename) ?>
                    <br>
                    (<?= $dim[0] ?> x <?= $dim[1] ?>,
                    <?= relsize($pic->getFilesize(), false) ?>)
                </td>
                <td>
                    <img src="<?= $pic->getURL() ?>" width="400">
                </td>
                <td>
                    <?= Icon::create('computer', $pic->desktop ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asInput(
                        32,
                        [
                            'title' => $pic->mobile
                                ? _('Bild nicht mehr für die Mobilansicht verwenden')
                                : _('Bild für die Mobilansicht verwenden'),
                            'formaction' => $controller->activationURL($pic->id, 'desktop', (int) !$pic->desktop)
                        ]
                    )?>

                    <?= Icon::create('cellphone', $pic->mobile ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asInput(
                        32,
                        [
                            'title' => $pic->mobile
                                ? _('Bild nicht mehr für die Mobilansicht verwenden')
                                : _('Bild für die Mobilansicht verwenden'),
                            'formaction' => $controller->activationURL($pic->id, 'mobile', (int) !$pic->mobile)
                        ]
                    )?>
                </td>
                <td class="actions">
                    <? if (!$pic->in_release): ?>
                        <?= Icon::create('trash')->asInput(
                            [
                                'title'        => _('Bild löschen'),
                                'data-confirm' => _('Soll das Bild wirklich gelöscht werden?'),
                                'formaction' => $controller->delete_picURL($pic->id)
                            ]
                        )?>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </table>
    <? else : ?>
    <?= MessageBox::info(_('In Ihrem System sind leider keine Bilder für den Startbildschirm hinterlegt.')) ?>
    <? endif ?>
</form>
