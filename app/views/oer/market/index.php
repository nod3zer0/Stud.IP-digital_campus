<?php
/**
 * @var Oer_MarketController $controller
 * @var array $material_data
 * @var array $tags
 * @var OERMaterial[] $new_ones
 */
?>
<form class="oer_search"
      action="<?= $controller->search() ?>"
      method="GET" aria-live="polite"
      data-searchresults="<?= htmlReady(json_encode($material_data)) ?>"
      data-filteredtag="<?= htmlReady(Request::get('tag')) ?>"
      data-filteredcategory="<?= htmlReady(Request::get('category')) ?>"
      data-tags="<?= htmlReady(json_encode($tags)) ?>"
      data-material_select_url_template="<?= htmlReady($controller->detailsURL('__material_id__')) ?>">
    <?= $this->render_partial('oer/market/_searchform') ?>
</form>


<? if (!empty($new_ones)) : ?>
    <div id="new_ones">
        <h2><?= _('Neuste Materialien') ?></h2>
        <ul class="oer_material_overview">
            <?= $this->render_partial('oer/market/_materials.php', ['materialien' => $new_ones]) ?>
        </ul>
    </div>
<? endif ?>






<?
