<input type="hidden" name="received" id="received" value="<?= (int) $received ?>">
<input type="hidden" name="since" id="since" value="<?= time() ?>">
<input type="hidden" name="folder_id" id="tag" value="<?= htmlReady(ucfirst(Request::get("tag"))) ?>">
<input type="hidden" name="search" id="search" value="<?= htmlReady(Request::get("search")) ?>">
<input type="hidden" name="search_autor" id="search_autor" value="<?= htmlReady(Request::get("search_autor")) ?>">
<input type="hidden" name="search_subject" id="search_subject" value="<?= htmlReady(Request::get("search_subject")) ?>">
<input type="hidden" name="search_content" id="search_content" value="<?= htmlReady(Request::get("search_content")) ?>">

<form action="?" method="post" id="bulk">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="mbox" value="<?= $received ? 'rec' : 'snd' ?>">
    <table class="default" id="messages">
        <caption>
            <?= $received ? _("Eingang") : _("Gesendet") ?>
            <? if (Request::get("tag")) : ?>
                <?= ', ' . _('Schlagwort') . ': ' . htmlReady(ucfirst(Request::get('tag'))) ?>
                <button onClick="STUDIP.Dialog.confirmAsPost('<?=_('Schlagwort wirklich löschen?')?>', '<?=$controller->link_for('messages/delete_tag', ['tag' => Request::get('tag')])?>');return false;" style="background: none; border: none; cursor: pointer;" title="<?= _("Schlagwort von allen Nachrichten entfernen.") ?>">
                    <?= Icon::create('trash', 'clickable')->asImg(20) ?>
                </button>
            <? endif ?>
        </caption>
        <colgroup>
            <col class="hidden-small-down">
            <col>
            <col class="hidden-small-down">
            <col style="width: 20ex">
            <col class="hidden-small-down">
        </colgroup>
        <thead>
            <tr>
                <th class="hidden-small-down">
                    <input type="checkbox" data-proxyfor="#bulk tbody :checkbox">
                </th>
                <th><?= _("Betreff") ?></th>
                <th  class="hidden-small-down"><?= $received ? _('Absender') : _('Empfänger') ?></th>
                <th><?= _("Zeit") ?></th>
                <th class="hidden-small-down"><?= _("Schlagworte") ?></th>
            </tr>
        </thead>

        <tbody aria-relevant="additions" aria-live="polite" data-shiftcheck>
            <? if (count($messages) > 0) : ?>
                <? if (!empty($more) || (Request::int("offset") > 0)) : ?>
                <noscript>
                <tr>
                    <td colspan="8">
                        <? if (Request::int("offset") > 0) : ?>
                        <a title="<?= _("zurück") ?>" href="<?= URLHelper::getLink("?", ['offset' => Request::int("offset") - $messageBufferCount > 0 ? Request::int("offset") - $messageBufferCount : null]) ?>"><?= Icon::create('arr_1left', 'clickable')->asImg(["class" => "text-bottom"]) ?></a>
                        <? endif ?>
                        <? if (!empty($more)) : ?>
                        <div style="float:right">
                            <a title="<?= _("weiter") ?>" href="<?= URLHelper::getLink("?", ['offset' => Request::int("offset") + $messageBufferCount]) ?>"><?= Icon::create('arr_1right', 'clickable')->asImg(["class" => "text-bottom"]) ?></a>
                        </div>
                        <? endif ?>
                    </td>
                </tr>
                </noscript>
                <? endif ?>
                <? foreach ($messages as $message) : ?>
                <?= $this->render_partial('messages/_message_row.php', ['message' => $message, 'received' => $received, 'settings' => $settings]) ?>
                <? endforeach ?>
                <? if (!empty($more) || (Request::int("offset") > 0)) : ?>
                <noscript>
                <tr>
                    <td colspan="7">
                        <? if (Request::int("offset") > 0) : ?>
                            <a title="<?= _("zurück") ?>" href="<?= URLHelper::getLink("?", ['offset' => Request::int("offset") - $messageBufferCount > 0 ? Request::int("offset") - $messageBufferCount : null]) ?>"><?= Icon::create('arr_1left', 'clickable')->asImg(["class" => "text-bottom"]) ?></a>
                        <? endif ?>
                        <? if (!empty($more)) : ?>
                            <div style="float:right">
                                <a title="<?= _("weiter") ?>" href="<?= URLHelper::getLink("?", ['offset' => Request::int("offset") + $messageBufferCount]) ?>"><?= Icon::create('arr_1right', 'clickable')->asImg(["class" => "text-bottom"]) ?></a>
                            </div>
                        <? endif ?>
                    </td>
                </tr>
                </noscript>
                <? endif ?>
            <? else : ?>
            <tr>
                <td colspan="7" style="text-align: center"><?= _("Keine Nachrichten") ?></td>
            </tr>
            <? endif ?>
            <tr id="reloader" class="more">
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>

</form>


<div style="display: none; background-color: rgba(255,255,255, 0.3); padding: 3px; border-radius: 5px; border: thin solid black;" id="move_handle">
    <?= Icon::create('mail', 'clickable')->asImg(20, ['class' => "text-bottom"]) ?>
    <span class="title"></span>
</div>

<? if ($message_id): ?>
<script>
jQuery(function ($) {
    STUDIP.Dialog.fromURL('<?= $controller->url_for('messages/read/' . $message_id) ?>');
});
</script>
<? endif; ?>
