<?php
# Lifter010: TODO
/**
 * @var Admin_PluginController $controller
 * @var array $num_updates
 * @var array $plugins
 * @var array $plugin_types
 * @var string $plugin_filter
 * @var string $core_filter
 */
use Studip\Button, Studip\LinkButton;

?>
<? if ($num_updates): ?>
    <?= MessageBox::info($this->render_partial('admin/plugin/update_info')) ?>
<? endif ?>

<? if (count($plugins) == 0): ?>
    <?= MessageBox::info(_('Es sind noch keine Plugins in diesem Stud.IP vorhanden.'), [
        _('Sie können Plugins aus dem Marktplatz installieren oder manuell hochladen.'),
        sprintf(
            _('Benutzen Sie dafür die Funktion "%sweitere Plugins installieren%s" in der Info-Box.'),
            '<a href="' . $controller->url_for('admin/plugin/search') . '">',
            '</a>'
        )
    ]) ?>
<? else: ?>
    <form action="<?= $controller->url_for('admin/plugin/save') ?>" method="post" class="default">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
        <input type="hidden" name="plugin_filter" value="<?= $plugin_filter ?>">

        <table class="default">
            <caption>
                <div class="actions" title="<?= _('Gesamt (aktiv/inaktiv)') ?>">
                    <?= sprintf(
                        _('%u Plugins (%u/%u)'),
                        count($plugins),
                        count(array_filter($plugins, function ($p) { return $p['enabled']; })),
                        count(array_filter($plugins, function ($p) { return !$p['enabled']; }))
                    ) ?>
                </div>
                <?= _('Verwaltung von Plugins') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Aktiv') ?></th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Typ') ?></th>
                    <th><?= _('Version') ?></th>
                    <th><?= _('Schema') ?></th>
                    <th><?= _('Position') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($plugins as $plugin): ?>
                    <? $pluginid = $plugin['id'] ?>
                    <tr>
                        <td style="padding-left: 1ex;" width="30">
                            <input type="checkbox" name="enabled_<?= $pluginid ?>"
                                   value="1" <? if ($plugin['enabled']) echo 'checked'; ?>>
                        </td>
                        <td>
                            <a data-dialog="size=auto" href="<?= $controller->url_for('admin/plugin/manifest/' . $pluginid) ?>">
                                <?= htmlReady($plugin['name']) ?>
                            <? if ($plugin['core']): ?>
                                <em>(<?= _('Kern-Plugin') ?>)</em>
                            <? endif; ?>
                            </a>
                        </td>
                        <td <? if (!$plugin['enabled']) echo 'class="quiet"'; ?>>
                            <?= join(', ', $plugin['type']) ?>
                        </td>
                        <td <? if (!$plugin['enabled']) echo 'class="quiet"'; ?>>
                            <?= htmlReady($update_info[$pluginid]['version'] ?? '') ?>
                        <? if ($plugin['automatic_update_url']): ?>
                            <?= Icon::create('install', Icon::ROLE_STATUS_RED)->asImg([
                                'title' => _('Automatische Updates sind eingerichtet'),
                                'style' => 'vertical-align: text-bottom',
                            ]) ?>
                        <? endif; ?>
                        </td>
                        <td <? if (!$plugin['enabled']) echo 'class="quiet"'; ?>>
                        <? if (!$plugin['depends']) : ?>
                            <?= htmlReady($migrations[$pluginid]['schema_version'] ?? '') ?>
                            <? if (!empty($migrations[$pluginid]['pending_migrations'])): ?>
                                <a href="<?= $controller->url_for('admin/plugin/migrate/' . $pluginid) ?>"
                                   title="<?= sprintf(_('%d ausstehende Migration(en)'), $migrations[$pluginid]['pending_migrations']) ?>">
                                    <?= Icon::create('plugin', Icon::ROLE_STATUS_RED) ?>
                                </a>
                            <? endif; ?>
                        <? endif; ?>
                        </td>
                        <td>
                            <input name="position_<?= $pluginid ?>" type="text" size="2"
                                   value="<?= $plugin['position'] ?>" <? if (!$plugin['enabled']) echo 'disabled'; ?>>
                        </td>
                        <td class="actions">
                            <? $actionMenu = ActionMenu::get()->setContext($plugin['name']) ?>
                            <? $actionMenu->addLink(
                                $controller->url_for('admin/role/assign_plugin_role/' . $pluginid),
                                _('Zugriffsrechte bearbeiten'),
                                Icon::create('edit', 'clickable', ['title' => _('Zugriffsrechte bearbeiten')])
                            ) ?>
                            <?
                            if (in_array('StudipModule', $plugin['type'])) {
                                $actionMenu->addLink(
                                    $controller->url_for('admin/plugin/edit_description/' . $pluginid),
                                    _('Beschreibung und Hervorhebung'),
                                    Icon::create('infopage', Icon::ROLE_CLICKABLE, ['title' => _('Beschreibung und Hervorhebung')]),
                                    ['data-dialog' => 'size=big']
                                );
                            }
                            ?>

                            <? if (!$plugin['depends'] && isset($update_info[$pluginid]['version']) && !$plugin['core']): ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('admin/plugin/edit_automaticupdate/' . $pluginid),
                                    $plugin['automatic_update_url'] ? _('Automatisches Update verwalten (eingerichtet)') : _('Automatisches Update verwalten'),
                                    Icon::create('install', $plugin['automatic_update_url'] ? 'attention' : 'clickable', [
                                        'title' => $plugin['automatic_update_url']
                                                 ? _('Automatisches Update verwalten (eingerichtet)')
                                                 : _('Automatisches Update verwalten')
                                    ]),
                                    ['data-dialog' => 'size=auto;reload-on-close']
                                ) ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('admin/plugin/download/' . $pluginid),
                                    _('Herunterladen'),
                                    Icon::create('download', 'clickable', ['title' => _('Herunterladen')])
                                ) ?>
                            <? endif ?>
                            <? if (!$plugin['depends'] && !$plugin['core']): ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('admin/plugin/ask_delete/' . $pluginid),
                                    _('Deinstallieren'),
                                    Icon::create('trash', 'clickable', ['title' => _('Deinstallieren')])
                                ) ?>
                            <? endif ?>
                            <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: center;" colspan="7">
                        <?= Button::createAccept(_('Speichern'), 'save', ['title' => _('Einstellungen speichern')]) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
