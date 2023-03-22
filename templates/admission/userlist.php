<?php
/**
 * @var AdmissionUserList $userlist
 */
?>
<?= $userlist->describe(['<b>', '</b>']) ?><br>
<?= _('Personen auf dieser Liste:') ?>
<? if ($userlist->getUsers()): ?>
<ul>
<? foreach ($userlist->getUsers(true) as $user): ?>
    <li>
        <?= htmlReady($user->getFullname('full_rev')) ?>
        (<?= htmlReady($user->username) ?>)
    </li>
<? endforeach; ?>
</ul>
<? else: ?>
<br>
<i><?= _('Es wurde noch niemand zugeordnet.'); ?></i>
<? endif; ?>
