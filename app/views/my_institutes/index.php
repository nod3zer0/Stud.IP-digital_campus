<?php
$is_important = function (?Navigation $nav): bool {
    return $nav
        && $nav->getImage() instanceof Icon
        && in_array($nav->getImage()->getRole(), [Icon::ROLE_ATTENTION, Icon::ROLE_STATUS_RED]);
};
?>

<? if (isset($flash['decline_inst'])) : ?>
    <?= QuestionBox::create(
        sprintf(
            _('Wollen Sie sich aus dem/der %s wirklich austragen?'),
            htmlReady($flash['name'])
        ),
        $controller->declineURL($flash['inst_id'], ['cmd' => 'kill', 'studipticket' => $flash['studipticket']]),
        $controller->declineURL($flash['inst_id'], ['cmd'=> 'back', 'studipticket' => $flash['studipticket']])
    ) ?>
<? endif ?>

<? if (empty($institutes)) : ?>
    <? if (!Config::get()->ALLOW_SELFASSIGN_INSTITUTE || $GLOBALS['perm']->have_perm("dozent")) : ?>
        <?=
        MessageBox::info(sprintf(_('Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zuständigen %sAdministratoren%s.'),
            '<a href="' . URLHelper::getLink('dispatch.php/siteinfo/show') . '">', '</a>'))?>
    <? else : ?>
        <?=
        MessageBox::info(sprintf(_('Sie haben sich noch keinen Einrichtungen zugeordnet.
           Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende %sOption%s unter "Persönliche Angaben - Studiendaten"
           auf Ihrer persönlichen Einstellungsseite.'), '<a href="' . URLHelper::getLink('dispatch.php/settings/studies#einrichtungen') . '">', '</a>'))?>
    <? endif ?>
<? else : ?>
    <table class="default" id="my_institutes">
        <caption><?= _('Meine Einrichtungen') ?></caption>
        <colgroup>
            <col style="width: 25px">
            <col>
            <col style="width: <?= $nav_elements * 32 ?>px">
            <col style="width: 45px">
        </colgroup>
        <thead>
        <tr>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Inhalt') ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($institutes as $values) : ?>
            <? $lastVisit = $values['visitdate']; ?>
            <? $instid = $values['institut_id'] ?>
            <tr>
                <td>
                    <?= InstituteAvatar::getAvatar($instid)->getImageTag(Avatar::SMALL, ['title' => $values['name']]) ?>
                </td>

                <td style="text-align: left">
                    <a href="<?= URLHelper::getLink('dispatch.php/institute/overview', ['auswahl' => $instid]) ?>">
                        <?= htmlReady($GLOBALS['INST_TYPE'][$values['type']]['name'] . ': ' . $values['name']) ?>
                    </a>
                </td>

                <td style="text-align: left; white-space: nowrap">
                <? if (!empty($values['navigation'])) : ?>
                    <ul class="my-courses-navigation">
                    <? foreach (MyRealmModel::array_rtrim($values['navigation']) as $key => $nav)  : ?>
                        <li class="my-courses-navigation-item <? if ($is_important($nav)) echo 'my-courses-navigation-important'; ?>">
                        <? if (isset($nav) && $nav->isVisible(true)) : ?>
                            <a href="<?=
                            UrlHelper::getLink('dispatch.php/institute/overview',
                                ['auswahl'     => $instid,
                                      'redirect_to' => strtr($nav->getURL(), '?', '&')]) ?>" <?= $nav->hasBadgeNumber() ? 'class="badge" data-badge-number="' . intval($nav->getBadgeNumber()) . '"' : '' ?>>
                                <?= $nav->getImage()->asImg(20, $nav->getLinkAttributes()) ?>
                            </a>
                        <? else: ?>
                            <span class="empty-slot" style="width: 20px"></span>
                        <? endif ?>
                    <? endforeach ?>
                    </li>
                <? endif ?>
                </td>

                <td style="text-align: left; white-space: nowrap">
                <? if (Config::get()->ALLOW_SELFASSIGN_INSTITUTE && $values['perms'] === 'user') : ?>
                    <a href="<?= $controller->decline_inst($instid) ?>">
                        <?= Icon::create('door-leave')->asImg(20, ['title' => _("aus der Einrichtung austragen")]) ?>
                    </a>
                <? else : ?>
                    <?= Assets::img('blank.gif', ['size' => '20']) ?>
                <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
