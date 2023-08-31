<div class="formpart">
    <label<?= ($this->required ? ' class="studiprequired"' : '') ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
    </label>
    <select class="select2" v-model="<?= htmlReady($this->name) ?>" <?= ($this->required ? 'required aria-required="true"' : '') ?> id="<?= $id ?>" <?= $attributes ?>>
    <? foreach ($options as $key => $option) : ?>
        <option value="<?= htmlReady($key) ?>"<?= ($key == $value ? " selected" : "") ?>>
            <?= htmlReady($option) ?>
        </option>
    <? endforeach ?>
    </select>
</div>
