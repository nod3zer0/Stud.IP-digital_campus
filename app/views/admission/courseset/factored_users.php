<?php
/**
 * @var array $users
 */
?>
<table class="default">
    <thead>
        <tr>
            <th><?= _("Name")?></th>
            <th><?= _("Faktor")?></th>
            <th><?= _("Angemeldet")?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($users as $user) : ?>
        <tr>
            <td><?= htmlReady($user['nachname'] . ', ' . $user['vorname'] . ' (' . $user['username'] . ')')?></td>
            <td><?= $user['factor'] == PHP_INT_MAX ? _('maximal') : htmlReady($user['factor'])?></td>
            <td><?= $user['applicant'] ? _("Ja") : _("Nein")?></td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
