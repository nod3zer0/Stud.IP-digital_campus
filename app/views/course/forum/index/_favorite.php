<? if (!ForumPerm::has('fav_entry', $seminar_id)) return; ?>

<!-- set/unset favorite -->
<? if (!$favorite) : ?>
    <a href="<?= $controller->link_for('course/forum/index/set_favorite/'. $topic_id) ?>" onClick="STUDIP.Forum.setFavorite('<?= $topic_id ?>');return false;">
        <?= Icon::create('staple', 'clickable', ['title' => _('Beitrag merken')])->asImg() ?>
    </a>
<? else : ?>
    <a href="<?= $controller->link_for('course/forum/index/unset_favorite/'. $topic_id) ?>" onClick="STUDIP.Forum.unsetFavorite('<?= $topic_id ?>');return false;">
        <?= Icon::create('staple', 'attention', ['title' => _('Beitrag nicht mehr merken')])->asImg() ?>
    </a>
<? endif ?>
