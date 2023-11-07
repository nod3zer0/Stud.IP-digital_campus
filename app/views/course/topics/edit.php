<? $date_ids = $topic->dates->pluck("termin_id") ?>
<form action="<?= $controller->store($topic) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <input type="hidden" name="open" value="<?= htmlReady($topic->getId()) ?>">
    <input type="hidden" name="edit" value="1">

    <fieldset>
        <legend><?= _('Thema bearbeiten') ?></legend>

        <label>
            <span class="required"><?= _('Titel') ?></span>
            <?= I18N::input('title', $topic->title, ['required' => '']) ?>
        </label>


        <label>
            <?= _("Beschreibung") ?>

            <?= I18N::textarea('description', $topic->description, [
                'class' => 'wysiwyg size-l',
            ]) ?>
        </label>

    <? if ($documents_activated) : ?>
        <label>
        <? $folder = $topic->folders->first() ?>
        <? if ($folder) : ?>
            <?= Icon::create('accept', Icon::ROLE_ACCEPT)->asImg(['class' => 'text-bottom']) ?>
            <?= _('Dateiordner vorhanden') ?>
        <? else : ?>
            <input type="checkbox" name="folder" id="topic_folder" value="1">
            <?= _('Dateiordner anlegen') ?>
        <? endif ?>
        </label>
    <? endif ?>

    <? if ($forum_activated) : ?>
        <label>
        <? if ($topic->forum_thread_url) : ?>
            <?= Icon::create('accept', Icon::ROLE_ACCEPT)->asImg(['class' => 'text-bottom']) ?>
            <?= _('Forenthema vorhanden') ?>
        <? else : ?>
            <input type="checkbox" name="forumthread" id="topic_forumthread" value="1">
            <?= _('Forenthema anlegen') ?>
        <? endif ?>
        </label>
    <? endif ?>

        <h2><?= _('Termine') ?></h2>
        <? foreach ($dates as $date) : ?>
            <label>
                <input type="checkbox" name="date[<?= htmlReady($date->id) ?>]" value="1" class="text-bottom"
                       <? if (in_array($date->id, $date_ids)) echo 'checked'; ?>>
                <?= Icon::create('date', Icon::ROLE_INFO)->asImg(['class' => 'text-bottom']) ?>
                <?= floor($date['date'] / 86400) !== floor($date['end_time'] / 86400) ? date("d.m.Y, H:i", $date['date'])." - ".date("d.m.Y, H:i", $date['end_time']) : date("d.m.Y, H:i", $date['date'])." - ".date("H:i", $date['end_time']) ?>

            <? if (count($date->topics) > 0) : ?>
            (
                <? foreach ($date->topics as $key => $localtopic) : ?>
                    <a href="<?= $controller->index(['open' => $localtopic->id]) ?>">
                        <?= Icon::create('topic')->asImg(['class' => 'text-bottom']) ?>
                        <?= htmlReady($localtopic->title) ?>
                    </a>
                <? endforeach ?>
            )
            <? endif ?>
            </label>
        <? endforeach ?>

        <h2><?= _('Hausarbeit/Referat') ?></h2>
        <label>
            <input type="checkbox" name="paper_related" value="1"
                   <? if ($topic->paper_related) echo 'checked'; ?>>
            <?= _('Thema behandelt eine Hausarbeit oder ein Referat') ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <div class="button-group">
            <?= \Studip\Button::createAccept(_('Speichern')) ?>

            <? if (!$topic->isNew()) : ?>
                <?= \Studip\LinkButton::create(
                    _('Löschen'),
                    $controller->url_for('course/topics/delete/' . $topic->getId()),
                    ['data-confirm' => _('Das Thema wirklich löschen?')]
                ) ?>
            <? endif ?>
        </div>
    </footer>
</form>
