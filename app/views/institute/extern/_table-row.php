<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 */
?>

<tr>
    <td>
        <strong><a href="<?= $controller->link_for('/edit', $config->type, $config->id) ?>">
            <?= htmlReady($config->name) ?>
            <?= isset($config->conf['not_fixed_after_migration']) ? '(!)' : '' ?>
            </a></strong>
    </td>
    <td>
        <small><?= htmlReady($config->description) ?></small>
    </td>
    <td data-sort-value="<?= (int) $config->chdate ?>">
        <?= date('d.m.Y G:H', $config->chdate) ?>
    </td>
    <td>
        <? if ($config->author) : ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $config->author->username]) ?>">
                <?= htmlReady($config->author->getFullName()) ?>
            </a>
        <? else : ?>
            <?= _('unbekannt') ?>
        <? endif ?>
    </td>
    <td class="actions">
        <?= ActionMenu::get()->addLink(
                $controller->editURL($config->type, $config->id),
                _('Konfiguration bearbeiten'),
                Icon::create('edit')
            )->addLink(
                $controller->infoURL($config->id),
                _('Informationen anzeigen'),
                Icon::create('infopage'),
                ['data-dialog' => '']
           )->addLink(
               $controller->downloadURL($config->id),
               _('Konfiguration herunterladen'),
               Icon::create('download')
           )->addButton(
               'delete',
               _('Konfiguration löschen'),
               Icon::create('trash'),
               [
                   'formaction'   => $controller->deleteURL($config->id),
                   'title'        => _('Konfiguration löschen'),
                   'data-confirm' => sprintf(_('Soll die Konfiguration "%s" gelöscht werden?'), htmlReady($config->name)),
                   'form'         => 'extern-page-index'
               ]
           )->render() 
       ?>
    </td>
</tr>
