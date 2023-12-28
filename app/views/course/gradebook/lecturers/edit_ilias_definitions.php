<?php
/** @var Grading\Definition[] $customDefinitions */
/** @var StudipController $controller */
?>
<table class="default">
    <caption>
        <?= _('ILIAS Leistungen definieren') ?>
    </caption>

    <thead>
    <tr class="tablesorter-ignoreRow">
        <th><?= _('Name') ?></th>
        <th><?= _('ID') ?></th>
        <th><?= _('Prozentwert') ?></th>
        <th><?= _('Bestanden') ?></th>
        <th class="actions"><?= _('Aktionen') ?></th>
    </tr>
    </thead>

    <? if (count($customDefinitions)) { ?>
        <tbody>
        <? foreach ($customDefinitions as $definition) { ?>
            <tr>
                <td>
                    <?= htmlReady($definition->name) ?>
                </td>
                <td>
                    <?= htmlReady($definition->item) ?>
                </td>
                <td>
                    <?= substr($definition->item, -1) & 1 ? 'x' : '' ?>
                </td>
                <td>
                    <?= substr($definition->item, -1) & 2 ? 'x' : '' ?>
                <td class="actions">
                    <?=
                    \ActionMenu::get()
                        ->addLink(
                            $controller->url_for(
                                'course/gradebook/lecturers/edit_ilias_definition',
                                $definition->id
                            ),
                            _('Ändern'),
                            Icon::create('edit'),
                            ['data-dialog' => 'size=fit']
                        )
                        ->addLink(
                            $controller->url_for(
                                'course/gradebook/lecturers/delete_ilias_definition',
                                $definition->id
                            ),
                            _('Löschen'),
                            Icon::create('trash'),
                            ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . _('Wollen Sie die Leistungsdefinition wirklich löschen?') . "', this.href);"]
                        ) ?>
                </td>
            </tr>
        <? } ?>
        </tbody>
    <? } ?>


    <tfoot class="gradebook-lecturer-custom-definitions-actions">
    <tr>
        <td colspan="5">
            <?= \Studip\LinkButton::createAdd(
                count($customDefinitions) ? _('Weiteren Test als Leistung definieren') : _('Test als Leistung definieren'),
                $controller->url_for('course/gradebook/lecturers/new_ilias_definition'),
                ['data-dialog' => 'size=fit']
            ) ?>
        </td>
    </tr>
    </tfoot>
</table>
<?php
