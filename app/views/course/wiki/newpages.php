<table class="default sortable-table" data-sortlist="[[3, 0]]">
    <caption>
        <?= _('Letzte Änderungen') ?>
    </caption>
    <thead>
        <tr>
            <th data-sort="text"><?= _('Seitenname') ?></th>
            <th data-sort="false"><?= _('Text') ?></th>
            <th data-sort="text"><?= _('Autor/-in') ?></th>
            <th data-sort="text"><?= _('Datum') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach (array_reverse($versions) as $version) : ?>
            <?= $this->render_partial('course/wiki/versioncompare', ['version' => $version]) ?>
        <? endforeach ?>
    </tbody>
</table>
