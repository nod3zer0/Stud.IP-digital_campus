<? use Studip\Button; ?>
<? if ($count = count($help_contents)) : ?>
    <form action="<?= $controller->url_for('help_content/store_settings') ?>" method="post">
        <input type="hidden" name="help_content_searchterm" value="<?= $help_content_searchterm ?>">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default sortable-table">
            <caption>
                <?= sprintf(ngettext('%u Hilfe-Text', '%u Hilfe-Texte', $count), $count) ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Aktiv') ?></th>
                    <th data-sort="text"><?= _('Seite') ?></th>
                    <th data-sort="text"><?= _('Sprache') ?></th>
                    <th data-sort="text"><?= _('Stud.IP Version') ?></th>
                    <th><?= _('Inhalt') ?></th>
                    <th data-sort="htmldata"><?= _('Letzte Änderung') ?></th>
                    <th data-sort="htmldata"><?= _('Geändert von') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($help_contents as $help_content_id => $help_content) : ?>
                    <tr>
                        <td><input type="checkbox" name="help_content_status_<?= $help_content_id ?>"
                                   value="1" class="help_on"
                                <?= tooltip(_("Status der Hilfe (aktiv oder inaktiv)"), false) ?><?= $help_content->visible ? ' checked' : '' ?>>
                        </td>
                        <td>
                            <?= htmlReady($help_content->route) ?>
                            <? if ($help_content->comment) : ?>
                                <?= tooltipIcon($help_content->comment) ?>
                            <? endif ?>
                        </td>
                        <td><?= htmlReady($help_content->language) ?></td>
                        <td><?= htmlReady($help_content->studip_version) ?></td>
                        <td><?= formatReady($help_content->content) ?></td>
                        <td><?= $help_content->chdate ? date('d.m.Y H:i', $help_content->chdate) : '' ?></td>
                        <td>
                            <? if ($help_content->author) : ?>
                                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $help_content->author->username]) ?>" class="link-intern" title="<?= _('Zum Profil') ?>">
                                    <?= htmlReady($help_content->author->getFullName()) ?>
                                </a>
                            <? elseif ($help_content->author_email) : ?>
                                <a href="mailto:<?= htmlReady($help_content->author_email) ?>">
                                    <?= htmlReady($help_content->author_email) ?>
                                </a>
                            <? else : ?>
                                 <?= _('unbekannt') ?>
                            <? endif ?>
                        </td>
                        <td class="actions">
                            <a href="<?= URLHelper::getURL('dispatch.php/help_content/edit/' . $help_content_id) ?>" <?= tooltip(_('Hilfe-Text bearbeiten')) ?>
                               data-dialog="size=auto;reload-on-close">
                                <?= Icon::create('edit', 'clickable')->asImg() ?></a>
                            <a href="<?= URLHelper::getURL('dispatch.php/help_content/delete/' . $help_content_id) ?>" <?= tooltip(_('Hilfe-Text löschen')) ?>
                               data-dialog="size=auto;reload-on-close">
                                <?= Icon::create('trash', 'clickable')->asImg() ?></a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <?= Button::createAccept(_('Speichern'), 'save_help_content_settings') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
<? else : ?>
    <?= _('Keine Hilfe-Texte vorhanden.') ?>
<? endif ?>
