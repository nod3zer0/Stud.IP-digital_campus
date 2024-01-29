<article class="studip toggle <?= ContentBoxHelper::classes($termin->getObjectId()) ?>"
         id="<?= htmlReady($termin->getObjectId()) ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href($termin->getObjectId()) ?>">
                <?= Icon::create('date', Icon::ROLE_INACTIVE)->asImg(['class' => 'text-bottom']) ?>
                <?= htmlReady($titles[$termin->getObjectId()] ?? $termin->getTitle()) ?>
            </a>
        </h1>
        <nav>
            <span>
                <?= $termin->getLocation() ? _('Raum') . ': ' . formatLinks($termin->getLocation()) : '' ?>
            </span>
            <? if ($admin && $isProfile && $termin->getObjectClass() === 'CalendarDateAssignment') : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/calendar/calendar') ?>"
                   title="<?= _('Zum Kalender') ?>">
                    <?= Icon::create('schedule')->asImg(['class' => 'text-bottom']) ?>
                </a>
                <? if ($termin->calendar_date->isWritable($GLOBALS['user']->id)) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/calendar/date/edit/' . $termin->getPrimaryObjectId()) ?>"
                       title="<?= _('Termin bearbeiten') ?>"
                       data-dialog>
                        <?= Icon::create('edit')->asImg(['class' => 'text-bottom']) ?>
                    </a>
                <? endif ?>
            <? elseif (!$course_range && in_array($termin->getObjectClass(), ['CourseDate', 'CourseExDate'])) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/course/dates', ['cid' => $termin->getPrimaryObjectId()]) ?>"
                   title="<?= _('Zur Veranstaltung') ?>">
                    <?= Icon::create('seminar')->asImg(['class'=> 'text-bottom']) ?>
                </a>
            <? endif ?>
        </nav>
    </header>
    <div>
        <?
        $themen = [];
        if ($termin instanceof CourseDate) {
            $themen = $termin->topics->toArray('title description');
        }
        $description = '';
        if ($termin instanceof CourseExDate) {
            $description = $termin->content;
        } elseif ($termin instanceof CourseDate && isset($termin->cycle)) {
            $description = $termin->cycle->description;
        } else {
            $description = $termin->getDescription();
        }
        ?>
        <? if ($description || count($themen) > 0) : ?>
            <p><?= formatReady($description) ?></p>
            <? if (count($themen)) : ?>
                <? foreach ($themen as $thema) : ?>
                    <h3>
                        <?= Icon::create('topic', Icon::ROLE_INFO)->asImg(20, ['class' => "text-bottom"]) ?>
                        <?= htmlReady($thema['title']) ?>
                    </h3>
                    <div>
                        <?= formatReady($thema['description']) ?>
                    </div>
                <? endforeach ?>
            <? endif ?>
        <? else : ?>
            <?= _('Keine Beschreibung vorhanden') ?>
        <? endif ?>
        <ul class="list-csv" style="text-align: center;">
            <? foreach ($termin->getAdditionalDescriptions() as $type => $info) : ?>
                <? if (trim($info)) : ?>
                    <li>
                        <small>
                            <? if (!is_numeric($type)): ?>
                                <em><?= htmlReady($type) ?>:</em>
                            <? endif; ?>
                            <?= htmlReady(trim($info)) ?>
                        </small>
                    </li>
                <? endif ?>
            <? endforeach ?>
        </ul>
        <? if (!$course_range && in_array($termin->getObjectClass(), [CourseDate::class, CourseExDate::class])) : ?>
            <div>
                <a href="<?= URLHelper::getLink('dispatch.php/course/dates', ['cid' => $termin->getPrimaryObjectId()]) ?>">
                    <?= Icon::create('link-intern')->asImg(['class'=> 'text-bottom']) ?>
                    <?= _('Zur Veranstaltung') ?>
                </a>
            </div>
        <? endif ?>
    </div>
</article>
