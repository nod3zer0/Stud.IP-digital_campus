<form method="post" name="room_request" class="default"
      action="<?= $this->controller->link_for('course/room_requests/find_by_roomname/' . $request_id . '/' . $this->step) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= CSRFProtection::tokenTag() ?>


    <?= $this->render_partial(
        'course/room_requests/_new_request_header') ?>

    <?= var_dump($_SESSION[$request_id]) ?>
    <section class="resources-grid">
        <div>
            <fieldset>
                <legend><?= _('Wünschbare Eigenschaften') ?></legend>

                <? if ($step == 2) : ?>
                    <?= $this->render_partial('course/room_requests/_room_with_properties') ?>
                <? endif ?>

                <? if ($available_room_categories): ?>
                    <label>
                        <?= _('Raumkategorie') ?>
                        <span class="flex-row">
                        <select name="category_id" <?= $category ? 'disabled' : '' ?>>
                        <option value=""><?= _('bitte auswählen') ?></option>
                        <? foreach ($available_room_categories as $rc): ?>
                            <option value="<?= htmlReady($rc->id) ?>"
                                    <?= ($selected_room->category_id == $rc->id)
                                        ? 'selected="selected"'
                                        : '' ?>>
                        <?= htmlReady($rc->name) ?>
                        </option>
                        <? endforeach ?>
                    </select>
                    <? if ($category) : ?>
                        <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['title' => _('alle Angaben zurücksetzen')])->asInput(
                            [
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'reset_category',
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? else : ?>
                        <?= Icon::create('accept', Icon::ROLE_CLICKABLE, ['title' => _('Raumtyp auswählen')])->asInput(
                            [
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'select_properties',
                                'value' => _('Raumtyp auswählen'),
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? endif ?>
                    </span>
                    </label>
                <? endif ?>

                <? if ($step == 2) : ?>
                    <!-- After choosing a category or room -->
                    <? if ($available_properties) : ?>
                        <? foreach ($available_properties as $property) : ?>
                            <?= $property->toHtmlInput(
                                $selected_properties[$property->name],
                                'selected_properties[' . htmlReady($property->name) . ']',
                                true,
                                false
                            ) ?>
                        <? endforeach ?>
                    <? endif ?>
                    <label>
                        <?= _('Rüstzeit (in Minuten)') ?>
                        <input type="number" name="preparation_time"
                               value="<?= htmlReady($preparation_time) ?>"
                               min="0" max="<?= htmlReady($max_preparation_time) ?>">
                    </label>
                    <? if ($user_is_global_resource_admin) : ?>
                        <label>
                            <input type="checkbox" name="reply_lecturers" value="1"
                                <?= $reply_lecturers
                                    ? 'checked="checked"'
                                    : ''
                                ?>>
                            <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
                        </label>
                    <? endif ?>

                    <label>
                        <?= _('Nachricht an die Raumvergabe') ?>
                        <textarea name="comment" cols="58" rows="4"
                                  placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur angefragten Raumbelegung') ?>"><?= htmlReady($comment) ?></textarea>
                    </label>
                <? endif ?>

                <? if (!$embedded) : ?>
            </fieldset>
        </div>

        <div>
            <fieldset>
                <legend><?= _('Raumsuche') ?></legend>
                <label>
                    <?= _('Raumname') ?>
                    <span class="flex-row">
                    <input type="text" name="room_name" value="<?= htmlReady($_SESSION[$request_id]['room_name']) ?>">
                    <?= Icon::create('search', Icon::ROLE_CLICKABLE)->asInput(
                        [
                            'name'  => 'search_by_name',
                            'class' => 'text-bottom',
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                        ]
                    ) ?>
                        <? if ($_SESSION[$request_id]['room_name']) : ?>
                            <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['title' => _('alle Angaben zurücksetzen')])->asInput(
                                [
                                    'type'  => 'image',
                                    'class' => 'text-bottom',
                                    'name'  => 'reset_category',
                                    'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                                ]
                            ) ?>
                        <? endif?>
                </span>
                </label>

                <? if ($available_rooms) : ?>
                    <label><?= _('Passende Räume') ?>
                        <section class="selectbox">
                                <? foreach ($available_rooms as $room): ?>
                                    <div class="flex-row">
                                        <label class="horizontal">
                                            <?= $available_room_icons[$room->id] ?>
                                            <input type="radio" name="selected_room_id"
                                                   data-activates="button[type='submit'][name='select_room']"
                                                   value="<?= htmlReady($room->id) ?>"
                                                <? if ($_SESSION[$request_id]['room_id'] == $room->id) echo 'checked' ?>>

                                            >
                                            <?= htmlReady(mb_substr($room->name, 0, 50)); ?>
                                            <? if ($room->properties): ?>
                                                <? $property_names = $room->getInfolabelPrperties()
                                                    ->pluck('fullname') ?>
                                                <?= tooltipIcon(implode("\n", $property_names)) ?>
                                            <? endif ?>
                                        </label>
                                    </div>
                                <? endforeach ?>
                        </section>
                    </label>
                <? else : ?>
                    <?= MessageBox::info(_('Es wurden keine passenden Räume gefunden!')) ?>
                <? endif ?>

            </fieldset>

        </div>
    </section>

<?= $this->render_partial('course/room_requests/_new_request_form_footer', ['step' => $step]) ?>
<? endif ?>
