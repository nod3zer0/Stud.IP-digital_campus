<? if ($ruleTypes) : ?>
    <table class="default" id="admissionrules">
        <caption><?= _('Installierte Anmelderegeln:') ?></caption>
            <thead>
                <tr>
                    <th><?= _('aktiv?') ?></th>
                    <th><?= _('Art der Anmelderegel') ?></th>
                </tr>
            </thead>
        <tbody>
        <? foreach ($ruleTypes as $type => $details): ?>
            <tr id="ruletype_<?= htmlReady($type) ?>">
                <td>
                    <a href="<?= $controller->toggle_activation($type) ?>">
                    <? if ($details['active']): ?>
                        <?= Icon::create('checkbox-checked')->asImg([
                            'title' => _('Diese Regel ist aktiv. Klicken Sie hier, um sie zu deaktivieren.')
                        ]) ?>
                    <? else: ?>
                        <?= Icon::create('checkbox-unchecked')->asImg([
                            'title' => _('Diese Regel ist inaktiv. Klicken Sie hier, um sie zu aktivieren.')
                        ]) ?>
                    <? endif; ?>
                    </a>
                </td>
                <td>
                    <strong><?= htmlReady($details['name']) ?></strong> (<?= htmlReady($type) ?>)
                    <br>
                    <?= htmlReady($details['description']) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? else : ?>
    <?= MessageBox::info(_('Sie haben noch keine Anmelderegeln installiert!'))->hideClose(); ?>
<? endif ?>
