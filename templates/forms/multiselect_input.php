<div class="formpart">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <multiselect name="<?= htmlReady($name) ?>[]" <?= ($required ? 'required aria-required="true"' : '') ?>
                 :options="<?= htmlReady(json_encode($options)) ?>"
                 :value="<?= htmlReady(json_encode($value)) ?>"
                 v-model="<?= htmlReady($name) ?>"
                 id="<?= $id ?>"
                 <?= $attributes ?>>
    </multiselect>
</div>
