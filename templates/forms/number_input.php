<label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
    <span class="textlabel">
        <?= htmlReady($this->title) ?>
    </span>
    <? if ($this->required) : ?>
        <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
    <? endif ?>
</label>
<input type="number"
       name="<?= htmlReady($name) ?>"
       value="<?= htmlReady($value) ?>"
       id="<?= $id ?>"
       <?= ($required ? 'required aria-required="true"' : '') ?>
       <?= $attributes ?>>
