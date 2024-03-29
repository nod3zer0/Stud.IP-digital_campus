<? use Studip\Button, Studip\LinkButton;?>

<form action="<?= $controller->url_for('course/members/set_autor_csv')?>" method="post" name="user" data-dialog class="default">
<?= CSRFProtection::tokenTag() ?>
    <h1>
        <?=sprintf(_('%s hinzufügen'), htmlReady(get_title_for_status('autor', 2)))?>
    </h1>
    <fieldset>
        <legend>
            <?=_('Teilnehmendenliste übernehmen')?>
        </legend>

        <label>
            <?=_('Eingabeformat')?>:

            <?= tooltipHtmlIcon(_('In das Textfeld <strong>Teilnehmendenliste übernehmen</strong> können Sie eine Liste mit Namen von Studierenden eingeben,
                die in die Veranstaltung aufgenommen werden sollen. Wählen Sie in der Auswahlbox das gewünschte Format, in dem Sie die Namen eingeben möchten.<br />
                <strong>Eingabeformat</strong><br/>
                <strong>Nachname, Vorname &crarr;</strong><br />Geben Sie dazu in jede Zeile den Nachnamen und (optional) den Vornamen getrennt durch ein Komma oder ein Tabulatorzeichen ein.<br />
                <strong>Nutzername &crarr;</strong><br />Geben Sie dazu in jede Zeile den Stud.IP Nutzernamen ein.<br />
                <strong>Email &crarr;</strong><br />Geben Sie dazu in jede Zeile die Email Adresse ein.'));?>

            <select name="csv_import_format">
                <option value="realname"><?=_("Nachname, Vorname")?> &crarr;</option>
                <option value="username"><?=_("Nutzername")?> &crarr;</option>
                <option value="email"><?=_("Email")?> &crarr;</option>
                <? if(!empty($accessible_df)) : ?>
                    <? foreach ($accessible_df as $df) : ?>
                        <option value="<?=$df->getId()?>" <?=(Request::get('csv_import_format') ==  $df->getId()? 'selected="selected"': '')?>><?= htmlReady($df->getName())?> &crarr;</option>
                    <? endforeach?>
                <? endif ?>
            </select>
        </label>

        <label>
            <?= sprintf(_('<strong>%s</strong> in die Veranstaltung eintragen'), htmlReady(get_title_for_status('autor', 2)))?></td>
            <textarea name="csv_import" rows="6" cols="50"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Eintragen'), 'add_member_list',
            ['title' => sprintf(_("als %s eintragen"), get_title_for_status('autor', 1))]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/members/index')) ?>
    </footer>
</form>
