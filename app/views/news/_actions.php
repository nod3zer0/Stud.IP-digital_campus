<? $user = new User($new['user_id']); ?>
<? if (Config::get()->NEWS_DISPLAY >= 1 || $new->havePermission('edit')): ?>
    <a class='news_user' href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $user->username) ?>">
        <?= htmlReady($user->getFullName()) ?>
    </a>
<? endif; ?>

<span class='news_date' title="<?= ($perm ? _('Ablaufdatum') . ': ' . strftime('%x', $new['date'] + $new['expire']) : '') ?>">
    <?= strftime('%x', $new['date']) ?>
</span>

<? if (Config::get()->NEWS_DISPLAY >= 2 || $new->havePermission('edit')): ?>
    <span title="<?= _('Aufrufe') ?>" class='news_visits' style="color: #050">
        <?= object_return_views($new['news_id']) ?>
    </span>
<? endif; ?>

<?
if ($new['allow_comments']) :
    $num = StudipComment::NumCommentsForObject($new['news_id']);
    $visited = object_get_visit($new['news_id'], 'news');
    $isnew = StudipComment::NumCommentsForObjectSinceLastVisit($new['news_id'], $visited, $GLOBALS['user']->id);
    ?>
    <? if ($num): ?>
        <? if ($isnew): ?>
            <span class="news_comments_indicator nowrap" title="<?= sprintf(_('%s neue(r) Kommentar(e)'), $isnew) ?>">
                <?= Icon::create('chat', Icon::ROLE_NEW) ?>
        <? else: ?>
            <span class="news_comments_indicator nowrap" title="<?= sprintf(_('%s Kommentare'), $num) ?>">
                <?= Icon::create('chat', Icon::ROLE_INFO) ?>
        <? endif; ?>
                <?= $num ?>
            </span>
    <? endif; ?>
<? endif; ?>



<? if ($new->havePermission('edit')): ?>
    <a href="<?= URLHelper::getLink('dispatch.php/news/edit_news/' . $new->id) ?>" data-dialog>
        <?= Icon::create('edit') ?>
    </a>
    <? if ($new->havePermission('unassign', $range)): ?>
        <a href=" <?= URLHelper::getLink('', ['remove_news' => $new->id, 'news_range' => $range]) ?>" >
            <?= Icon::create('remove') ?>
        </a>
    <? endif; ?>
    <? if ($new->havePermission('delete')): ?>
        <a href=" <?= URLHelper::getLink('', ['delete_news' => $new->id]) ?>" >
            <?= Icon::create('trash') ?>
        </a>
    <? endif; ?>
<? endif; ?>