<? endif ?>

<?
$sidebar = Sidebar::Get();

if (Config::get()->PLUGINS_UPLOAD_ENABLE) {
    $uploadArea = $sidebar->addWidget(new SidebarWidget());
    $uploadArea->setTitle(_('Plugin als ZIP-Datei hochladen'));
    $uploadArea->addElement(new WidgetElement(
        $this->render_partial('admin/plugin/upload-drag-and-drop'))
    );

    $sidebar->addWidget(new ActionsWidget())->addLink(
        _('Plugin von URL installieren'),
        $controller->url_for('admin/plugin/edit_automaticupdate'),
        Icon::create('download')
    )->asDialog();
}

$widget = $sidebar->addWidget(new OptionsWidget());
$widget->setTitle(_('Darstellungseinstellungen'));
$widget->addSelect(
    _('Darstellung einschränken'),
    $controller->url_for('admin/plugin'),
    'plugin_filter',
    array_merge(
        ['' => _('Alle Plugin-Typen anzeigen')],
        array_combine($plugin_types, $plugin_types)
    ),
    $plugin_filter
);
$widget->addRadioButton(
    _('Alle Plugins anzeigen'),
    $controller->url_for('admin/plugin?core_filter=yes'),
    ($core_filter ?: 'yes') === 'yes'
);
$widget->addRadioButton(
    _('Kern-Plugins ausblenden'),
    $controller->url_for('admin/plugin?core_filter=no'),
    $core_filter === 'no'
);
$widget->addRadioButton(
    _('Nur Kern-Plugins anzeigen'),
    $controller->url_for('admin/plugin?core_filter=only'),
    $core_filter === 'only'
);
if ($plugin_filter || ($core_filter ?: 'yes') !== 'yes') {
    $widget->addElement(new WidgetElement('<hr>'));
    $widget->addElement(new LinkElement(
        _('Zurücksetzen'),
        $controller->url_for('admin/plugin?reset_filter=1'),
        Icon::create('refresh'),
        ['class' => 'options-radio']
    ));
}
