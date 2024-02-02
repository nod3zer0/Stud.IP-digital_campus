<div class="formpart">
    <section <?= $this->orientation == 'horizontal' ? 'class="hgroup"' : '' ?> id="<?= htmlReady($id) ?>">
    <span class="textlabel">
        <?= htmlReady($this->title) ?>
    </span>

    <? foreach ($options as $key => $option) : ?>
        <label class="" <?= $attributes ?>>
                <input type="radio"
                       name="<?= htmlReady($this->name) ?>"
                       v-model="<?= htmlReady($this->name) ?>"
                       value="<?= htmlReady($key) ?>" <?= $key == $value ? 'checked' : '' ?>>
                    <?= htmlReady($option) ?>
        </label>
    <? endforeach ?>
</section>
</div>
