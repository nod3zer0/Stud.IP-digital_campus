<div class="blubber_course_info indented">
    <div class="headline">
        <div class="side">
            <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId()]) ?>">
                <?= htmlReady($course->name) ?>
            </a>
            <div class="icons">
                <ul class="my-courses-navigation">
                <? foreach ($icons as $icon) : ?>
                    <li class="my-courses-navigation-item <? if ($icon->getImage()->signalsAttention()) echo 'my-courses-navigation-important'; ?>">
                        <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId(), 'redirect_to' => $icon->getURL()]) ?>"<?= $icon->getTitle() ? ' title="'.htmlReady($icon->getTitle()).'"' : "" ?>>
                            <?= $icon->getImage()->asImg(20) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </div>
        </div>
    </div>
    <? if ($nextdate) : ?>
        <div>
            <h4><?= _('Nächster Termin') ?></h4>
            <a href="<?= URLHelper::getLink("dispatch.php/course/dates/details/".$nextdate->getId(), ['cid' => $course->id]) ?>" data-dialog="size=auto">
                <?= Icon::create('date')->asImg(['class' => "text-bottom"]) ?>
                <?= htmlReady($nextdate->getFullname()) ?>
            </a>
        </div>
    <? endif ?>
    <div>
        <? $sem_class = $course->getSemClass() ?>
        <h4><?= htmlReady($sem_class['title_dozent_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['dozent'][1]) ?></h4>
        <ol class="clean members">
            <? foreach ($teachers as $teacher) : ?>
                <li>
                    <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $teacher['username']]) ?>">
                        <?= Avatar::getAvatar($teacher['user_id'])->getImageTag(Avatar::SMALL) ?>
                        <?= htmlReady($teacher->getUserFullname()) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ol>
        <? if (count($tutors)) : ?>
            <h4><?= htmlReady($sem_class['title_tutor_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['tutor'][1]) ?></h4>
            <ol class="clean members">
                <? foreach ($tutors as $tutor) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $tutor['username']]) ?>">
                            <?= Avatar::getAvatar($tutor['user_id'])->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($tutor->getUserFullname()) ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ol>
        <? endif ?>
        <h4>
            <?= sprintf(_("%s %s und %s"), $students_count, $sem_class['title_tutor_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['autor'][1], $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['user'][1]) ?>
        </h4>
    </div>
</div>
<?= $this->render_partial("blubber/_tagcloud") ?>
<?= $this->render_partial('blubber/disable-notifications', compact('thread', 'unfollowed')) ?>
