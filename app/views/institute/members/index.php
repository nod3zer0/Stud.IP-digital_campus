<? if ($institute): ?>
    <table class="default" id="list_institute_members">
        <caption><?= _('Mitarbeiterinnen und Mitarbeiter') ?></caption>
        <colgroup>
            <col style="width: 32px">
        <? foreach ($structure as $key => $field): ?>
            <? if ($key !== 'statusgruppe'): ?>
                <col <? if (!empty($field['width'])): ?> style="width: <?= htmlReady($field['width']) ?>px"<? endif ?>>
            <? endif; ?>
        <? endforeach; ?>
        </colgroup>
        <thead>
            <tr>
            <? foreach ($structure as $key => $field): ?>
                <th <? if ($key === 'actions') echo 'class="actions"'; ?> <? if (!empty($field['colspan'])): ?>colspan="<?= $field['colspan'] ?>"<? endif; ?>>
                <? if (!empty($field['link'])): ?>
                    <a href="<?= URLHelper::getLink($field['link']) ?>">
                        <?= htmlReady($field['name']) ?>
                    </a>
                <? else: ?>
                    <?= htmlReady($field['name']) ?>
                <? endif; ?>
                </th>
            <? endforeach; ?>
            </tr>
        </thead>
    <? foreach ($display_tables as $variables): ?>
        <?= $this->render_partial('institute/members/_table_body', $variables) ?>
    <? endforeach; ?>
    </table>
<? endif; ?>
