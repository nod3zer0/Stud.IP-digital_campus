<?php
/**
 * @var Admin_SpecificationController $controller
 * @var AuxLockRule[] $rules
 */
?>
<form method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default <? if (count($rules) > 0) echo 'sortable-table'; ?>" data-sortlist="[[0, 0]]">
        <caption>
            <?= _('Verwaltung von Zusatzangaben') ?>
        </caption>
        <colgroup>
            <col style="width: 40%">
            <col>
            <col style="width: 10ex">
            <col style="width: 8ex">
        </colgroup>
        <thead>
            <tr>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="text"><?= _('Beschreibung') ?></th>
                <th data-sort="htmldata">
                    <abbr title="<?= _('Anzahl der zugeordneten Veranstaltungen') ?>">#</abbr>
                </th>
                <th class="actions" data-sort="false"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (count($rules) === 0): ?>
            <tr>
                <td colspan="4" style="text-align: center">
                    <?= _('Es wurden noch keine Zusatzangaben definiert.') ?>
                </td>
            </tr>
        <? endif ?>
        <? foreach ($rules as $index => $rule) : ?>
            <tr>
                <td><?= htmlReady($rule->name) ?></td>
                <td><?= htmlReady(Studip\Markup::removeHtml($rule->description)) ?></td>
                <td data-sort-value="<?= count($rule->courses) ?>">
                    <?= number_format(count($rule->courses), 0, ',', '.') ?>
                </td>
                <td class="actions">
                    <a href="<?= $controller->edit($rule) ?>">
                        <?= Icon::create('edit')->asImg(['title' => _('Regel bearbeiten')]) ?>
                    </a>
                <? if (count($rule->courses) > 0): ?>
                    <?= Icon::create('trash', Icon::ROLE_INACTIVE)->asImg(
                        tooltip2(_('Die Regel kann nicht gelöscht werden, da sie noch verwendet wird.'))
                    ) ?>
                <? else: ?>
                    <?= Icon::create('trash')->asInput(tooltip2(_('Regel löschen')) + [
                        'formaction'   => $controller->deleteURL($rule),
                        'data-confirm' => sprintf(_('Wollen Sie die Regel "%s" wirklich löschen?'), $rule->name),
                    ]) ?>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
</form>
