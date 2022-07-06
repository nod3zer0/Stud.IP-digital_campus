<?php
$_id = htmlReady(implode('_', (array) $area->getId()));
?>
<li id="lvgruppe_search_<?= $_id ?>" class="<?= TextHelper::cycle('odd', 'even') ?>">

   <?= Icon::create('arr_2left', Icon::ROLE_SORT)->asInput([
       'name'    => "assign[{$_id}]",
       'onclick' => "return STUDIP.MVV.CourseWizard.assignNode('{$_id}')",
       'class'   => in_array($_id, $values['studyareas'] ?: []) ? 'hidden-no-js' : '',
   ]) ?>

    <?= htmlReady($area->getDisplayName()) ?>

    <?= Icon::create('info')->asInput([
       'name'           => "lvgruppe_search[details][{$_id}]",
       'onclick'        => "return STUDIP.MVV.CourseWizard.showSearchDetails('{$_id}')",
       'class'          => '',
       'data-id'        => $_id,
       'data-course_id' => htmlReady($course_id),
   ]) ?>
</li>
