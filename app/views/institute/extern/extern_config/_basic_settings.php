<?php
/**
 * @var ExternPageConfig $config
 * @var ExternPage $page
 */
?>

<fieldset>
    <legend>
        <?= _('Allgemeine Daten') ?>
    </legend>
    <label class="studiprequired" for="external_page_config_name">
        <span class="textlabel">
            <?= _('Name der Konfiguration') ?>
        </span>
        <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <input type="text"
               id="external_page_config_name"
               value="<?= htmlReady($config->name) ?>"
               required
               name="name"
               maxlength="255">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description"><?= htmlReady($config->description) ?></textarea>
    </label>
    <label>
        <?= _('Ausgabesprache') ?>
        <select name="language">
            <? foreach (Config::get()->CONTENT_LANGUAGES as $language_id => $language_data) : ?>
                <option value="<?= $language_id ?>"
                    <?= $page->language === $language_id ? ' selected' : '' ?>>
                    <?= htmlReady($language_data['name']) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Automatisches Umkodieren der Inhalte') ?>
        <?
        $escaping_types = [
            _('Keine Umkodierung vornehmen') => '',
            _('html-konform umkodieren')     => 'htmlReady',
            _('xml-konform umkodieren')      => 'xml',
            _('json-konform umkodieren')     => 'json'
        ]
        ?>
        <select name="escaping">
            <? foreach ($escaping_types as $escaping_name => $escaping_type) : ?>
                <option value="<?= $escaping_type ?>"
                    <?= $page->escaping === $escaping_type ? ' selected' : '' ?>>
                    <?= htmlReady($escaping_name) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
</fieldset>
