<label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
    <span class="textlabel">
        <?= htmlReady($this->title) ?>
    </span>
    <? if ($this->required) : ?>
        <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
    <? endif ?>
</label>
<range-input v-model="<?= htmlReady($name) ?>"
             name="<?= htmlReady($name) ?>"
             value="<?= htmlReady($value) ?>"
             id="<?= $id ?>"
             <?= $attributes ?>></range-input>
