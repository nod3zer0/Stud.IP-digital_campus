<?php
/**
 * @var Navigation[] $navigation
 * @var Navigation[] $footer
 * @var Navigation[] $quicklinks
 */
?>
<h2><?= _('Hauptnavigation') ?></h2>
<?= $this->render_partial('sitemap/navigation', ['navigation' => $navigation, 'needs_image' => true, 'style' => 'bold'])
?>
<h2><?= _('Zusatznavigation') ?></h2>
<?= $this->render_partial('sitemap/navigation', ['navigation' => $quicklinks, 'needs_image' => false, 'style' => 'bold'])
?>
<h2><?= _('Fußzeile') ?></h2>
<?= $this->render_partial('sitemap/navigation', ['navigation' => $footer, 'needs_image' => false, 'style' => 'bold'])
?>
