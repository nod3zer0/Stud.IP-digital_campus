<?php
/**
 * @var String $startId
 * @var String $show_as
 * @var String $treeTitle
 * @var String $breadcrumIcon
 * @var String $semester
 * @var String $semClass
 */
?>
<div data-studip-tree>
    <studip-tree start-id="<?= htmlReady($startId) ?>" view-type="<?= htmlReady($show_as) ?>" :visible-children-only="true"
                 title="<?= htmlReady($treeTitle) ?>" breadcrumb-icon="<?= htmlReady($breadcrumbIcon) ?>"
                 :with-search="true" :with-export="true" :with-courses="true" semester="<?= htmlReady($semester) ?>"
                 :sem-class="<?= htmlReady($semClass) ?>" :with-export="true"></studip-tree>
</div>
