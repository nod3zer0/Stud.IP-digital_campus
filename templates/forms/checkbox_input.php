<input type="hidden" name="<?= htmlReady($name) ?>" value="0">
    <label<?= ($required ? ' class="studiprequired"' : '') ?>>
    <input type="checkbox"
           v-model="<?= htmlReady($this->name) ?>"
           name="<?= htmlReady($this->name) ?>"
           value="1"
           id="<?= $id ?>"
           <?= ($this->required ? 'required aria-required="true"' : '') ?>
           <?= $attributes ?>>
    <span class="textlabel">
        <?= htmlReady($this->title) ?>
    </span>
    <? if ($this->required) : ?>
        <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
    <? endif ?>
</label>
