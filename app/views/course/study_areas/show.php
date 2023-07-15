<? if (!$locked) : ?>
<form action="<?= $controller->link_for('course/study_areas/save/' . $course->id, $url_params) ?>"
    <?= Request::isDialog() ? 'data-dialog' : '' ?>
      method="post" class="default">
    <? endif ?>
    <?= $tree ?>
    <? if (!$locked) : ?>
</form>
<? endif ?>
