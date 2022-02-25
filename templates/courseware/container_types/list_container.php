<h3><?= sprintf(_('Container-Typ: %s'), htmlReady($title)) ?></h3>
<? foreach ($payload['sections'][0]['blocks'] as $block_id): ?>
    <? $block = $container->blocks->find($block_id); ?>
    <? if ($block): ?>
        <? $block_html_template = $block->type->getPdfHtmlTemplate(); ?>
        <? if ($block_html_template): ?>
            <?= $block_html_template->render(); ?>
        <? endif; ?>
    <? else: ?>
        <p><?= _('Block konnte nicht gefunden werden') ?></p>
    <? endif; ?>
<? endforeach ?>
