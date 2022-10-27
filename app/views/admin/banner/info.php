<?php
/**
 * @var Admin_BannerController $controller
 * @var array $banner
 * @var array $target_types
 * @var array $priorities
 * @var array $assigned
 */
?>
<table class="default">
    <tbody>
        <tr>
            <td rowspan="9" colspan="2" style="text-align: center;">
            <? if ($banner['banner_path']): ?>
                <?= $banner->toImg() ?>
            <? else: ?>
                <?= _('noch kein Bild hochgeladen') ?>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td><?= _("Beschreibung:") ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($banner['description']) ?>"
                       size="40" maxlength="254">
            </td>
        </tr>
        <tr>
            <td><?= _('Alternativtext:') ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($banner['alttext']) ?>"
                       size="40" maxlength="254">
            </td>
        </tr>
        <tr>
            <td><?= _('Verweis-Typ:') ?></td>
            <td>
                 <select disabled>
                 <? foreach ($target_types as $key => $label): ?>
                    <option value="<?= $key ?>" <? if ($banner['target_type'] == $key) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?= _('Verweis-Ziel:') ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($banner['target']) ?>"
                       size="40" maxlength="254" disabled>
            </td>
        </tr>
        <tr>
            <td><?= _('Anzeigen ab:')?></td>
            <td>
                <input type="text" size="40" name="start_date" id="start_date" value="<?= $banner['startdate'] ?
                    date('d.m.Y H:i', $banner['startdate']) : '' ?>" data-datetime-picker disabled>
            </td>
        </tr>
        <tr>
            <td><?= _('Anzeigen bis:') ?></td>
            <td>
                <input type="text" size="40" name="end_date" id="end_date" value="<?= $banner['enddate'] ?
                    date('d.m.Y H:i', $banner['enddate']) : '' ?>" data-datetime-picker disabled>
            </td>
        </tr>
        <tr>
            <td><?= _('Priorität:') ?></td>
            <td>
                <select disabled>
                <? foreach ($priorities as $key => $label): ?>
                    <option value="<?= $key ?>" <? if ($banner['priority'] == $key) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?= _('Sichtbarkeit:')?></td>
            <td>
                <select multiple name="assignedroles[]" size="10" style="width: 100%" disabled>
                <? foreach ($assigned as $assignedrole): ?>
                    <option value="<?= $assignedrole->getRoleid() ?>">
                        <?= htmlReady($assignedrole->getRolename()) ?>
                        <? if ($assignedrole->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                    </option>
                <? endforeach ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>
