<div class="formpart">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <studip-wysiwyg
                   id="<?= $id ?>"
                   v-model="<?= htmlReady($name) ?>"
                   value="<?= htmlReady($value) ?>"
                   <?= $required ? 'required' : '' ?>>
    </studip-wysiwyg>
    </div>
