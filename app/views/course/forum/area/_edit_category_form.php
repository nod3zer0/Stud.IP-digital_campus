<form method="post" action="<?= $controller->link_for('course/forum/index/edit_category/' . $category_id) ?>" class="default">
    <input type="text" required name="name" class="size-m" maxlength="255" value="<?= htmlReady($categories[$category_id]) ?>">

    <?= Studip\Button::createAccept(_('Kategorie speichern'), '',
        ['onClick' => "javascript:STUDIP.Forum.saveCategoryName('". $category_id ."'); return false;"]) ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/forum/index/index#cat_'. $category_id),
        ['onClick' => "STUDIP.Forum.cancelEditCategoryName('". $category_id ."'); return false;"]) ?>
</form>
