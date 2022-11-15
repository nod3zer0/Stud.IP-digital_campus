<? use Studip\Button, Studip\LinkButton; ?>

<? if (count($categories) === 0): ?>
<p class="info"><?= _('Es existieren zur Zeit keine eigenen Kategorien.') ?></p>
<? else: ?>
<form action="<?= $controller->url_for('settings/categories/store') ?>" method="post" name="main_content" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <? foreach ($categories as $index => $category): ?>
        <input type="hidden" name="ids[]" value="<?= htmlReady($category->id) ?>">

        <fieldset>
            <legend style="display: flex; flex-wrap: nowrap; justify-content: space-between">
                <span><?= htmlReady($category->name) ?></span>
                <span>
                <? if ($index > 0): ?>
                    <a href="<?= $controller->url_for('settings/categories/swap', $category->id, $last->id) ?>">
                        <?= Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top', 'title' =>_('Kategorie nach oben verschieben')]) ?>
                    </a>
                <? else: ?>
                    <?= Icon::create('arr_2up', 'inactive')->asImg(['class' => 'text-top']) ?>
                <? endif; ?>

                <? if ($index < $count - 1): ?>
                    <a href="<?= $controller->url_for('settings/categories/swap', $category->id, $categories[$index + 1]->id) ?>">
                                <?= Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top', 'title' =>_('Kategorie nach unten verschieben')]) ?>
                            </a>
                <? else: ?>
                    <?= Icon::create('arr_2down', 'inactive')->asImg(['class' => 'text-top']) ?>
                <? endif; ?>

                    <a href="<?= $controller->url_for('settings/categories/delete', $category->id) ?>">
                        <?= Icon::create('trash')->asImg(['class' => 'text-top', 'title' => _('Kategorie löschen')]) ?>
                    </a>
                </span>
            </legend>

            <p>
                (<?= $visibilities[$category->id] ?>)
            </p>

            <label>
                <?= _('Name') ?>
                <?= I18N::input("category-name-{$category->id}", $category->name, [
                    'aria-label' => _('Name der Kategorie'),
                    'class'      => 'size-l',
                    'id'         => "name{$index}",
                    'required'   => '',
                ]) ?>
            </label>

            <label>
                <?= _('Inhalt') ?>

                <?= I18n::textarea("category-content-{$category->id}", $category->content, [
                    'aria-label' => _('Inhalt der Kategorie:'),
                    'class'      => 'resizable add_toolbar wysiwyg size-l',
                    'id'         => "content{$index}",
                ]) ?>
            </label>
        </fieldset>
    <? $last = $category;
       endforeach; ?>

    <? if ($hidden_count > 0): ?>
            <?= sprintf(ngettext('Es existiert zusätzlich eine Kategorie, die Sie nicht einsehen und bearbeiten können.',
                                 'Es existiereren zusätzlich %s Kategorien, die Sie nicht einsehen und bearbeiten können.',
                                 $hidden_count), $hidden_count) ?>
    <? endif; ?>

    <footer>
            <?= Button::create(_('Übernehmen'), 'store') ?>
    </footer>
</form>
<? endif; ?>
