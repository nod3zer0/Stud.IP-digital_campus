<? if (!$locked) : ?>
    <form action="<?= $controller->link_for('course/study_areas/save/' . $course->id, $url_params) ?>"
          <?= Request::isDialog() ? 'data-dialog' : '' ?>
          method="post">
<? endif?>
    <?= $tree ?>
    <div style="text-align: center;">
    <? if ($is_activated) : ?>
        <? if ($is_required) : ?>
            <?= _("Die Veranstaltung muss <b>mindestens einen</b> Studienbereich haben.") ?>
        <? endif ?>
    <? else : ?>
        <?= _("Die Veranstaltung darf <b>keine</b> Studienbereiche haben.") ?>
    <? endif ?>
    </div>
<? if(!$locked) : ?>
    </form>
<? endif ?>
