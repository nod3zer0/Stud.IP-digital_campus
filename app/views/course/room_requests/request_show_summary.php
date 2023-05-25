<form method="post" name="room_request" class="default"
      action="<?= $controller->link_for('course/room_requests/store_request/' . $request_id) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= CSRFProtection::tokenTag() ?>


    <?= $this->render_partial('course/room_requests/_new_request_header') ?>
    <section class="resources-grid">
        <div>
        <fieldset>
            <legend>
                <?= _('Zusammenfassung') ?>
            </legend>
            <label>
                <?= _('Ausgewählte Raumkategorie') ?>
                <input type="hidden" name="selected_room_id"
                       value="<?= htmlReady($selected_room->id) ?>">
                <br>

                <strong><?= htmlReady($selected_room_category->name) ?></strong>
            </label>

            <label>
                <?= _('Ausgewählter Raum') ?>
                <? if ($selected_room): ?>
                    <input type="hidden" name="selected_room_id"
                           value="<?= htmlReady($selected_room->id) ?>">
                    <br>

                    <strong><?= htmlReady($selected_room->name) ?></strong>
                <? else : ?>
                    <br>

                    <strong><?= _('Es wurde kein spezifischer Raum gewählt.') ?></strong>
                <? endif ?>
            </label>

            <? foreach ($available_properties as $property) : ?>
                <? foreach ($selected_properties as $key => $value) : ?>
                    <? if ($property->name === $key) :  ?>
                        <?= $property->toHtmlInput(
                            $selected_properties[$property->name],
                            'selected_properties[' . htmlReady($property->name) . ']',
                            true,
                            false,
                            true
                        ) ?>
                    <? endif ?>
                <? endforeach ?>
            <? endforeach ?>

        </fieldset>
        </div>
        <div>
            <fieldset>
                <legend>
                    <?= _('Sonstiges') ?>
                </legend>
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
                                ? 'checked'
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

            </fieldset>
        </div>

    </section>
    <?= $this->render_partial('course/room_requests/_new_request_form_footer', ['step' => $step, 'search_by' => 'roomname']) ?>
