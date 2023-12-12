<div class="formpart" data-form-input-for="<?= htmlReady($name) ?>">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <i18n-textarea type="textarea"
                   id="<?= $id ?>"
                   name="<?= htmlReady($this->name) ?>"
                   value="<?= htmlReady($value) ?>"
                   <?= $required ? 'required' : '' ?>
                   @selectlanguage="(language_id) => selectLanguage('<?= htmlReady($this->name) ?>', language_id)"
                   @allinputs="setInputs">
    </i18n-textarea>
</div>
