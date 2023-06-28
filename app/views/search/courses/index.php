<?php
/**
 * @var String $startId
 * @var String $nodeClass
 */
?>
<div data-studip-tree>
    <studip-tree start-id="<?= htmlReady($startId) ?>" view-type="list" :visible-children-only="true"
                 title="<?= htmlReady($treeTitle) ?>" breadcrumb-icon="<?= htmlReady($breadcrumbIcon) ?>"
                 :with-search="true" :with-export="true" :with-courses="true" semester="<?= htmlReady($semester) ?>"
                 :sem-class="<?= htmlReady($semClass) ?>" :with-export="true"></studip-tree>
</div>
