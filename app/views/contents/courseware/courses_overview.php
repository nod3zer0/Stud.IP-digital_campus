<div class="cw-content-projects">
    <? if (empty($sem_courses)) : ?>
        <? if (!$all_semesters) : ?>
        <h2>
            <?= htmlReady($semesters[0]->name) ?>
            <? if ($current_semester->id == $semesters[0]->id) : ?>
                (<?= _('Aktuelles Semester') ?> )
            <? endif ?>
        </h2>
        <? endif ?>

        <?= MessageBox::info(_('Keine der Veranstaltungen auf die sie Zugriff '
            . 'haben hat eine Courseware mit Inhalten.')); ?>
    <? else : ?>
    <? foreach($semesters as $semester) :?>
        <? if (!empty($sem_courses[$semester->id]['coursewares'])): ?>
        <h2>
            <?= htmlReady($semester->name) ?>
            <? if ($current_semester->id == $semester->id) : ?>
                (<?= _('Aktuelles Semester') ?> )
            <? endif ?>
        </h2>
        <ul class="cw-tiles">
            <? foreach($sem_courses[$semester->id]['coursewares'] as $element) :?>
                <li class="tile <?= htmlReady($element['payload']['color'])?>">
                    <a href="<?= URLHelper::getLink('dispatch.php/course/courseware/?cid='.$element['range_id'].'#/structural_element/'.$element['id']) ?>">
                        <? if ($element->getImageUrl() === null) : ?>
                            <div class="preview-image default-image"></div>
                        <? else : ?>
                            <div class="preview-image" style="background-image: url(<?= htmlReady($element->getImageUrl()) ?>)" ></div>
                        <? endif; ?>
                        <div class="description">
                            <header><?= htmlReady($element['title']) ?></header>
                            <div class="description-text-wrapper">
                                <p>
                                    <?= htmlReady($element['payload']['description']) ?>
                                </p>
                            </div>
                            <footer>
                                <?= Icon::create('seminar', Icon::ROLE_INFO_ALT)?> <?= htmlReady($element['course']['name'])?>
                            </footer>
                        </div>
                    </a>
                </li>
            <? endforeach; ?>
        </ul>
        <? endif; ?>
    <? endforeach; ?>
    <? endif; ?>
</div>
