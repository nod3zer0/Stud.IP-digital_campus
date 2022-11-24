<?php
/**
 * @var array $aux
 * @var Course_MembersController $controller
 */
?>
<? if (count($aux['rows']) === 0) : ?>
    <?= MessageBox::info(_('Keine Zusatzangaben oder Teilnehmende vorhanden.')) ?>
<? else : ?>
    <form method="post" action="<?= $controller->store_additional() ?>">
        <?= CSRFProtection::tokenTag()?>
        <table class="default">
            <caption><?= _('Zusatzangaben bearbeiten') ?></caption>
            <thead>
                <tr>
                <? foreach ($aux['head'] as $head): ?>
                    <th><?= htmlReady($head) ?></th>
                <? endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <? foreach ($aux['rows'] as $entry): ?>
                <tr>
                <? foreach ($aux['head'] as $key => $value): ?>
                    <td><?= $key === 'name' ? htmlReady($entry[$key]) : $entry[$key] ?></td>
                <? endforeach; ?>
                </tr>
            <? endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?= count($aux['head']) ?>">
                        <?= Studip\Button::create(_('Speichern'), 'save') ?>
                    </td>
                </tr>
            </tfoot>

        </table>
    </form>
<? endif ?>
