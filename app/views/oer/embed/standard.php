<? if ($url) : ?>
    <a href="<?= htmlReady($url) ?>">
<? else : ?>
    <a href="<?= htmlReady($source_url) ?>" target="_blank">
<? endif ?>
    <?= Icon::create('oer-campus')->asImg(['class' => 'text-bottom']) ?>
    <?= htmlReady($material['name']) ?>
</a>
