<? if ($admin || $termine) : ?>
    <article class="studip">
        <header>
            <h1>
                <?= Icon::create('schedule', 'info')->asImg() ?>
                <?= htmlReady($title) ?>
            </h1>
            <nav>
                <? if ($admin) : ?>
                    <? if ($isProfile) : ?>
                        <a href="<?= URLHelper::getLink('dispatch.php/calendar/date/add') ?>"
                           data-dialog="reload-on-close"
                           title="<?= _('Neuen Termin anlegen') ?>">
                            <?= Icon::create('add', 'clickable')->asImg(['class' => 'text-bottom']) ?>
                        </a>
                    <? else: ?>
                        <a href="<?= URLHelper::getLink("dispatch.php/course/timesrooms", ['cid' => $range_id]) ?>"
                           title="<?= _('Neuen Termin anlegen') ?>">
                            <?= Icon::create('admin', 'clickable')->asImg(['class' => 'text-bottom']) ?>
                        </a>
                    <? endif ?>
                <? endif ?>
            </nav>
        </header>
        <? if ($termine) : ?>
            <? foreach ($termine as $termin) : ?>
                <?= $this->render_partial('calendar/contentbox/_termin.php', ['termin' => $termin, 'course_range' => $course_range]) ?>
            <? endforeach ?>
        <? else: ?>
            <section>
                <?= _('Es sind keine aktuellen Termine vorhanden. Zum Anlegen neuer Termine können Sie die Aktion „Neuen Termin anlegen“ benutzen.') ?>
            </section>
        <? endif ?>
    </article>
<? endif ?>
