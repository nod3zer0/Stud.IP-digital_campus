<?php
/**
 * @var WikiPage $page
 * @var array $diffs
 */
?>
<h1><?= htmlReady($page->name) . ' - ' . _('Änderungsliste') ?></h1>

<div>
    <? foreach (array_reverse($diffs) as $diff) : ?>
        <?= $this->render_partial('course/wiki/versiondiff', $diff) ?>
    <? endforeach ?>
</div>
