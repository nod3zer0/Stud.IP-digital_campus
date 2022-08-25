<div style="text-align: right;">
    <a href="<?= URLHelper::getLink("dispatch.php/oer/market/details/{$id}") ?>"
       title="<?= htmlReady(_('Zum OER Campus wechseln')) ?>">
        <?= Icon::create('oer-campus')->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($material['name']) ?>
    </a>
</div>
