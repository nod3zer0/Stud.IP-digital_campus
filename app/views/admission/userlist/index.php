<?php
/**
 * @var AdmissionUserList[] $userlists
 * @var Admission_UserListController $controller
 */
Helpbar::get()->addPlainText(_('Info'),"Personenlisten erfassen eine Menge von Personen, die ".
                                       "mit modifizierten Chancen in die Platzverteilung bei ".
                                       "Anmeldeverfahren eingehen. Dies können z.B. ".
                                       "Härtefälle sein, die bevorzugt einen Platz in ".
                                       "Veranstaltungen erhalten sollen.");
Helpbar::get()->addPlainText(_('Info'), "Hier sehen Sie alle Personenlisten, auf die Sie Zugriff ".
                                        "haben.");
?>
<form action="#" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default">
        <colgroup>
            <col>
            <col>
            <col>
            <col style="width: 8ex;">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
                <th><?= _('Personen') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (empty($userlists)): ?>
            <tr>
                <td colspan="4" style="text-align: center;">
                    <?= _('Es sind noch keine Personenlisten vorhanden.') ?><br>
                    <?= Studip\LinkButton::create(
                        _('Neue Personenliste anlegen'),
                        $controller->configureURL()
                    ) ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($userlists as $list): ?>
            <tr id="userlist_<?= htmlReady($list->getId()) ?>">
                <td><?= htmlReady($list->getName()) ?></td>
                <td><?= htmlReady($list->describe()) ?></td>
                <td><?= count($list->getUsers()) ?></td>
                <td class="actions">
                    <a href="<?= $controller->configure($list->getId()) ?>">
                        <?= Icon::create('edit')->asImg(tooltip2(_('Nutzerliste bearbeiten'))) ?>
                    </a>
                    <?= Icon::create('trash')->asInput(tooltip2(_('Personenliste löschen')) + [
                       'formaction' => $controller->deleteURL($list->getId()),
                       'data-confirm' => sprintf(_('Soll die Nutzerliste %s wirklich gelöscht werden?'), $list->getName()),
                    ]) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
</form>
