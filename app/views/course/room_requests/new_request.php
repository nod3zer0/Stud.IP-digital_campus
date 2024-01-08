<form method="post" name="room_request" class="default"
      action="<?= $controller->link_for('course/room_requests/request_first_step/' . $request_id) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= CSRFProtection::tokenTag() ?>

    <?= $this->render_partial('course/room_requests/_new_request_header') ?>

    <section class="resources-grid">
        <div>
            <fieldset>
                <legend><?= _('Suche nach Raumkategorie und Eigenschaften') ?></legend>

            <? if ($available_room_categories): ?>
                <label>
                    <?= _('Raumkategorie') ?>
                    <span class="flex-row">
                        <select name="category_id" <?= !empty($category) ? 'disabled' : '' ?>>
                        <option value=""><?= _('bitte auswählen') ?></option>
                        <? foreach ($available_room_categories as $rc): ?>
                            <option value="<?= htmlReady($rc->id) ?>"
                                <? if (isset($_SESSION[$request_id]['room_category']) && $_SESSION[$request_id]['room_category'] === $rc->id) echo 'selected'; ?>
                            >
                        <?= htmlReady($rc->name) ?>
                        </option>
                        <? endforeach ?>
                    </select>
                    <? if (!empty($category)) : ?>
                        <?= Icon::create('decline')->asInput(
                            [
                                'title' => _('alle Angaben zurücksetzen'),
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'reset_category',
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? else : ?>
                        <?= Icon::create('accept')->asInput(
                            [
                                'title' => _('Raumtyp auswählen'),
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'search_by_category',
                                'value' => _('Raumtyp auswählen'),
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? endif ?>
                    </span>
                </label>
            <? endif ?>
            </fieldset>
        </div>

<? if (empty($embedded)) : ?>
        <div>
            <fieldset>
                <legend><?= _('Raumsuche') ?></legend>
                <label>
                    <?= _('Raumname') ?>
                    <span class="flex-row">
                        <input type="text" name="room_name" value="<?= htmlReady($_SESSION[$request_id]['room_name'] ?? '') ?>">
                        <?= Icon::create('search')->asInput([
                            'title' => _('Räume suchen'),
                            'name'  => 'search_by_name',
                            'class' => 'text-bottom',
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;',
                        ]) ?>
                        <? if (!empty($room_name)) : ?>
                            <?= Icon::create('decline')->asInput([
                                'title' => _('alle Angaben zurücksetzen'),
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'reset_name',
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;',
                            ]) ?>
                        <? endif?>
                    </span>
                </label>

            </fieldset>
        </div>
<? endif; ?>
    </section>

<? if (empty($embedded)) : ?>
    <?= $this->render_partial('course/room_requests/_new_request_form_footer', ['step' => $step]) ?>
<? endif ?>
