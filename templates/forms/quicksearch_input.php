<div class="formpart" data-form-input-for="<?= htmlReady($name) ?>">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <span>
        <quicksearch value="<?= htmlReady($value) ?>"
                     name="<?= htmlReady($name) ?>"
                     id="<?= $id ?>"
                     <?= ($this->required ? 'required aria-required="true"' : '') ?>
                     <?= $attributes ?>>
    </span>'
</div>
