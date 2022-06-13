<div class="formpart">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <i18n-textarea type="text"
                   id="<?= $id ?>"
                   name="<?= htmlReady($this->name) ?>"
                   value="<?= htmlReady($value) ?>"
                   <?= $required ? 'required' : '' ?>
                   @allinputs="setInputs">
    </i18n-textarea>
</div>
