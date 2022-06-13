<fieldset>
    <? if ($legend) : ?>
        <legend><?= htmlReady($this->legend) ?></legend>
    <? endif ?>
    <? foreach ($parts as $part) : ?>
        <?= $part->renderWithCondition() ?>
    <? endforeach ?>
</fieldset>
