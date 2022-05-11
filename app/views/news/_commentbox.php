<article class="comment open" id="newscomment-<?= htmlReady($comment['comment_id']) ?>">
    <time><?= reltime($comment['mkdate']) ?></time>
    <h1>#<?= $index + 1 ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $comment['username']]) ?>">
             <?= htmlReady($comment['fullname']) ?>
        </a>
    </h1>
    <?= formatReady($comment['content']) ?>
</article>
