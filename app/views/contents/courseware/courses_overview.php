<div class="cw-content-projects">
    <? foreach($semesters as $semester) :?>
        <h2><?= htmlReady($semester['semester_name']) ?></h2>
        <? if (!empty($semester['coursewares'])): ?>
        <ul class="cw-tiles">
            <? foreach($semester['coursewares'] as $element) :?>
                <li class="tile <?= htmlReady($element['payload']['color'])?>">
                    <a href="<?= URLHelper::getLink('dispatch.php/course/courseware/?cid='.$element['range_id'].'#/structural_element/'.$element['id']) ?>">
                        <div class="preview-image" style="background-image: url(<?= htmlReady($element->getImageUrl()) ?>)" ></div>
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
        <? if (empty($semester['coursewares']) && !empty($empty_courses)): ?>
            <?= MessageBox::info(_('Es wurden noch keine Lernunterlagen angelegt.')); ?>
        <? endif; ?>
        <? if (empty($semester['coursewares']) && empty($empty_courses)): ?>
            <?= MessageBox::info(_('Es wurden keine Veranstaltungen gefunden.')); ?>
        <? endif; ?> 
    <? endforeach; ?>
</div>
