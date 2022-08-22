<form method="post" name="room_request" class="default"
      action="<?= $this->controller->link_for('course/room_requests/request_first_step/' . $request_id) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= CSRFProtection::tokenTag() ?>


<?= $this->render_partial(
    'course/room_requests/_new_request_header') ?>

<?= var_dump($_SESSION[$request_id]) ?>
    <br/>
    <section class="resources-grid">
    <div>
        <fieldset>
            <legend><?= _('Wünschbare Eigenschaften') ?></legend>

            <? if ($available_room_categories): ?>
                <label>
                    <?= _('Raumkategorie') ?>
                    <span class="flex-row">
                        <select name="category_id" <?= $category ? 'disabled' : '' ?>>
                        <option value=""><?= _('bitte auswählen') ?></option>
                        <? foreach ($available_room_categories as $rc): ?>
                            <option value="<?= htmlReady($rc->id) ?>"
                                    <?= ($_SESSION[$request_id]['room_category'] == $rc->id)
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
                                'name'  => 'search_by_category',
                                'value' => _('Raumtyp auswählen'),
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? endif ?>
                    </span>
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
                    <input type="text" name="room_name" value="<?= htmlReady($room_name) ?>">
                    <?= Icon::create('search', Icon::ROLE_CLICKABLE)->asInput(
                        [
                            'name'  => 'search_by_name',
                            'class' => 'text-bottom',
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                        ]
                    ) ?>
                    <? if ($room_name) : ?>
                        <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['title' => _('alle Angaben zurücksetzen')])->asInput(
                            [
                                'type'  => 'image',
                                'class' => 'text-bottom',
                                'name'  => 'reset_name',
                                'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                            ]
                        ) ?>
                    <? endif?>
                </span>
            </label>

        </fieldset>

    </div>
</section>
<?= $this->render_partial('course/room_requests/_new_request_form_footer', ['step' => $step]) ?>
<? endif ?>
