<?
$authors = $document->getAuthorNames();
$issue_date = $document->getIssueDate(true);
$identifiers = $document->getIdentifiers();
$url = ($document->download_url ?: $document->document_url);
$is_search = !$document->csl_data;
?>
<? if ($is_search) : ?>
    <?
    $description_fields = $document->getSearchDescription();
    ?>
    <h3><?= _('Suche in der Bibliothek') ?></h3>
    <ul class="default">
        <? foreach ($description_fields as $field) : ?>
            <li><?= htmlReady($field) ?></li>
        <? endforeach ?>
    </ul>
<? else : ?>
    <? if ($format === 'full') : ?>
        <dl>
            <dt><?= _('Titel') ?></dt>
            <dd><?= htmlReady($document->getTitle()) ?></dd>
            <dt><?= _('Typ') ?></dt>
            <dd><?= htmlReady($document->getType('display_name')) ?></dd>
            <? if (!empty($document->csl_data['issued']) || !empty($document->csl_data['publisher'])) : ?>
                <dt><?= _('Veröffentlicht') ?></dt>
                <dd><?= htmlReady($document->csl_data['publisher'] . ' ' . $document->getIssueDate(true)) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['medium'])) : ?>
                <dt><?= _('Medium') ?></dt>
                <dd><?= htmlReady($document->csl_data['medium']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['author'])) : ?>
                <dt><?= _('Erstellt von') ?></dt>
                <dd><?= htmlReady($document->getAuthorNames()) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['URL'])) : ?>
                <dt><?= _('URL') ?></dt>
                <dd><a href="<?= htmlReady($document->csl_data['URL']) ?>" target="_blank"><?= htmlReady($document->csl_data['URL']) ?></a></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['translator'][0]['family'])) : ?>
                <dt><?= _('Übersetzer*in') ?></dt>
                <dd>
                <? foreach ($document->csl_data['translator'] as $index => $translator) : ?>
                    <?= $index > 0 ? ', ' : '' ?>
                    <?= htmlReady($translator['suffix'].' '.$translator['given'].' '.$translator['family']) ?>
                <? endforeach ?>
                </dd>
            <? endif ?>
            <? if (!empty($document->csl_data['title-short'])) : ?>
                <dt><?= _('Kurztitel') ?></dt>
                <dd><?= htmlReady($document->csl_data['title-short']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['source'])) : ?>
                <dt><?= _('Quelle') ?></dt>
                <dd><?= htmlReady($document->csl_data['source']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['publisher-place'])) : ?>
                <dt><?= _('Verlagsort') ?></dt>
                <dd><?= htmlReady($document->csl_data['publisher-place']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['number-of-volumes'])) : ?>
                <dt><?= _('Bandanzahl') ?></dt>
                <dd><?= htmlReady($document->csl_data['number-of-volumes']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['number-of-pages'])) : ?>
                <dt><?= _('Seitenanzahl') ?></dt>
                <dd><?= htmlReady($document->csl_data['number-of-pages']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['note'])) : ?>
                <dt><?= _('Zusätzliche Information') ?></dt>
                <dd><?= htmlReady($document->csl_data['note']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['language'])) : ?>
                <dt><?= _('Sprache') ?></dt>
                <dd><?= htmlReady($document->csl_data['language']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['issued'][0][0])) : ?>
                <dt><?= _('Datum der Veröffentlichung der Ausgabe') ?></dt>
                <dd><?= htmlReady((!empty($document->csl_data['issued'][0][2]) ? $document->csl_data['issued'][0][2].'.' : '')
                        .(!empty($document->csl_data['issued'][0][1]) ? $document->csl_data['issued'][0][1].'.' : '')
                        .$document->csl_data['issued'][0][0]) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['ISBN'])) : ?>
                <dt><?= _('ISBN') ?></dt>
                <dd><?= htmlReady($document->csl_data['ISBN']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['event-place'])) : ?>
                <dt><?= _('Veranstaltungsort') ?></dt>
                <dd><?= htmlReady($document->csl_data['event-place']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['editor'][0]['family'])) : ?>
                <dt><?= _('Verfasser*in') ?></dt>
                <dd>
                    <? foreach ($document->csl_data['editor'] as $index => $editor) : ?>
                        <?= $index > 0 ? ', ' : '' ?>
                        <?= htmlReady($editor['suffix'].' '.$editor['given'].' '.$editor['family']) ?>
                    <? endforeach ?>
                </dd>
            <? endif ?>
            <? if (!empty($document->csl_data['edition'])) : ?>
                <dt><?= _('Auflagen') ?></dt>
                <dd><?= htmlReady($document->csl_data['edition']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['collection-title'])) : ?>
                <dt><?= _('Sammlungstitel') ?></dt>
                <dd><?= htmlReady($document->csl_data['collection-title']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['collection-number'])) : ?>
                <dt><?= _('Sammlungsnummer') ?></dt>
                <dd><?= htmlReady($document->csl_data['collection-number']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['collection-editor'][0]['family'])) : ?>
                <dt><?= _('Sammlungseditor') ?></dt>
                <dd>
                    <? foreach ($document->csl_data['collection-editor'] as $index => $editor) : ?>
                        <?= $index > 0 ? ', ' : '' ?>
                        <?= htmlReady($editor['suffix'].' '.$editor['given'].' '.$editor['family']) ?>
                    <? endforeach ?>
                </dd>
            <? endif ?>
            <? if (!empty($document->csl_data['call-number'])) : ?>
                <dt><?= _('Signatur') ?></dt>
                <dd><?= htmlReady($document->csl_data['call-number']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['archive_location'])) : ?>
                <dt><?= _('Speicherort im Archiv') ?></dt>
                <dd><?= htmlReady($document->csl_data['archive_location']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['archive'])) : ?>
                <dt><?= _('Archiv') ?></dt>
                <dd><?= htmlReady($document->csl_data['archive']) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['accessed'][0][0])) : ?>
                <dt><?= _('Zugriffsdatum') ?></dt>
                <dd><?= htmlReady((!empty($document->csl_data['accessed'][0][2]) ? $document->csl_data['accessed'][0][2].'.' : '')
                        .(!empty($document->csl_data['accessed'][0][1]) ? $document->csl_data['accessed'][0][1].'.' : '')
                        .$document->csl_data['accessed'][0][0]) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['abstract'])) : ?>
                <dt><?= _('Inhaltsangabe') ?></dt>
                <dd><?= htmlReady($document->csl_data['abstract']) ?></dd>
            <? endif ?>

            <? if ($document->catalog) : ?>
                <dt><?= _('Katalog') ?></dt>
                <? if ($document->opac_link) : ?>
                    <dd><a target="_blank" title="<?=_('Im OPAC anzeigen')?>" href="<?=$document->opac_link?>"><?= htmlReady($document->catalog) ?></a></dd>
                <? else : ?>
                    <dd><?= htmlReady($document->catalog) ?></dd>
                <? endif ?>
            <? endif ?>
        </dl>
    <? endif ?>
<? endif ?>
