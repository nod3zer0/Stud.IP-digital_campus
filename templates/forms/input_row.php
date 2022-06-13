<div class="hgroup">
    <? foreach ($parts as $part) : ?>
        <?= $part->renderWithCondition() ?>
    <? endforeach ?>
</div>
