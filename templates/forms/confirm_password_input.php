<div class="formpart">
    <label <?= $this->required ? 'class="studiprequired"' : '' ?> for="<?= $id ?>">
        <span class="textlabel">
            <?= htmlReady($this->title) ?>
        </span>
        <? if ($this->required) : ?>
            <span class="asterisk" title="<?= _('Dies ist ein Pflichtfeld') ?>" aria-hidden="true">*</span>
        <? endif ?>
        <input type="password"
               v-model="<?= htmlReady($this->name) ?>"
               name="<?= htmlReady($this->name) ?>"
               value="<?= htmlReady($this->value) ?>"
               id="<?= $id ?>" <?= $this->required ? 'required aria-required="true"' : '' ?>
            <?= $attributes ?>>
    </label>
</div>
