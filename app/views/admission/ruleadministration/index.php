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
                    <a href="<?= $controller->toggle_activation($type) ?>" role="button">
                    <? if ($details['active']): ?>
                        <?= Icon::create('checkbox-checked')->asImg([
                            'title' => sprintf(
                                _('Die Regel "%s" ist aktiv. Klicken Sie hier, um sie zu deaktivieren.'),
                                $details['name']
                            )
                        ]) ?>
                    <? else: ?>
                        <?= Icon::create('checkbox-unchecked')->asImg([
                            'title' => sprintf(
                                _('Die Regel "%s" ist inaktiv. Klicken Sie hier, um sie zu aktivieren.'),
                                $details['name']
                            )
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
