<? foreach ($studygroups as $group)  : ?>
    <tr>
        <td class="gruppe<?= $group['gruppe'] ?>"></td>
        <td>
            <?= CourseAvatar::getAvatar($group['seminar_id'])->getImageTag(Avatar::SMALL, ['title' => $group['name']])
            ?>
        </td>
        <td style="text-align: left">
            <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $group['seminar_id']]) ?>"
                <?= $group['last_visitdate'] >= $group['chdate'] ? 'style="color: red;"' : '' ?>>
                <?= htmlReady($group['name']) ?>
            </a>
            <? if ($group['visible'] == 0) : ?>
                <? $infotext = _("Versteckte Studiengruppen können über die Suchfunktionen nicht gefunden werden."); ?>
                <? $infotext .= " "; ?>
                <? if (Config::get()->ALLOW_DOZENT_VISIBILITY) : ?>
                    <? $infotext .= _("Um die Studiengruppe sichtbar zu machen, wählen Sie den Punkt \"Sichtbarkeit\" im Administrationsbereich der Veranstaltung."); ?>
                <? else : ?>
                    <? $infotext .= _("Um die Studiengruppe sichtbar zu machen, wenden Sie sich an die Admins."); ?>
                <? endif ?>
                <?= _("[versteckt]") ?>
                <?= tooltipicon($infotext) ?>
            <? endif ?>
        </td>
        <td style="text-align: left; white-space: nowrap;">
            <? if (!empty($group['navigation'])) : ?>
                <ul class="my-courses-navigation" style="flex-wrap: nowrap">
                <? foreach (MyRealmModel::array_rtrim($group['navigation']) as $key => $nav)  : ?>
                    <? if (isset($nav) && $nav->isVisible(true)) : ?>
                        <li class="my-courses-navigation-item <? if ($nav->getImage()->signalsAttention()) echo 'my-courses-navigation-important'; ?>">
                            <a href="<?=
                            URLHelper::getLink('seminar_main.php',
                                ['auswahl'     => $group['seminar_id'],
                                      'redirect_to' => $nav->getURL()]) ?>" <?= $nav->hasBadgeNumber() ? 'class="badge" data-badge-number="' . intval($nav->getBadgeNumber()) . '"' : '' ?>>
                                <?= $nav->getImage()->asImg(20, $nav->getLinkAttributes()) ?>
                            </a>
                        </li>
                    <? elseif (is_string($key)) : ?>
                        <li class="my-courses-navigation-item">
                            <span class="empty-slot" style="width: 20px"></span>
                        </li>
                    <? endif ?>
                <? endforeach ?>
                </ul>
            <? endif ?>
        </td>
        <td style="text-align: right">
            <? if (in_array($group["user_status"], ["dozent", "tutor"])) : ?>
                <? $adminmodule = $group["sem_class"]->getAdminModuleObject(); ?>
                <? if ($adminmodule) : ?>
                    <? $adminnavigation = $adminmodule->getIconNavigation($group['seminar_id'], 0, $GLOBALS['user']->id); ?>
                <? endif ?>
                <? if ($adminnavigation) : ?>
                    <a href="<?= URLHelper::getLink($adminnavigation->getURL(), ['cid' => $group['seminar_id']]) ?>">
                        <?= $adminnavigation->getImage()->asImg(20, $adminnavigation->getLinkAttributes())?>
                    </a>
                <? endif ?>

            <? elseif ($group["binding"]) : ?>
                <a href="<?= URLHelper::getLink('', ['auswahl' => $group['seminar_id'], 'cmd' => 'no_kill']) ?>">
                    <?= Icon::create('door-leave', 'inactive', ['title' => _("Die Teilnahme ist bindend. Bitte wenden Sie sich an die Lehrenden.")])->asImg(20) ?>
                </a>
            <?
            else : ?>
                <a href="<?= URLHelper::getLink("dispatch.php/my_courses/decline/{$group['seminar_id']}", ['cmd' => 'suppose_to_kill']) ?>">
                    <?= Icon::create('door-leave', 'inactive', ['title' => _("aus der Studiengruppe abmelden")])->asImg(20) ?>
                </a>
            <? endif ?>
        </td>
    </tr>
<? endforeach ?>
