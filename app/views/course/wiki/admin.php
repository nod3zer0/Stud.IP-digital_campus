<?php
/**
 * @var WikiPage[] $pages
 * @var Course_WikiController $controller
 * @var CourseConfig $config
 * @var Course|Institute $range
 */
?>
<form class="default" method="post" action="<?= $controller->store_course_config() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if (count($pages) > 0) : ?>
        <label>
            <?= _('Startseite des Wikis') ?>
            <select name="wiki_startpage_id">
                <? foreach ($pages as $page) : ?>
                    <option value="<?= htmlReady($page->id) ?>"
                            <? if ($config->WIKI_STARTPAGE_ID == $page->id) echo 'selected'; ?>
                    ><?= htmlReady($page->name) ?></option>
                <? endforeach ?>
            </select>
        </label>
    <? endif ?>

    <? if ($config->WIKI_CREATE_PERMISSION === 'all' || $GLOBALS['perm']->have_studip_perm($config->WIKI_CREATE_PERMISSION, $range->id)) : ?>
        <label>
            <?= _('Wer darf neue Wiki-Seiten anlegen?') ?>
            <select name="wiki_create_permission">
                <option value="all"
                        <? if ($config->WIKI_CREATE_PERMISSION === 'all') echo 'selected'; ?>
                ><?= _('Alle') ?></option>
                <option value="tutor"
                        <? if ($config->WIKI_CREATE_PERMISSION === 'tutor') echo 'selected'; ?>
                ><?= _('Tutor/-innen und Lehrende') ?></option>
                <option value="dozent"
                        <?= $GLOBALS['perm']->have_studip_perm('dozent', $range->id) ? '' : 'disabled'?>
                        <? if ($config->WIKI_CREATE_PERMISSION === 'dozent') echo 'selected'; ?>
                ><?= _('Nur Lehrende') ?></option>
            </select>
        </label>
    <? else : ?>
        <div>
            <?= _('Wer darf neue Wiki-Seiten anlegen?') ?>
            <div>
                <? switch ($config->WIKI_CREATE_PERMISSION) {
                    case 'all':
                        echo _('Alle');
                        break;
                    case 'tutor':
                        echo _('Tutor/-innen und Lehrende');
                        break;
                    case 'dozent':
                        echo _('Nur Lehrende');
                        break;
                } ?>
            </div>
        </div>
    <? endif ?>

    <? if ($config->WIKI_RENAME_PERMISSION === 'all' || $GLOBALS['perm']->have_studip_perm($config->WIKI_RENAME_PERMISSION, $range->id)) : ?>
        <label>
            <?= _('Wer darf Wiki-Seiten umbenennen?') ?>
            <select name="wiki_rename_permission">
                <option value="all"
                        <? if ($config->WIKI_RENAME_PERMISSION === 'all') echo 'selected'; ?>
                ><?= _('Alle') ?></option>
                <option value="tutor"
                        <? if ($config->WIKI_RENAME_PERMISSION === 'tutor') echo 'selected'; ?>
                ><?= _('Tutor/-innen und Lehrende') ?></option>
                <option value="dozent"
                        <?= $GLOBALS['perm']->have_studip_perm('dozent', $range->id) ? '' : 'disabled'?>
                        <? if ($config->WIKI_RENAME_PERMISSION === 'dozent') echo 'selected'; ?>
                ><?= _('Nur Lehrende') ?></option>
            </select>
        </label>
    <? else : ?>
        <div>
            <?= _('Wer darf Wiki-Seiten umbenennen?') ?>
            <div>
                <? switch ($config->WIKI_RENAME_PERMISSION) {
                    case 'all':
                        echo _('Alle');
                        break;
                    case 'tutor':
                        echo _('Tutor/-innen und Lehrende');
                        break;
                    case 'dozent':
                        echo _('Nur Lehrende');
                        break;
                } ?>
            </div>
        </div>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(_('Übernehmen')) ?>
    </div>
</form>
