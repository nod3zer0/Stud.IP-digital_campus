<?php
/**
 * @var bool $show_wiki_page_form
 * @var Course_WikiController $controller
 * @var Range $range
 * @var bool $success
 * @var bool $bad_course_search
 * @var QuickSearch $course_search
 * @var Course $selected_course
 * @var array $wiki_pages
 */
?>
<form class="default" method="post"
      name="wiki_import_form"
      data-dialog="size=auto;<?= $show_wiki_page_form ? 'reload-on-close' : '' ?>"
      action="<?= $controller->import() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <? if (!$show_wiki_page_form && !$success): ?>
        <fieldset>
            <legend><?= _('Suche nach Veranstaltungen') ?></legend>
            <label class="with-action">
                <? if ($bad_course_search): ?>
                    <?= _('Meinten Sie eine der folgenden Veranstaltungen?') ?>
                <? else: ?>
                    <?= _('Sie können hier eine Veranstaltung mit zu importierenden Wiki-Seiten suchen.') ?>
                <? endif ?>
                <?= $course_search->render() ?>
                <? if ($bad_course_search): ?>
                    <a href="<?= $controller->import() ?>"
                       data-dialog="1">
                        <?= Icon::create('decline')->asImg([
                            'class'   => 'text-bottom',
                            'title'   => _('Suche zurücksetzen'),
                            'onclick' => "STUDIP.QuickSearch.reset('wiki_import_form', 'selected_range_id');"
                        ]) ?>
                    </a>
                <? else : ?>
                    <?= Icon::create('search')->asImg([
                        'class'   => 'text-bottom',
                        'title'   => _('Suche starten'),
                        'onclick' => "jQuery(this).closest('form').submit();"
                    ]) ?>
                <? endif ?>
            </label>
            <div data-dialog-button>
                <? if ($bad_course_search): ?>
                    <?= Studip\LinkButton::create(
                        _('Neue Suche'),
                        $controller->importURL(),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                <? endif ?>
                <?= Studip\LinkButton::createCancel(
                    _('Abbrechen'),
                    $controller->pageURL()
                ) ?>
            </div>
        </fieldset>
    <? endif ?>

    <? if ($show_wiki_page_form): ?>
        <input type="hidden" name="selected_range_id"
               value="<?= htmlReady($selected_course->id) ?>">
        <? if ($wiki_pages): ?>
            <table class="default">
                <colgroup>
                    <col style="width: 20px">
                    <col>
                </colgroup>
                <caption>
                    <?= sprintf(
                        _('%s: Importierbare Wiki-Seiten'),
                        htmlReady($selected_course->getFullName())
                    ) ?>
                </caption>
                <thead>
                <tr>
                    <th>
                        <input type="checkbox"
                               data-proxyfor=":checkbox[name='selected_wiki_page_ids[]']">
                    </th>
                    <th><?= _('Seitenname') ?></th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($wiki_pages as $wiki_page): ?>
                    <? if ($wiki_page->isReadable()) : ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="selected_wiki_page_ids[]"
                                       value="<?= htmlReady($wiki_page->getId()) ?>">
                            </td>
                            <td><?= htmlReady($wiki_page->name) ?></td>
                        </tr>
                    <? endif ?>
                <? endforeach ?>
                </tbody>
            </table>
            <div data-dialog-button>
                <?= Studip\Button::create(_('Importieren'), 'import') ?>
                <?= Studip\LinkButton::create(
                    _('Neue Suche'),
                    $controller->importURL(),
                    ['data-dialog' => 'size=auto']
                ) ?>
                <?= Studip\LinkButton::createCancel(
                    _('Abbrechen'),
                    $controller->pageURL()
                ) ?>
            </div>
        <? else: ?>
            <?= MessageBox::info(
                _('Die gewählte Veranstaltung besitzt keine Wiki-Seiten!')
            ) ?>
        <? endif ?>
    <? endif ?>
    <? if ($success): ?>
        <div data-dialog-button>
            <?= Studip\LinkButton::create(
                _('Import neu starten'),
                $controller->importURL(),
                ['data-dialog' => 'size=auto']
            ) ?>
            <?= Studip\LinkButton::createCancel(
                _('Zurück zum Wiki'),
                $controller->pageURL()
            ) ?>
        </div>
    <? endif ?>
</form>
