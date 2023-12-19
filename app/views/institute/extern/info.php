<?php
/**
 * @var ExternPageConfig $page
 * @var DataField[] $datafields
 * @var string $author
 * @var string $editor
 */
?>

<section class="contentbox">
    <header>
        <h1><?= _('Allgemeine Informationen') ?></h1>
    </header>
    <section>
        <dl>
            <dt><?= _('Modultyp') ?></dt>
            <dd><?= htmlReady($page->type) ?></dd>
            <dt><?= _('Name der Konfiguration') ?></dt>
            <dd><?= htmlReady($page->name) ?></dd>
            <dt><?= _('Erstellt am') ?></dt>
            <dd><?= date('d.m.Y H:m ', $page->mkdate) . sprintf(_('durch %s'), $author) ?></dd>
            <dt><?= _('Bearbeitet am') ?></dt>
            <dd><?= date('d.m.Y H:m ', $page->chdate) . sprintf(_('durch %s'), $editor) ?></dd>
            <dt><?= _('Beschreibung') ?></dt>
            <dd><?= htmlReady($page->description) ?></dd>
            <dt><?= _('Zur Hilfeseite') ?></dt>
            <dd><a class="link-extern" href="<?= htmlReady(format_help_url(PageLayout::getHelpKeyword())) ?>">
                    <?= sprintf(_('Hilfe zur Konfiguration des Moduls "%s".'), htmlReady($page->type)) ?>
                </a>
            </dd>
        </dl>
    </section>
</section>
<section class="contentbox">
    <header>
        <h1><?= _('Informationen zur Verlinkung') ?></h1>
    </header>
    <section>
        <dl>
            <dt><?= _('Direkter Link') ?></dt>
            <dd>
                <blockquote>
                    <? $link = htmlReady(URLHelper::getLink('dispatch.php/extern/index/' . $page->id, null,true)) ?>
                    <a href="<?= $link ?>" target="_blank"><?= $link ?></a>
                </blockquote>
                <span style="font-size: smaller;">
                    <?= _('Diese Adresse können Sie in einen Link auf Ihrer Website integrieren, um auf die Ausgabe des Moduls zu verweisen.') ?>
                </span>
            </dd>
            <dt><?= _('Mögliche URL-Parameter') ?></dt>
            <dd><?= htmlReady(implode(', ', $page->getAllowedRequestParams(true))) ?></dd>
        </dl>
    </section>
</section>
<? if (count($datafields)) : ?>
<? $classes = DataField::getDataClass() ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Datenfelder') ?></h1>
        </header>
        <section>
            <dl>
                <? foreach ($datafields as $class_name => $datafield_class) : ?>
                    <? foreach ($datafield_class as $datafield) : ?>
                        <dt><?= htmlReady($classes[$class_name] . ': ' . $datafield->name) ?></dt>
                        <dd>
                            <?= 'DATAFIELD_' . $datafield->id ?>
                            <span style="font-size: smaller;"><?= htmlReady($datafield->description) ?></span>
                        </dd>
                    <? endforeach ?>
                <? endforeach ?>
            </dl>
            <p><?= _('Hinweis: Die gelisteten Datenfelder sind eventuell nicht für alle Objekte eines Typs verfügbar.') ?></p>
        </section>
    </section>
<? endif ?>
