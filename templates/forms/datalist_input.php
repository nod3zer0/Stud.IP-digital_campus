<div class="formpart">
    <label <?= $this->required ? 'class="studiprequired"' : '' ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
        <input type="text"
               list="<?= htmlReady($this->title) ?>"
               v-model="<?= htmlReady($this->name) ?>"
               name="<?= htmlReady($this->name) ?>"
               value="<?= htmlReady($this->value) ?>"
               id="<?= htmlReady($id) ?>"
               <?= $this->required ? 'required aria-required="true"' : '' ?>
            <?= $attributes ?>>
        <datalist class="" id="<?= htmlReady($this->title)  ?>">
            <? foreach ($options as $key => $option) : ?>
                <option value="<?= htmlReady($option) ?>"<?= ($option == $value ? " selected" : "") ?>>
                </option>
            <? endforeach ?>
        </datalist>
    </label>

</div>


