<?php
if (!$selected_terms_of_use_id) {
    $selected_terms_of_use_id = ContentTermsOfUse::findDefault()->id;
}
?>
<? if ($content_terms_of_use_entries) : ?>
<fieldset>
    <legend><?= _('Lizenzauswahl') ?></legend>
    <div style="margin-bottom: 1ex;">
        <?= _('Bereitgestellte Dateien können heruntergeladen und ggf. weiterverbreitet werden.
               Dabei ist das Urheberrecht sowohl beim Hochladen der Datei als auch bei der Nutzung
               zu beachten. Bitte geben Sie daher an, um welche Art von Bereitstellung es sich handelt.
               Diese Angabe dient mehreren Zwecken: Beim Herunterladen wird ein Hinweis angezeigt,
               welche Nutzung der Datei zulässig ist. Beim Hochladen stellt die Angabe eine
               Entscheidungshilfe dar, damit Sie sichergehen können, dass die Datei tatsächlich
               bereitgestellt werden darf.') ?>
    </div>
    <fieldset class="select_terms_of_use">
    <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
        <input type="radio" name="content_terms_of_use_id"
               value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
               id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
               <? if ($content_terms_of_use_entry->id == $selected_terms_of_use_id) echo 'checked'; ?>
               aria-description="<?= htmlReady(kill_format($content_terms_of_use_entry->description)) ?>">

        <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
            <div class="icon">
            <? if ($content_terms_of_use_entry['icon']) : ?>
                <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)): ?>
                    <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="32" height="32">
                <? else : ?>
                    <?= Icon::create($content_terms_of_use_entry['icon'], Icon::ROLE_CLICKABLE)->asImg(32) ?>
                <? endif ?>
            <? endif ?>
            </div>
            <div class="text">
                <?= htmlReady($content_terms_of_use_entry->name) ?>
            </div>
            <?= Icon::create('arr_1down', Icon::ROLE_CLICKABLE)->asImg(24, ['class' => 'arrow']) ?>
            <?= Icon::create('check-circle', Icon::ROLE_CLICKABLE)->asImg(32, ['class' => 'check']) ?>
        </label>

        <? if (trim($content_terms_of_use_entry->description)): ?>
            <div class="terms_of_use_description">
                <div class="description">
                    <?= formatReady($content_terms_of_use_entry->description ?: _('Keine Beschreibung')) ?>
                </div>
            </div>
        <? endif ?>
    <? endforeach ?>
    </fieldset>
</fieldset>
<? endif; ?>
