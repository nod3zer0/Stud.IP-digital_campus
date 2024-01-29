<? if ($participation_message) : ?>
    <?= $participation_message ?>
    <article class="studip">
        <header><h1><?= _('Neuen Teilnahmestatus wählen') ?></h1></header>
        <section>
            <form class="default" method="post" data-dialog="reload-on-close"
                  action="<?= $controller->link_for('calendar/date/participation/' . $date->id) ?>">
                <?= CSRFProtection::tokenTag() ?>
                <fieldset>
                    <? if ($user_participation_status) : ?>
                        <label>
                            <input type="radio" name="participation" value=""
                                   data-activates="button[name='update_participation']">
                            <?= _('Abwartend') ?>
                        </label>
                    <? endif ?>
                    <? if ($user_participation_status !== 'ACCEPTED') : ?>
                        <label>
                            <input type="radio" name="participation" value="ACCEPTED"
                                   data-activates="button[name='update_participation']">
                            <?= _('Angenommen') ?>
                        </label>
                    <? endif ?>
                    <? if ($user_participation_status !== 'DECLINED') : ?>
                        <label>
                            <input type="radio" name="participation" value="DECLINED"
                                   data-activates="button[name='update_participation']">
                            <?= _('Abgelehnt') ?>
                        </label>
                    <? endif ?>
                    <? if ($user_participation_status !== 'ACKNOWLEDGED') : ?>
                        <label>
                            <input type="radio" name="participation" value="ACKNOWLEDGED"
                                   data-activates="button[name='update_participation']">
                            <?= _('Angenommen (keine Teilnahme)') ?>
                        </label>
                    <? endif ?>
                </fieldset>
                <div data-dialog-button>
                    <?= \Studip\Button::create(_('Teilnahmestatus ändern'), 'update_participation') ?>
                </div>
            </form>
        </section>
    </article>
<? endif ?>
<? if ($date->description) : ?>
    <article class="studip">
        <header><h1><?= _('Beschreibung') ?></h1></header>
        <section><?= htmlReady($date->description) ?></section>
    </article>
<? endif ?>
<article class="studip">
    <header><h1><?= _('Informationen') ?></h1></header>
    <section>
        <dl>
            <dt><?= _('Zeit') ?></dt>
            <dd><?= date('d.m.Y H:i', $date->begin) ?> - <?= date('d.m.Y H:i', $date->end) ?></dd>
            <dt><?= _('Kategorie') ?></dt>
            <dd><?= htmlReady($date->getCategoryAsString()) ?></dd>
            <dt><?= _('Zugriff') ?></dt>
            <dd><?= htmlReady($date->getAccessAsString()) ?></dd>
            <? if ($date->repetition_type) : ?>
                <dt><?= _('Wiederholung') ?></dt>
                <dd><?= htmlReady($date->getRepetitionAsString()) ?></dd>
            <? endif ?>
            <? if (
                $date->author && $date->editor
                && (
                    ($date->author_id !== User::findCurrent()->id)
                    || ($date->editor_id !== User::findCurrent()->id)
                )
            ) : ?>
                <dt><?= _('Bearbeitung') ?></dt>
                <dd>
                    <? if ($date->author->id === $date->editor->id) : ?>
                        <? if ($date->mkdate === $date->chdate) : ?>
                            <?= sprintf(
                                _('Erstellt von %s'),
                                htmlReady($date->author->getFullName())
                            ) ?>
                        <? else : ?>
                            <?= sprintf(
                                _('Erstellt und zuletzt bearbeitet von %s'),
                                htmlReady($date->author->getFullName())
                            ) ?>
                        <? endif ?>
                    <? else : ?>
                        <?= sprintf(
                            _('Erstellt von %1$s, zuletzt bearbeitet von %2$s'),
                            htmlReady($date->author->getFullName()),
                            htmlReady($date->editor->getFullName())
                        ) ?>
                    <? endif ?>
                </dd>
            <? endif ?>
        </dl>
    </section>
</article>
<? if ($is_group_date) : ?>
    <article class="studip">
        <header><h1><?= _('Teilnahmen') ?></h1></header>
        <section>
            <table class="default">
                <body>
                    <? foreach ($calendar_assignments as $assignment) : ?>
                        <tr>
                            <td><?= htmlReady($assignment->getRangeName()) ?></td>
                            <td><?= htmlReady($assignment->getParticipationAsString()) ?></td>
                        </tr>
                    <? endforeach ?>
                </body>
            </table>
        </section>
    </article>
<? endif ?>
<div data-dialog-button>
    <? if ($date->isWritable(User::findCurrent()->id) && $all_assignments_writable) : ?>
        <?
        $button_params = [];
        if ($selected_date) {
            $button_params['selected_date'] = $selected_date;
        }
        ?>
        <?= Studip\LinkButton::create(
            _('Bearbeiten'),
            $controller->url_for('calendar/date/edit/' . $date->id, array_merge($button_params, ['return_path' => '/calendar/calendar'])),
            ['data-dialog' => 'size=auto;reload-on-close']
        ) ?>
        <?= \Studip\LinkButton::create(
            _('Löschen'),
            $controller->url_for('calendar/date/delete/' . $date->id, $button_params),
            ['data-dialog' => 'reload-on-close']
        ) ?>
    <? endif ?>
</div>
