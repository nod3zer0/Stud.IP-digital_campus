<div class="formpart">
    <label <?= $this->required ? 'class="studiprequired"' : '' ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>

        <input type="text" list="<?= $this->title ?>" id="" v-model="<?= htmlReady($this->name) ?>" <?= $this->required ? 'required aria-required="true"' : '' ?> />

        <datalist class="" id="<?= $this->title ?>" <?= $attributes ?>>
            <? foreach ($options as $key => $option) : ?>
                <option value="<?= htmlReady($option) ?>"<?= ($option == $value ? " selected" : "") ?>>
                </option>
            <? endforeach ?>
        </datalist>
    </label>

</div>


