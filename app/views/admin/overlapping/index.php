<?php
/**
 * @var SimpleORMapCollection $conflicts
 * @var array $semtypes
 * @var array $fachsems
 */
?>
<?= $this->render_partial('admin/overlapping/selection', ['fachsems' => $fachsems, 'semtypes' => $semtypes]) ?>
<? if (count($conflicts)) : ?>
    <?= $this->render_partial('admin/overlapping/overlapping') ?>
<? endif; ?>
<script>
    STUDIP.Overlapping.init();
</script>
