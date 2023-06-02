<div class="formpart">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <i18n-textarea type="<?= \Config::get()->WYSIWYG ? 'wysiwyg' : 'textarea' ?>"
                   id="<?= $id ?>"
                   name="<?= htmlReady($name) ?>"
                   value="<?= htmlReady($value) ?>"
                   @allinputs="setInputs"
                   :wysiwyg_disabled="<?= \Config::get()->WYSIWYG ? 'false' : 'true' ?>" <?= $required ? 'required' : '' ?>>
    </i18n-textarea>
    </div>
