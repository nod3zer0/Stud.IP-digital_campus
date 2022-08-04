<? foreach ($payload['sections'][0]['blocks'] as $block_id): ?>
    <? $block = $container->blocks->find($block_id); ?>
    <? if ($block): ?>
        <? $block_html_template = $block->type->getPdfHtmlTemplate(); ?>
        <? if ($block_html_template): ?>
            <?= $block_html_template->render(); ?>
        <? endif; ?>
    <? endif; ?>
<? endforeach ?>
