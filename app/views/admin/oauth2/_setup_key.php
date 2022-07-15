<?php
    $checkmark = function (bool $checked): Icon {
        return $checked
             ? Icon::create('accept', Icon::ROLE_STATUS_GREEN)
             : Icon::create('decline', Icon::ROLE_STATUS_RED);
    };

    $predicate = function ($checked, $positive, $negative) {
        return $checked ? $positive : $negative;
    };
?>
<ul>
    <li style="list-style-image: url(<?= $checkmark($key->exists())->asImagePath() ?>)">
        <?= $predicate($key->exists(), _('Datei existiert.'), _('Datei existiert nicht.')) ?>
    </li>
    <li style="list-style-image: url(<?= $checkmark($key->isReadable())->asImagePath() ?>)">
        <?= $predicate($key->isReadable(), _('Datei ist lesbar.'), _('Datei ist nicht lesbar.')) ?>
    </li>
    <? if ($key->isReadable()) { ?>
        <li style="list-style-image: url(<?= $checkmark($key->hasProperMode())->asImagePath() ?>)">
            <?= $predicate(
                $key->hasProperMode(),
                sprintf(_('Korrekte Zugriffsberechtigung: %s'), $key->mode()),
                sprintf(_('Falsche Zugriffsberechtigung: %s'), $key->mode())
            ) ?>
        </li>
    <? } ?>
</ul>
