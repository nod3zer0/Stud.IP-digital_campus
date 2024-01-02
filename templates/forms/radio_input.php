<div class="formpart">
    <section <?= $this->orientation == 'horizontal' ? 'class="hgroup"' : '' ?> for="<?= $id ?>">
    <span class="textlabel">
        <?= htmlReady($this->title) ?>
    </span>

    <? foreach ($options as $key => $option) : ?>
        <label class="" <?= $attributes ?>>
                <input type="radio"
                       v-model="<?= htmlReady($this->name) ?>"
                       value="<?= htmlReady($key) ?>" <?= $key == $value ? 'checked' : '' ?>>
                    <?= htmlReady($option) ?>
        </label>
    <? endforeach ?>
</section>
</div>
