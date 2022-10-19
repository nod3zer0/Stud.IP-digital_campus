<li class="notification item" data-id="<?= $notification['personal_notification_id'] ?>" data-timestamp="<?= (int) $notification['mkdate'] ?>">
    <div class="main">
        <a class="content" href="<?= URLHelper::getLink('dispatch.php/jsupdater/mark_notification_read/' . $notification['personal_notification_id']) ?>"<?= $notification['dialog'] ? ' data-dialog' : '' ?>>
            <? if ($notification['avatar']): ?>
                <div class="avatar" style="background-image: url(<?= $notification['avatar'] ?>);"></div>
            <? endif ?>
            <?= htmlReady($notification['text']) ?>
        </a>
        <button class="options mark_as_read">
            <?= Icon::create('decline')->asImg(12, ['title' => _('Als gelesen markieren')]) ?>
        </button>
    </div>
    <? if ($notification->more_unseen > 0): ?>
        <div class="more">
            <?= htmlReady(sprintf(_('... und %u weitere'), $notification->more_unseen)) ?>
        </div>
    <? endif ?>
</li>
